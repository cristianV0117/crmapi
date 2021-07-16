<?php namespace App\Services;
use Exceptions\TasksException;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
class TracingsServices
{

    private $task;

    public function __construct()
    {
        $this->tasks = new Task();
    }

    public function storeTracingsWithTasks(array $tasks, int $id)
    {
        try {
            $task = [];
            for ($i = 0; $i < count($tasks); $i++) {
                $task[] = [
                    'Description' => $tasks[$i]['Description'],
                    'Status' => $tasks[$i]['Status'],
                    'TracingsId' => $id,
                    'TypesTasksId' => $tasks[$i]['TypesTasksId'],
                    'DeadLine' => Carbon::parse($tasks[$i]['DeadLine']),
                    'User' => $tasks[$i]['User'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
            }
            $responseInsert = $this->tasks::insert($task);
            if (!$responseInsert) {
                throw new TasksException('Ha ocurrido un error', 500);
            }
        } catch (QueryException $e) {
            throw new TasksException('SEGUIMIENTOS_SERVICE_ERR TASK', 500);
        }
        
    }

    public function __destruct()
    {
        $this->tasks = null;
    }
}