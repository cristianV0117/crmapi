<?php namespace Config;
final class System
{
    public static function system(): bool
    {
        return ($_ENV['SIS_STATUS'] === 'true') ? true : false;
    }
}
