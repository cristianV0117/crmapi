<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Core\Base;
class TypeTask extends Model
{
    public function __construct()
    {
        (new Base());
    }

    protected $primaryKey = 'Id';

    protected $connection = "crm";

    protected $table = "TypesTasks";
}