<?php namespace App\Services;
use Illuminate\Database\QueryException;
use App\Controllers\MailerController;
use App\Models\Tracing;
use Exceptions\TasksException;
class TasksServices
{

    private $emailController;

    public function __construct()
    {
        $this->tracing = new Tracing();
        $this->emailController = new MailerController();
    }

    public function getTracingByContact(int $idContact): Tracing
    {
        try {
            return $this->tracing->where('ContactsId', $idContact)->get('Id')->first();
        } catch (QueryException $e) {
            throw new TasksException('TAREAS_SERVICE_ERR CONTACT', 500);
        }
    }

    public function sendEmailNotification(array $data)
    {
        $this->emailController->mailFromSystem($data);
    }

    public function __destruct()
    {
        $this->tracing = null;
        $this->emailController = null;
    }
}