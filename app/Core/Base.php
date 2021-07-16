<?php namespace Core;
use Config\Connections;
class Base extends Connections
{
    public function DB(): \Illuminate\Database\Capsule\Manager
    {
        return $this->connection;
    }
}