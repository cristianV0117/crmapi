<?php namespace App\Controllers;
use App\Controllers\BaseController;
use Slim\Http\{Request, Response};
use Respect\Validation\Validator as v;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use App\Models\Contact;
use App\Services\ContactsServices;
use Exceptions\ContactsException;
class ContactsController extends BaseController
{

    private $contact;
    private $service;

    public function __construct()
    {
        $this->contact = new Contact();
        $this->service = new ContactsServices();
    }

    public function index(Request $request, Response $response, array $args): Response
    {
        try {
            return $this->response($this->contact->with(['channels' => function ($query){
                return $query->where('Status', 1);
            }])->where('Status', 1)->where('User', $request->getHeaderLine('User'))->get(), 200, $response);
        } catch (QueryException $e) {
            throw new ContactsException('CONTACTOS_ERR INDEX', 500);
        }
    }

    public function store(Request $request, Response $response, array $args): Response
    {
        try {
            $post = $request->getParsedBody();
            if (!$this->validate($post, [
                'Name'      => v::notEmpty()->stringType()->length(1, 55),
                'Cellphone' => v::notEmpty()->stringType()->length(1, 15),
                'Email'     => v::notEmpty()->email()->length(1, 125),
                'Petition'  => v::notEmpty()->stringType(),
                'Status'    => v::notEmpty()->intType()->length(1, 1)
            ])) {
                throw new ContactsException('Request enviado incorrecto', 400);
            }

            if ($this->exist('email', $post['Email'], $this->contact)) {
                throw new ContactsException('El contacto ya existe', 401);
            }

            $this->contact->Name = $post['Name'];
            $this->contact->Cellphone = $post['Cellphone'];
            $this->contact->Email = $post['Email'];
            $this->contact->Petition = $post['Petition'];
            $this->contact->User = (!empty($post['User'])) ? $post['User'] : 'crm@refocosta.com';
            $this->contact->Status = $post['Status'];
            $responseInsert  = $this->contact->save();
            if (!$responseInsert) {
                throw new ContactsException('Ha ocurrido un error', 500);
            }
            
            $this->service->sendEmailNotification([
                "Subject" => "Registro de contacto",
                "Body" => "Se ha registrado el contacto <strong>" . $this->contact->Name . "</strong>",
                "Address" => $this->contact->User
            ]);

            if (!empty($post['ChannelId'])) {
                $this->service->storeContactsWithChannels($post['ChannelId'], $this->contact->Id);
            }
            if (!empty($post['TypeChannelId'])) {
                $this->service->storeContactsWithTypesChannels($post['TypeChannelId'], $this->contact->Id);
            }
            if (!empty($post['Channel']) && !empty($post['TypeChannel'])) {
                $idChannel = $this->service->storeChannelAndStoreContactWithChannel($post['Channel'], $post['TypeChannel'], $this->contact->Id);
            }
            $this->service->storeContactInTracing([
                "TypesObservationsId" => 1,
                "ContactsId" => $this->contact->Id,
                "TypesChannelsId" => (empty($post['TypeChannelId'])) ? $idChannel : $post['TypeChannelId'][0],
                "UsersId" => 1
            ], null, $post['Type']);

            return $this->response([
                "Id"   => $this->contact->Id,
                "Name" => $this->contact->Name,
                "Cellphone" => $this->contact->Cellphone,
                "Email" => $this->contact->Email,
                "Petition" => $this->contact->Petition,
                "Status" => $this->contact->Status
            ], 201, $response);
            return $response;
        } catch (QueryException $e) {
            throw new ContactsException('USUARIOS_ERR STORE', 500);
        }
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $record = $this->contact->with(['channels' => function ($query){
                return $query->where('Status', 1);
            }])->with('tracings.typesObservations')->with('tracings.typesChannels')->with(['typesChannels' => function ($query) {
                return $query->where('Status', 1);
            }])->where('Status', 1)->get()->find($id);

            if ($record === null) {
                throw new ContactsException('El registro no existe', 404);
            }
            return $this->response($record, 200, $response);
        } catch (QueryException $e) {
            throw new ContactsException('USUARIOS_ERR SHOW', 500);
        }
        
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $post = $request->getParsedBody();

            if (!$this->validate($post, [
                'Name'      => v::optional(v::notEmpty()->stringType()->length(1, 55)),
                'Cellphone' => v::optional(v::notEmpty()->stringType()->length(1, 15)),
                'Email'     => v::optional(v::notEmpty()->email()->length(1, 125)),
                'Petition'  => v::optional(v::notEmpty()->stringType()),
                'Status'    => v::optional(v::notEmpty()->intType()->length(1, 1))
            ])) {
                throw new ContactsException('Request enviado incorrecto', 400);
            }

            $record = $this->contact->find($id);
            
            if ($record === null) {
                throw new ContactsException('El registro no existe', 404);
            }

            if ($post['User'] != $record->User) {
                $this->service->storeContactInTracing([
                    "TypesObservationsId" => 1,
                    "ContactsId" => $record->Id,
                    "TypesChannelsId" => 1,
                    "UsersId" => 1
                ], "El contacto ha cambiado de responsable a " . $post['User'], 2);
            }

            $record->Name = $post['Name'];
            $record->Cellphone = $post['Cellphone'];
            $record->Email = $post['Email'];
            $record->Petition = $post['Petition'];
            $record->User = $post['User'];
            $record->Status = $post['Status'];
            $record->updated_at = Carbon::now('America/Bogota');
            $responseUpdate = $record->save();

            if (!$responseUpdate) {
                throw new ContactsException('Ha ocurrido un error', 500);
            }

            $this->service->sendEmailNotification([
                "Subject" => "Actualizacion de contacto",
                "Body" => "Se ha editado el contacto <strong>" . $record->Name . "</strong>",
                "Address" => $record->User
            ]);

            $this->service->storeContactsWithChannels($post['ChannelId'], $record->Id);
            $this->service->removeContactsWithChannels($post['ChannelIdDel'], $record->Id);

            return $this->response([
                "id"    => $record->Id,
                "name"  => $record->Name,
                "Status"=> $record->Status
            ], 200, $response);
        } catch (QueryException $e) {
            throw new ContactsException('CONTACTOS_ERR UPDATE', 500);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $record = $this->contact->find($id);

            if ($record === null) {
                throw new ContactsException('El registro no existe', 404);
            }

            $record->Status = 0;
            $responseDelete = $record->save();

            $this->service->deleteTracingsWhenContactDeleted($record);

            if (!$responseDelete) {
                throw new ContactsException('Ha ocurrido un error', 500);
            }

            return $this->response('OK ' . $id, 200, $response);
        } catch (QueryException $e) {
            throw new ContactsException('CONTACTOS_ERR', 500);
        }
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $record = $this->contact->find($id);

            if ($record === null) {
                throw new ContactsException('El registro no existe', 404);
            }

            $responseDestroy =  $record->delete();

            if (!$responseDestroy) {
                throw new ContactsException('Ha ocurrido un error', 500);
            }

            return $this->response('OK ' . $id, 200, $response);
        } catch (QueryException $e) {
            throw new ContactsException('CONTACTOS_ERR', 500);
        }
    }

    public function __destruct()
    {
        $this->contact = null;
        $this->service = null;
    }
}