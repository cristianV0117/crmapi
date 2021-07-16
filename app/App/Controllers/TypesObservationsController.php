<?php namespace App\Controllers;
use App\Controllers\BaseController;
use Slim\Http\{Request, Response};
use Respect\Validation\Validator as v;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use App\Models\TypeObservation;
use Exceptions\TypesObservationsException;
class TypesObservationsController extends BaseController
{
    private $typeObservation;

    public function __construct()
    {
        $this->typeObservation = new TypeObservation();
    }

    public function index(Request $request, Response $response, array $args): Response
    {
        try {
            return $this->response($this->typeObservation->where('Status', 1)->where('Id', '<>', 1)->get(), 200, $response);
        } catch (QueryException $e) {
            throw new TypesObservationsException('TIPO_OBSERVACION_ERR INDEX', 500);
        }
    }

    public function store(Request $request, Response $response, array $args): Response
    {
        try {
            $post = $request->getParsedBody();
            
            if (!$this->validate($post, [
                'Name'   => v::notEmpty()->stringType()->length(1, 55),
                'Status' => v::notEmpty()->intType()->length(1, 1)
            ])) {
                throw new TypesObservationsException('Request enviado incorrecto', 400);
            }

            $this->typeObservation->Name = $post['Name'];
            $this->typeObservation->Status = $post['Status'];
            $responseInsert = $this->typeObservation->save();

            if ($responseInsert === null) {
                throw new TypesObservationsException('Ha ocurrido un error', 500);
            }

            return $this->response([
                "Id"     => $this->typeObservation->Id,
                "Name"   => $this->typeObservation->Name,
                "Status" => $this->typeObservation->Status
            ], 201, $response);
        } catch (QueryException $e) {
            throw new TypesObservationsException('TIPO_OBSERVACION_ERR STORE', 500);
        }
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $record = $this->typeObservation->where('Status', 1)->get()->find($id);

            if ($record === null) {
                throw new TypesObservationsException('El registro no existe', 404);
            }

            return $this->response($record, 200, $response);
        } catch (QueryException $e) {
            throw new TypesObservationsException('TIPO_OBSERVACION_ERR SHOW', 500);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $post = $request->getParsedBody();

            if (!$this->validate($post, [
                'Name'   => v::optional(v::notEmpty()->stringType()->length(1, 55)),
                'Status' => v::optional(v::notEmpty()->intType()->length(1, 1))
            ])) {
                throw new TypesObservationsException('Request enviado incorrecto', 400);
            }

            $record = $this->typeObservation->where('Status', 1)->get()->find($id);

            if ($record === null) {
                throw new TypesObservationsException('El registro no existe', 404);
            }

            $record->Name   = (!empty($post['Name'])) ? $post['Name'] : $record->Name;
            $record->Status = (!empty($post['Status'])) ? $post['Status'] : (int) $record->Status;
            $record->updated_at = Carbon::now('America/Bogota');
            $responseUpdate = $record->save();

            if (!$responseUpdate) {
                throw new TypesObservationsException('Ha ocurrido un error', 500);
            }

            return $this->response([
                "id"    => $record->Id,
                "name"  => $record->Name,
                "Status"=> $record->Status
            ], 200, $response);
        } catch (QueryException $e) {
            throw new TypesObservationsException('TIPO_OBSERVACION_ERR UPDATE', 500);
        }

    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $record = $this->typeObservation->where('Status', 1)->get()->find($id);

            if ($record === null) {
                throw new TypesObservationsException('El registro no existe', 404);
            }

            $record->Status = 0;
            $responseDelete = $record->save();

            if (!$responseDelete) {
                throw new TypesObservationsException('Ha ocurrido un error', 500);
            }
            
            return $this->response('OK '. $id, 200, $response);
        } catch (QueryException $e) {
            throw new TypesObservationsException('TIPO_OBSERVACION_ERR DELETE', 500);
        }
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $record = $this->typeObservation->where('Status', 1)->get()->find($id);

            if ($record === null) {
                throw new TypesObservationsException('El registro no existe', 404);
            }

            $responseDestroy = $record->delete();
            if (!$responseDestroy) {
                throw new TypesObservationsException('Ha ocurrido un error', 500);
            }

            return $this->response('OK '. $id, 200, $response);
        } catch (QueryException $e) {
            throw new TypesObservationsException('TIPO_OBSERVACION_ERR DESTROY', 500);
        }
    }
}