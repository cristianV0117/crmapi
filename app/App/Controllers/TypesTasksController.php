<?php namespace App\Controllers;
use App\Controllers\BaseController;
use Slim\Http\{Request, Response};
use Respect\Validation\Validator as v;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use App\Models\TypeTask;
use Exceptions\TypesTasksException;
class TypesTasksController extends BaseController
{

    private $typeTask;

    public function __construct()
    {
        $this->typeTask = new TypeTask();
    }

    public function index(Request $request, Response $response, array $args): Response
    {
        try {
            return $this->response($this->typeTask->where('Status', 1)->get(), 200, $response);
        } catch (QueryException $e) {
            throw new TypesTasksException('TIPO_TAREAS_ERR INDEX', 500);
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
                throw new TypesTasksException('Request enviado incorrecto', 400);
            }

            $this->typeTask->Name = $post['Name'];
            $this->typeTask->Status = $post['Status'];
            $responseInsert = $this->typeTask->save();

            if ($responseInsert === null) {
                throw new TypesTasksException('Ha ocurrido un error', 500);
            }

            return $this->response([
                "Id"     => $this->typeTask->Id,
                "Name"   => $this->typeTask->Name,
                "Status" => $this->typeTask->Status
            ], 201, $response);
        } catch (QueryException $e) {
            throw new TypesTasksException('TIPO_TAREAS_ERR STORE', 500);
        }
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $record = $this->typeTask->where('Status', 1)->get()->find($id);

            if ($record === null) {
                throw new TypesTasksException('El registro no existe', 404);
            }

            return $this->response($record, 200, $response);
        } catch (QueryException $e) {
            throw new TypesTasksException('TIPO_TAREAS_ERR SHOW', 500);
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
                throw new TypesTasksException('Request enviado incorrecto', 400);
            }

            $record = $this->typeTask->where('Status', 1)->get()->find($id);

            if ($record === null) {
                throw new TypesTasksException('El registro no existe', 404);
            }

            $record->Name   = (!empty($post['Name'])) ? $post['Name'] : $record->Name;
            $record->Status = (!empty($post['Status'])) ? $post['Status'] : (int) $record->Status;
            $record->updated_at = Carbon::now('America/Bogota');
            $responseUpdate = $record->save();

            if (!$responseUpdate) {
                throw new TypesTasksException('Ha ocurrido un error', 500);
            }

            return $this->response([
                "id"    => $record->Id,
                "name"  => $record->Name,
                "Status"=> $record->Status
            ], 200, $response);
        } catch (QueryException $e) {
            throw new TypesTasksException('TIPO_TAREAS_ERR UPDATE', 500);
        }

    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $record = $this->typeTask->where('Status', 1)->get()->find($id);

            if ($record === null) {
                throw new TypesTasksException('El registro no existe', 404);
            }

            $record->Status = 0;
            $responseDelete = $record->save();

            if (!$responseDelete) {
                throw new TypesTasksException('Ha ocurrido un error', 500);
            }
            
            return $this->response('OK '. $id, 200, $response);
        } catch (QueryException $e) {
            throw new TypesTasksException('TIPO_TAREAS_ERR DELETE', 500);
        }
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $record = $this->typeTask->where('Status', 1)->get()->find($id);

            if ($record === null) {
                throw new TypesTasksException('El registro no existe', 404);
            }

            $responseDestroy = $record->delete();
            if (!$responseDestroy) {
                throw new TypesTasksException('Ha ocurrido un error', 500);
            }

            return $this->response('OK '. $id, 200, $response);
        } catch (QueryException $e) {
            throw new TypesTasksException('TIPO_TAREAS_ERR DESTROY', 500);
        }
    }

    //

    public function __destruct()
    {
        $this->typeTask = null;
    }
}