<?php namespace Config;
abstract class Key
{
    public static function key(): string
    {
        return $_ENV['SECRET'];
    }
}