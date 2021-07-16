<?php namespace Middlewares;
use Psr\Http\Message\{RequestInterface as Request, ResponseInterface as Response};
use Exceptions\KeyException;
use Config\Key;
final class KeyMiddleware extends Key
{
    public function __invoke(Request $request, Response $response, $next): Response
    {
        if ($request->getHeaderLine('Authorization') == null) {
            throw new KeyException('No autorizado', 400);
        }

        if ($request->getHeaderLine('Authorization') != self::key()) {
            throw new KeyException('No autorizado', 401);
        }
        $response = $next($request, $response);
        return $response;
    }
}