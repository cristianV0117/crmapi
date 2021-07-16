<?php namespace Config;
abstract class Atdb
{
    protected static function atdb()
    {
        return base64_decode($_ENV['ATDB']);
    }
}