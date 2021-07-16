<?php namespace App\Controllers;
use App\Controllers\BaseController;
use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as v;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use App\Models\Tracing;
use App\Services\TracingsServices;
use Exceptions\TracingsException;
class TracingsController extends BaseController
{

    private $tracing;
    private $service;

    public function __construct()
    {
        $this->tracing = new Tracing();
        $this->service = new TracingsServices();
    }

    public function index(Request $request, Response $response, array $args) :Response
    {
        try {
            return $this->response($this->tracing->with(['contacts' => function ($query) {
                return $query->where('Status', 1);
            }])->with(['typesChannels' => function ($query) {
                return $query->where('Status', 1);
            }])->with(['typesObservations' => function ($query) {
                return $query->where('Status', 1);
            }])->where('Status', 1)->get(), 200, $response);
        } catch (QueryException $e) {
            throw new TracingsException('SEGUIMIENTOS_ERR INDEX', 500);
        }
    }

    public function store(Request $request, Response $response, array $args): Response
    {
        try {
            $post = $request->getParsedBody();

            if (!$this->validate($post, [
                'TypesObservationsId' => v::notEmpty()->intType(),
                'ContactsId'          => v::notEmpty()->intType(),
                'TypesChannelsId'     => v::notEmpty()->intType(),
                'UsersId'             => v::notEmpty()->intType(),
                'Observation'         => v::notEmpty()
            ])) {
                throw new TracingsException('Request enviado incorrecto', 400);
            }

            $this->tracing->Observation = $post["Observation"];
            $this->tracing->TypesObservationsId = $post["TypesObservationsId"];
            $this->tracing->ContactsId = $post["ContactsId"];
            $this->tracing->TypesChannelsId = $post["TypesChannelsId"];
            $this->tracing->UsersId = $post["UsersId"];
            $this->tracing->Status = 1;
            $this->tracing->Quotation = (!empty($post["Quotation"])) ? $post["Quotation"] : 0;
            $this->tracing->Price = empty(!$post["Price"]) ? $post["Price"] : 0;
            $this->tracing->QuotationDate = (!empty($post["Quotation"])) ? Carbon::now('America/Bogota') : null;
            $responseStore = $this->tracing->save();
            
            if (!$responseStore) {
                throw new TracingsException('Ha ocurrido un error', 500);
            }

            if (count($post['tasks']) > 0) {
                $this->service->storeTracingsWithTasks($post['tasks'], $this->tracing->Id);
            }

            return $this->response('Registrado correctamente', 201, $response);
        } catch (QueryException $e) {
            throw new TracingsException('SEGUIMIENTOS_ERR STORE', 500);
        }
    }

    public function storeFromSystem(array $array, ?string $message, int $type)
    {
        try {
            ($type == 1) ? 
                    $this->tracing->Observation = "Se ha registrado automaticamente desde formulario externo":
                    $this->tracing->Observation = "Se ha registrado automaticamente desde la plataforma CRM" ;
            if ($message != null) {
                $this->tracing->Observation .= ", " . $message;
            }
            $this->tracing->TypesObservationsId = $array["TypesObservationsId"];
            $this->tracing->ContactsId = $array["ContactsId"];
            $this->tracing->TypesChannelsId = $array["TypesChannelsId"];
            $this->tracing->UsersId = $array["UsersId"];
            $this->tracing->Status = 1;
            $this->tracing->Auto = 1;
            $responseStoreSystem = $this->tracing->save();
            if (!$responseStoreSystem) {
                throw new TracingsException('Ha ocurrido un error', 500);
            }
        } catch (QueryException $e) {
            throw new TracingsException('SEGUIMIENTOS_ERR SYSTEM', 500);
        }
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];

            $record = $this->tracing->with(['contacts' => function ($query) {
                return $query->where('Status', 1);
            }])->with(['typesChannels' => function ($query) {
                return $query->where('Status', 1);
            }])->with(['typesObservations' => function ($query) {
                return $query->where('Status', 1);
            }])->with('tasks.typesTasks')->where('Status', 1)->get()->find($id);

            if ($record === null) {
                throw new TracingsException('El registro no existe', 404);
            }

            return $this->response($record, 200, $response);
        } catch (QueryException $e) {
            throw new TracingsException('SEGUIMIENTOS_ERR SHOW', 500);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $post = $request->getParsedBody();

            if (!$this->validate($post, [
                'TypesObservationsId' => v::optional(v::notEmpty()->intType()),
                'ContactsId'          => v::optional(v::notEmpty()->intType()),
                'TypesChannelsId'     => v::optional(v::notEmpty()->intType()),
                'Observation'         => v::optional(v::notEmpty())
            ])) {
                throw new TracingsException('Request enviado incorrecto', 400);
            }

            $record = $this->tracing->find($id);

            $record->TypesObservationsId = (!empty($post['TypesObservationsId'])) ? $post['TypesObservationsId'] : (int) $record->TypesObservationsId;
            $record->ContactsId = (!empty($post['ContactsId'])) ? $post['ContactsId'] : (int) $record->ContactsId;
            $record->TypesChannelsId = (!empty($post['TypesChannelsId'])) ? $post['TypesChannelsId'] : (int) $record->TypesChannelsId;
            $record->Observation = (!empty($post['Observation'])) ? $post['Observation'] : $record->Observation;
            $record->Quotation = (!empty($post['Quotation'])) ? $post['Quotation'] : $record->Quotation;
            $record->Price = (!empty($post['Price'])) ? $post['Price'] : $record->Price;
            $record->QuotationDate = (!empty($post['Quotation'])) ? Carbon::now('America/Bogota') : $record->QuotationDate;
            $record->Sale = (!empty($post['Sale'])) ? $post['Sale'] : $record->Sale;
            $record->Value = (!empty($post['Value'])) ? $post['Value'] : $record->Value;
            $record->SaleDate = (!empty($post['Sale'])) ? Carbon::now('America/Bogota') : $record->SaleDate;
            $record->updated_at = Carbon::now('America/Bogota');
            $responseUpdate = $record->save();

            if (!$responseUpdate) {
                throw new TracingsException('Ha ocurrido un error', 500);
            }

            return $this->response([
                "id" => $record->Id
            ], 200, $response);
            return $response;
        } catch (QueryException $e) {
            throw new TracingsException('SEGUIMIENTOS_ERR UPDATE', 500);
        }
    }

    public function __destruct()
    {
        $this->tracing = null;
        $this->service = null;
    }
}