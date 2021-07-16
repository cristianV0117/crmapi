<?php namespace App\Controllers\Nativa;
use App\Controllers\BaseController;
use Slim\Http\{Request, Response};
use App\Models\Nativa\User;
class UsersController extends BaseController
{
    private $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function index(Request $request, Response $response, array $args): Response
    {
        return $this->response($this->user->select('UserName', 'FirstName', 'LastName')->where('IsActive', 1)->get(), 200, $response);
    }

    public function __destruct()
    {
        $this->user = null;
    }
}