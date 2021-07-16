<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Core\Base;
class Contact extends Model
{
    public function __construct()
    {
        (new Base());
    }

    protected $primaryKey = 'Id';

    protected $connection = "crm";

    protected $table = "Contacts";

    // ONE TO MANY //

    public function tracings()
    {
        return $this->hasMany(Tracing::class, 'ContactsId', 'Id');
    }

    // MANY TO MANY //

    public function channels()
    {
        return $this->belongsToMany(Channel::class, 'ContactsChannels', 'ContactsId', 'ChannelsId');
    }

    public function typesChannels()
    {
        return $this->belongsToMany(TypeChannel::class, 'ContactsTypesChannels', 'ContactsId', 'TypesChannelsId');
    }

}