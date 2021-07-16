<?php namespace App\Controllers;
use App\Controllers\BaseController;
use Slim\Http\{Request, Response};
use Respect\Validation\Validator as v;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use App\Models\TypeChannel;
use Exceptions\TypesChannelsException;
class TypesChannelsController extends BaseController
{
    private $typeChannel;

    public function __construct()
    {
        $this->typeChannel = new TypeChannel();
    }

    public function index(Request $request, Response $response, array $args): Response
    {
        try {
            return $this->response($this->typeChannel->where('Status', 1)->where('id', '<>', 1)->get(), 200, $response);
        } catch (QueryException $e) {
            throw new TypesChannelsException('CANALES_ERR INDEX', 500);
        }
        
    }

    public function store(Request $request, Response $response, array $args): Response
    {
        try {
            $post = $request->getParsedBody();

            if (!$this->validate($post, [
                'Name'       => v::notEmpty()->stringType()->length(1, 45),
                'ChannelsId' => v::notEmpty()->intType(),
                'Status'     => v::notEmpty()->intType()->length(1, 1)
            ])) {
                throw new TypesChannelsException('Verifica la información enviada', 400);
            }
            
            $this->typeChannel->Name = $post["Name"];
            $this->typeChannel->ChannelsId = $post["ChannelsId"];
            $this->typeChannel->Status = $post['Status'];
            $responseInsert = $this->typeChannel->save();
            if (!$responseInsert) {
                throw new TypesChannelsException('Ha ocurrido un error', 500);
            }
            return $this->response([
                "Id"     => $this->typeChannel->Id,
                "Name"   => $this->typeChannel->Name,
                "Status" => $this->typeChannel->Status
            ], 201, $response);
        } catch (QueryException $e) {
            throw new TypesChannelsException('CANALES_ERR STORE', 500);
        }
    }

    public function storeFromSystem(string $typeChannel, int $idChannel): int
    {
        try {
            if ($this->exist('Name', $typeChannel, $this->typeChannel)) {
                $record = $this->typeChannel->where('Status', 1)->where('Name', $typeChannel)->get()->first();
                return $record->Id;
            } else {
                $this->typeChannel->Name = $typeChannel;
                $this->typeChannel->ChannelsId = $idChannel;
                $this->typeChannel->Status = 1;
                $this->typeChannel->save();
                return $this->typeChannel->Id;
            }
        } catch (QueryException $e) {
            throw new TypesChannelsException('CANALES_ERR STORE SYSTEM', 500);
        }
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args["id"];
            
            $record = $this->typeChannel->with(array('channels' => function($query) {
                $query->where('Status', 1);
            }))->where('Status', 1)->find($id);

            if ($record === null) {
                throw new TypesChannelsException('El registro no existe', 404);
            }

            return $this->response($record, 200, $response);
        } catch (QueryException $e) {
            throw new TypesChannelsException('CANALES_ERR SHOW', 500);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $post = $request->getParsedBody();

            if (!$this->validate($post, [
                'Name'       => v::optional(v::stringType()->length(1, 45)),
                'ChannelsId' => v::optional(v::intType()),
                'Status'     => v::optional(v::notEmpty()->intType()->length(1, 1))
            ])) {
                throw new TypesChannelsException('Verifica la información enviada', 400);
            }

            $record = $this->typeChannel->find($id);

            if ($record === null) {
                throw new TypesChannelsException('El registro no existe', 404);
            }

            $record->Name = (!empty($post['Name'])) ? $post['Name'] : $record->Name;
            $record->ChannelsId = (!empty($post['ChannelsId'])) ? $post['ChannelsId'] : $record->ChannelsId;
            $record->Status = (!empty($post['Status'])) ? $post['Status'] : $record->Status;
            $record->updated_at = Carbon::now('America/Bogota');
            $responseUpdate = $record->save();

            if (!$responseUpdate) {
                throw new TypesChannelsException('Ha ocurrido un error', 500);
            }

            return $this->response([
                "id"    => $record->Id,
                "name"  => $record->Name,
                "Status"=> $record->Status
            ], 200, $response);
        } catch (QueryException $e) {
            throw new TypesChannelsException('CANALES_ERR UPDATE', 500);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $record = $this->typeChannel->find($id);

            if ($record == null) {
                throw new TypesChannelsException("El registro no existe", 404);
            }

            $record->Status = 0;
            $responseDelete = $record->save();
            
            if (!$responseDelete) {
                throw new TypesChannelsException('Ha ocurrido un error', 500);
            }

            return $this->response('OK ' . $id, 200, $response);
        } catch (QueryException $e) {
            throw new TypesChannelsException('CANALES_ERR DELETE', 500);
        }
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $record = $this->typeChannel->find($id);

            if ($record === null) {
                throw new TypesChannelsException('El registro no existe', 404);
            }

            $responseDestroy = $record->delete();
            
            if (!$responseDestroy) {
                throw new TypesChannelsException('Ha ocurrido un error', 500);
            }

            return $this->response('OK ' . $id, 200, $response);
        } catch (QueryException $e) {
            throw new TypesChannelsException('CANALES_ERR DESTROY', 500);
        }
    }

    public function __destruct()
    {
        $this->typeChannel = null;
    }
}