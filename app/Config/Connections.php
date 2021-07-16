<?php namespace Config;
use Illuminate\Database\Capsule\Manager as Capsule;
use Config\DataBase;
abstract class Connections extends DataBase
{
    protected $connection;

    public function __construct()
    {
        $this->connection = new Capsule();
        $this->connection();
        $this->start();
    }

    private function connection()
    {
        $this->connection->addConnection([
            'driver'    => 'sqlsrv',
            'host'      => self::dataBase()["DATABASE_SERVER"],
            'database'  => self::dataBase()["DATABASE_DB"],
            'username'  => self::dataBase()["DATABASE_USER"],
            'password'  => self::dataBase()["DATABASE_PASS"],
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci'
        ], 'crm');

        $this->connection->addConnection([
            'driver'    => 'sqlsrv',
            'host'      => self::dataBaseNativa()["DATABASE_NATIVA_SERVER"],
            'database'  => self::dataBaseNativa()["DATABASE_NATIVA_DB"],
            'username'  => self::dataBaseNativa()["DATABASE_NATIVA_USER"],
            'password'  => self::dataBaseNativa()["DATABASE_NATIVA_PASS"],
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci'
        ], 'refosus');
    }

    private function start()
    {
        $this->connection->setAsGlobal();
        $this->connection->bootEloquent();
    }

    abstract public function DB();

    public function __destruct()
    {
        $this->connection = null;
    }
}