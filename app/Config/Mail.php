<?php namespace Config;
class Mail
{
    public static function mail(): array
    {
        return [
            "SMTP_HOST" => $_ENV["SMTP_HOST"],
            "SMTP_USER" => $_ENV["SMTP_USER"],
            "SMTP_PASS" => $_ENV["SMTP_PASS"],
            "SMTP_PORT" => $_ENV["SMTP_PORT"],
            "SMTP_SECU" => $_ENV["SMTP_SECU"]
        ];
    }
}