<?php namespace App\Services;
use App\Models\Channel;
use Exceptions\ChannelsException;
class ChannelsServices
{
    public function deleteTypesChannelsWhenChannelDeleted(Channel $channel)
    {
        try {
            for ($i = 0; $i < count($channel->typesChannels); $i++) {
            $channel->typesChannels[$i]->Status = 0;
            $channel->typesChannels[$i]->save();
        }
        } catch (\Illuminate\Database\QueryException $e) {
            throw new ChannelsException($e->getMessage(), 500);
        }
    }
}