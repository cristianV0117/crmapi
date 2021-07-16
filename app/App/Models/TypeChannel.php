<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Core\Base;
class TypeChannel extends Model
{
    public function __construct()
    {
        (new Base());
    }

    protected $primaryKey = 'Id';

    protected $connection = "crm";

    protected $table = "TypesChannels";

    public function channels()
    {
        return $this->belongsTo(Channel::class, 'ChannelsId');
    }

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'ContactsTypesChannels', 'TypesChannelsId', 'ContactsId');
    }
}