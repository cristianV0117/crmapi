<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Core\Base;
class Tracing extends Model
{
    public function __construct()
    {
        (new Base());
    }

    protected $primaryKey = 'Id';

    protected $connection = "crm";

    protected $table = "Tracings";

    public function typesObservations()
    {
        return $this->belongsTo(TypeObservation::class, 'TypesObservationsId', 'Id');
    }

    public function contacts()
    {
        return $this->belongsTo(Contact::class, 'ContactsId', 'Id');
    }

    public function typesChannels()
    {
        return $this->belongsTo(TypeChannel::class, 'TypesChannelsId', 'Id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'TracingsId', 'Id');
    }
}