<?php namespace App\Services;
use App\Models\Contact;
use App\Controllers\TracingsController;
use App\Controllers\MailerController;
use App\Controllers\ChannelsController;
use App\Controllers\TypesChannelsController;
use Illuminate\Database\QueryException;
use Exceptions\ContactsException;
class ContactsServices
{
    private $tracingController;
    private $emailCotroller;
    private $channelController;
    private $typeChannelController;

    public function __construct()
    {
        $this->tracingController = new TracingsController();
        $this->emailCotroller = new MailerController();
        $this->channelController = new ChannelsController();
        $this->typeChannelController = new TypesChannelsController();
    }

    public function storeContactsWithChannels(array $channelId, int $contactId)
    {
        try {
            if (count($channelId) > 0) {
                $contact = Contact::find($contactId);
                for ($i = 0; $i < count($channelId); $i++) { 
                    $contact->channels()->attach($channelId[$i]);
                }
            }
        } catch (QueryException $e) {
            throw new ContactsException('CONTACTOS_SERVICE_ERR ATACH', 500);
        }
    }

    public function removeContactsWithChannels(array $channelIdDel, int $contactId)
    {
        try {
            if (count($channelIdDel) > 0) {
                $contact = Contact::find($contactId);
                for ($i = 0; $i < count($channelIdDel); $i++) {
                    $contact->channels()->detach($channelIdDel[$i]);
                }
            }
        } catch (QueryException $e) {
            throw new ContactsException('CONTACTOS_SERVICE_ERR DETACH', 500);
        }
        
    }

    public function storeContactsWithTypesChannels(array $typeChannelId, int $contactId)
    {
        try {
            if (count($typeChannelId) > 0) {
                $contact = Contact::find($contactId);
                for ($i = 0; $i < count($typeChannelId); $i++) {
                    $contact->typesChannels()->attach($typeChannelId[$i]);
                }
            }
        } catch (QueryException $e) {
            throw new ContactsException('CONTACTOS_SERVICE_ERR ATACH', 500);
        }
    }

    public function storeChannelAndStoreContactWithChannel(string $channel, string $typeChannel, int $idContact): int
    {
        $idChannel = $this->channelController->storeFromSystem($channel);
        $idTypeChannel = $this->typeChannelController->storeFromSystem($typeChannel, $idChannel);
        $this->storeContactsWithChannels([$idChannel], $idContact);
        $this->storeContactsWithTypesChannels([$idTypeChannel], $idContact);
        return $idTypeChannel;
    }

    public function storeContactInTracing(array $array, ?string $message,int $type)
    {
        $this->tracingController->storeFromSystem($array, $message, $type);
    }

    public function deleteTracingsWhenContactDeleted(Contact $contact)
    {
        try {
            for ($i = 0; $i < count($contact->tracings); $i++) {
            $contact->tracings[$i]->Status = 0;
            $contact->tracings[$i]->save();
        }
        } catch (\Illuminate\Database\QueryException $e) {
            throw new ContactsException($e->getMessage(), 500);
        }
    }

    public function sendEmailNotification(array $data)
    {
        $this->emailCotroller->mailFromSystem($data);
    }

    public function __destruct()
    {
        $this->tracingController = null;
        $this->emailCotroller = null;
        $this->channelController = null;
        $this->typeChannelController = null;
    }
}