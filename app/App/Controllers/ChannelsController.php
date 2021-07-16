<?php namespace App\Controllers;
use App\Controllers\BaseController;
use Slim\Http\{Request, Response};
use Respect\Validation\Validator as v;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use App\Models\Channel;
use App\Services\ChannelsServices;
use Exceptions\ChannelsException;
class ChannelsController extends BaseController
{
    private $channel;
    private $service;

    public function __construct()
    {
        $this->channel = new Channel();
        $this->service = new ChannelsServices();
    }

    public function index(Request $request,  Response $response, array $args): Response
    {
        try {
            return $this->response($this->channel->with(array('typesChannels' => function ($query){
                return $query->where('Status', 1);
            }))->where('Status', 1)->get(), 200, $response);
        } catch (QueryException $e) {
            throw new ChannelsException('CATEOGORIAS_CANALES_ERR INDEX', 500);
        }
        
    }

    public function store(Request $request,  Response $response, array $args): Response
    {
        try {
            $post = $request->getParsedBody();

            if (!$this->validate($post, [
                'Name'   => v::notEmpty()->stringType()->length(1, 45),
                'Status' => v::notEmpty()->intType()->length(1, 1)
            ])) {
                throw new ChannelsException('Request enviado incorrecto', 400);
            }

            $this->channel->Name = $post['Name'];
            $this->channel->Status = $post['Status'];
            $responseInsert = $this->channel->save();
            if (!$responseInsert) {
                throw new ChannelsException('Ha ocurrido un error', 500);
            }

            return $this->response([
                "Id"     => $this->channel->Id,
                "Name"   => $this->channel->Name,
                "Status" => $this->channel->Status
            ], 201, $response);
        } catch (QueryException $e) {
            throw new ChannelsException('CATEGORIAS_CANALES_ERR STORE', 500);
        }
    }

    public function storeFromSystem(string $channel): int
    {
        try {
            if ($this->exist('Name', $channel, $this->channel)) { 
                $record = $this->channel->where('Status', 1)->where('Name', $channel)->get()->first();
                return $record->Id;
            } else {
                $this->channel->Name = $channel;
                $this->channel->Status = 1;
                $this->channel->save();
                return $this->channel->Id;
            }
        } catch (QueryException $e) {
            throw new ChannelsException('CATEGORIAS_CANALES STORE SYSTEM', 500);
        }
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $record = $this->channel->with(['typesChannels' => function ($query) {
                return $query->where('Status', 1);
            }])->where('Status', 1)->find($id);

            if ($record === null) {
                throw new ChannelsException('El registro no existe', 404);
            }

            return $this->response($record, 200, $response);
        } catch (QueryException $e) {
            throw new ChannelsException('CATEGORIAS_CANALES_ERR SHOW', 500);
        }
        
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $post = $request->getParsedBody();

            if (!$this->validate($post, [
                'Name'   => v::optional(v::stringType()->length(1, 45)),
                'Status' => v::optional(v::notEmpty()->intType()->length(1, 1))
            ])) {
                throw new ChannelsException('Request enviado incorrecto', 400);
            }

            $record = $this->channel->find($id);

            if ($record === null) {
                throw new ChannelsException('El registro no existe', 404);
            }

            $record->Name = (!empty($post['Name'])) ? $post['Name'] : $record->Name;
            $record->Status = (!empty($post['Status'])) ? $post['Status'] : (int) $record->Status;
            $record->updated_at = Carbon::now('America/Bogota');
            $responseUpdate = $record->save();

            if (!$responseUpdate) {
                throw new ChannelsException('Ha ocurrido un error', 500);
            }

            return $this->response([
                "id"    => $record->Id,
                "name"  => $record->Name,
                "Status"=> $record->Status
            ], 200, $response);
            return $response;
        } catch (QueryException $e) {
            throw new ChannelsException('CATEGORIAS_CANALES_ERR UPDATE', 500);
        }

    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $record = $this->channel->find($id);

            if ($record == null) {
                throw new ChannelsException("El registro no existe", 404);
            }

            $record->Status = 0;
            $responseDelete = $record->save();

            $this->service->deleteTypesChannelsWhenChannelDeleted($record);

            if (!$responseDelete) {
                throw new ChannelsException('Ha ocurrido un error', 500);
            }

            return $this->response('OK ' . $id, 200, $response);
        } catch (QueryException $e) {
            throw new ChannelsException('CATEGORIAS_CANALES_ERR DELETE', 500);
        }
        
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $record = $this->channel->find($id);

            if ($record === null) {
                throw new ChannelsException('El registro no existe', 404);
            }

            $responseDestroy = $record->delete();

            if (!$responseDestroy) {
                throw new ChannelsException('Ha ocurrido un error', 500);
            }

            return $this->response('OK ' . $id, 200, $response);
        } catch (QueryException $e) {
            throw new ChannelsException('CATEGORIAS_CANALES_ERR DESTROY', 500);
        }
            
    }
    
    public function __destruct()
    {
        $this->channel = null;
        $this->service = null;
    }
}