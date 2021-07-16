<?php namespace App\Controllers;
use App\Controllers\BaseController;
use Slim\Http\{Request, Response};
class AppController extends BaseController
{
    public function app(Request $request, Response $response, array $args): Response
    {
        return $this->response([
            "crm"     => "NATIVA CRM API",
            "home"    => "Bienvenido",
            "version" => "1.0"
        ], 200, $response);
    }
}