<?php namespace App\Controllers;
use Slim\Http\Response;
abstract class BaseController
{
    use \Core\Validator;

    protected function response($data, int $status, Response $response): Response
    {
        $result = [
            "status" => $status,
            "error"  => false,
            "message"=> $data
        ];
        return $response->withJson($result, $status);
    }

    protected function validate(array $post, array $rules): bool
    {
        self::validateRequest($post, $rules);
        if (self::failded()) {
            return false;
        }
        return true;
    }

    protected function exist(string $column, string|int $delimiter, object $class): bool
    {
        $record = $class->where($column, $delimiter)->get($column)->first();
        return ($record != null) ? true : false;
    }

}