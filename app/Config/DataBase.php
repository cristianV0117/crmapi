<?php namespace Config;
abstract class DataBase
{
    public static function dataBase(): array
    {
        return [
            "DATABASE_SERVER" => $_ENV["DATABASE_SERVER"],
            "DATABASE_DB"     => $_ENV["DATABASE_DB"],
            "DATABASE_USER"   => $_ENV["DATABASE_USER"],
            "DATABASE_PASS"   => $_ENV["DATABASE_PASS"]
        ];
    }

    public static function dataBaseNativa()
    {
        return [
            "DATABASE_NATIVA_SERVER" => $_ENV["DATABASE_NATIVA_SERVER"],
            "DATABASE_NATIVA_DB"     => $_ENV["DATABASE_NATIVA_DB"],
            "DATABASE_NATIVA_USER"   => $_ENV["DATABASE_NATIVA_USER"],
            "DATABASE_NATIVA_PASS"   => $_ENV["DATABASE_NATIVA_PASS"]
        ];
    }
}