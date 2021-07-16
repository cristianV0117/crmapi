<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Core\Base;
class Channel extends Model
{
    public function __construct()
    {
        (new Base());
    }

    protected $primaryKey = 'Id';

    protected $connection = "crm";

    protected $table = "Channels";

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'ContactsChannels', 'ChannelsId', 'ContactsId');
    }

    public function typesChannels()
    {
        return $this->hasMany(TypeChannel::class, 'ChannelsId', 'Id');
    }
}