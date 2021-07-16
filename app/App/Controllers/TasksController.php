<?php namespace App\Controllers;
use App\Controllers\BaseController;
use Slim\Http\{Request, Response};
use Respect\Validation\Validator as v;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use App\Models\Task;
use App\Services\TasksServices;
use DateTimeZone;
use Exceptions\TasksException;
class TasksController extends BaseController
{
    private $task;
    private $service;

    public function __construct()
    {
        $this->task = new Task();
        $this->service = new TasksServices();
    }

    public function index(Request $request, Response $response, array $args) :Response
    {
        try {
            return $this->response($this->task->with('tracings')->where('Status', '<>', 0)->where('User', $request->getHeaderLine('User'))->get(), 200, $response);
        } catch (QueryException $e) {
            throw new TasksException('TAREAS_ERR INDEX', 500);
        }
    }

    public function store(Request $request, Response $response, array $args): Response
    {
        try {
            $post = $request->getParsedBody();

            if (!$this->validate($post, [
                'Description' =>  v::notEmpty()->stringType()->length(1, 45),
                'Status'      =>  v::notEmpty()->intType()->length(1, 1),
                'contactsId'  =>  v::notEmpty()->intType(),
                "TypesTasksId" => v::notEmpty()->intType(),
                "User"        =>  v::optional(v::notEmpty()->email())
            ])) {
                throw new TasksException('Request enviado incorrecto', 400);
            }

            $idTracings = $this->service->getTracingByContact($post['contactsId']);

            $this->task->Description = $post['Description'];
            $this->task->Status = $post['Status'];
            $this->task->TracingsId = $idTracings->Id;
            $this->task->TypesTasksId = $post['TypesTasksId'];
            $this->task->DeadLine = Carbon::parse($post['DeadLine']);
            $this->task->User = $post['User'];
            $responseInsert = $this->task->save();

            if (!$responseInsert) {
                throw new TasksException('Ha ocurrido un error', 500);
            }

            $this->service->sendEmailNotification([
                "Subject" => "Registro de tarea",
                "Body" => "Se ha registrado la tarea <strong>" . $this->task->Id . "</strong>",
                "Address" => $this->task->User
            ]);

            return $this->response([
                "Id" => $this->task->Id,
                "Description" => $this->task->Description,
                "Status" => $this->task->Status,
            ], 201, $response);
        } catch (QueryException $e) {
            throw new TasksException('TAREAS_ERR STORE', 500);
        }
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            
            $record = $this->task->with('tracings')->with('typesTasks')->where('Status', '<>', 0)->find($id);

            if ($record === null) {
                throw new TasksException('El registro no existe', 404);
            }

            return $this->response($record, 200, $response);
        } catch (QueryException $e) {
            throw new TasksException('TAREAS_ERR SHOW', 500);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $post = $request->getParsedBody();

            if (!$this->validate($post, [
                'Description'  => v::optional(v::notEmpty()->stringType()->length(1, 45)),
                'Status'       => v::optional(v::notEmpty()->intType()->length(1, 1)),
                'TracingsId'   => v::optional(v::notEmpty()->intType()),
                'TypesTasksId' => v::optional(v::notEmpty()->intType())
            ])) {
                throw new TasksException('Request enviado incorrecto', 400);
            }

            $record = $this->task->find($id);

            $record->Description = (!empty($post['Description'])) ? $post['Description'] : $record->Description;
            $record->Status = (!empty($post['Status'])) ? $post['Status'] : (int) $record->Status;
            $record->TracingsId = (!empty($post['TracingsId'])) ? $post['TracingsId'] : (int) $record->TracingsId;
            $record->TypesTasksId = (!empty($post['TypesTasksId'])) ? $post['TypesTasksId'] : (int) $record->TypesTasksId;
            $record->DeadLine = (!empty($post['DeadLine'])) ? $post['DeadLine'] : $record->DeadLine;
            $record->User = $post['User'];
            $record->updated_at = Carbon::now('America/Bogota');
            $responseUpdate = $record->save();

            if (!$responseUpdate) {
                throw new TasksException('Ha ocurrido un error', 500);
            }

            return $this->response([
                "id"          => $record->Id,
                "Description" => $record->Description,
                "Status"      => $record->Status
            ], 200, $response);
        } catch (QueryException $e) {
            throw new TasksException('TAREAS_ERR UPDATE', 500);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];

            $record = $this->task->find($id);

            if ($record === null) {
                throw new TasksException('El registro no existe', 404);
            }

            $record->Status = 0;
            $responseDelete = $record->save();

            if (!$responseDelete) {
                throw new TasksException('Ha ocurrido un error', 500);
            }

            return $this->response('OK ' . $id, 200, $response);
        } catch (QueryException $e) {
            throw new TasksException('TAREAS_ERR DELETE', 500);
        }
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $record = $this->task->find($id);

            if ($record === null) {
                throw new TasksException('El registro no existe', 404);
            }

            $responseDestroy = $record->delete();

            if (!$responseDestroy) {
                throw new TasksException('Ha ocurrido un error', 500);
            }

            return $this->response('OK ' . $id, 200, $response);
        } catch (QueryException $e) {
            throw new TasksException('TAREAS_ERR DESTROY', 500);
        }
    }

    public function reminder(Request $request, Response $response, array $args): Response
    {
        $now = Carbon::now('America/Bogota')->format('Y-m-d');
        $records = $this->task->where('Status', 1)->get();
        $defeated = [];
        foreach ($records as $value) {
            if ($now > $value->DeadLine) {
                array_push($defeated, 'Por favor soluciona la tarea ' . $value->Id);
            }
        }
        return $this->response($defeated, 200, $response);
        
    }

    public function __destruct()
    {
        $this->task = null;
        $this->service = null;
    }
}