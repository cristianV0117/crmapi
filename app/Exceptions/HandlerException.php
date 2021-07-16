<?php namespace Exceptions;
use Psr\Http\Message\{RequestInterface as Request, ResponseInterface as Response};
class HandlerException extends \Slim\Handlers\Error
{
    public function __invoke(Request $request, Response $response, \Exception $exception)
    {
        $status = $exception->getCode();
        $classTemporally = new \ReflectionClass(get_class($exception));
        $class = explode('\\', $classTemporally->getName());
        $data = [
            "status" => $status,
            "error"  => true,
            "class"  => $class[1],
            "message"=> $exception->getMessage()
        ];
        $body = json_encode($data);
        $response->getBody()->write((string) $body);
        return $response
            ->withStatus($status)
            ->withHeader('Content-type', 'application/problem+json');
    }
}