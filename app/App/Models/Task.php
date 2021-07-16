<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Core\Base;
class Task extends Model
{
    public function __construct()
    {
        (new Base());
    }

    protected $primaryKey = 'Id';

    protected $connection = "crm";

    protected $table = "Tasks";

    public function tracings()
    {
        return $this->belongsTo(Tracing::class, 'TracingsId', 'Id');
    }

    public function typesTasks()
    {
        return $this->belongsTo(TypeTask::class, 'TypesTasksId', 'Id');
    }
}