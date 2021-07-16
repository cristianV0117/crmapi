<?php namespace App\Models\Nativa;
use Illuminate\Database\Eloquent\Model;
use Core\Base;
class User extends Model
{
    public function __construct()
    {
        (new Base());
    }

    protected $primaryKey = 'Id';

    protected $connection = "refosus";

    protected $table = "AspNetUsers";
}