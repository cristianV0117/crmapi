<?php
use Middlewares\{KeyMiddleware, AtdbMiddleware};
use Exceptions\HandlerException;

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true
    ]
]);
$c = $app->getContainer();
$c['errorHandler'] = function ($c) {
    return new HandlerException();
};

// REDIRECCION DE LA RAIZ / //
$app->redirect('/', '/api/v1');
$app->redirect('/api', '/api/v1');

$app->get('/api/v1', '\App\Controllers\AppController:app');

// RUTAS //
$app->group('/api/v1', function () use ($app) {
    // CHANNELS //
    $app->group('/channels', function () use ($app) {
        $app->get('', '\App\Controllers\ChannelsController:index');
        $app->post('', '\App\Controllers\ChannelsController:store');
        $app->get('/{id}', '\App\Controllers\ChannelsController:show');
        $app->put('/{id}', '\App\Controllers\ChannelsController:update');
        $app->delete('/{id}', '\App\Controllers\ChannelsController:destroy');
        $app->patch('/{id}', '\App\Controllers\ChannelsController:delete');
    });
    // CONTACTS //
    $app->group('/contacts', function () use ($app) {
        $app->get('', '\App\Controllers\ContactsController:index');
        $app->post('', '\App\Controllers\ContactsController:store');
        $app->get('/{id}', '\App\Controllers\ContactsController:show');
        $app->put('/{id}', '\App\Controllers\ContactsController:update');
        $app->delete('/{id}', '\App\Controllers\ContactsController:destroy');
        $app->patch('/{id}', '\App\Controllers\ContactsController:delete');
    });
    // TYPES CHANNELS //
    $app->group('/types-channels', function () use ($app) {
        $app->get('', '\App\Controllers\TypesChannelsController:index');
        $app->post('', '\App\Controllers\TypesChannelsController:store');
        $app->get('/{id}', '\App\Controllers\TypesChannelsController:show');
        $app->put('/{id}', '\App\Controllers\TypesChannelsController:update');
        $app->delete('/{id}', 'App\Controllers\TypesChannelsController:destroy');
        $app->patch('/{id}', '\App\Controllers\TypesChannelsController:delete');
    });
    // TYPES OBSERVATIONS //
    $app->group('/types-observations', function () use  ($app) {
        $app->get('', '\App\Controllers\TypesObservationsController:index');
        $app->post('', '\App\Controllers\TypesObservationsController:store');
        $app->get('/{id}', '\App\Controllers\TypesObservationsController:show');
        $app->put('/{id}', 'App\Controllers\TypesObservationsController:update');
        $app->patch('/{id}', '\App\Controllers\TypesObservationsController:delete');
        $app->delete('/{id}', '\App\Controllers\TypesObservationsController:destroy');
    });
    // TRACINGS //
    $app->group('/tracings', function () use ($app) {
        $app->get('', '\App\Controllers\TracingsController:index');
        $app->post('', '\App\Controllers\TracingsController:store');
        $app->get('/{id}', '\App\Controllers\TracingsController:show');
        $app->put('/{id}', '\App\Controllers\TracingsController:update');
    });
    // TYPES TASKS //
    $app->group('/types-tasks', function () use ($app) {
        $app->get('', '\App\Controllers\TypesTasksController:index');
        $app->post('', '\App\Controllers\TypesTasksController:store');
        $app->get('/{id}', '\App\Controllers\TypesTasksController:show');
        $app->put('/{id}', '\App\Controllers\TypesTasksController:update');
        $app->patch('/{id}', '\App\Controllers\TypesTasksController:delete');
        $app->delete('/{id}', 'App\Controllers\TypesTasksController:destroy');
    });
    // TASKS //
    $app->group('/tasks', function () use ($app) {
        $app->get('', '\App\Controllers\TasksController:index');
        $app->post('', '\App\Controllers\TasksController:store');
        $app->get('/{id}', '\App\Controllers\TasksController:show');
        $app->put('/{id}', '\App\Controllers\TasksController:update');
        $app->patch('/{id}', '\App\Controllers\TasksController:delete');
        $app->delete('/{id}', '\App\Controllers\TasksController:destroy');
        //
        $app->get('/task/reminder', '\App\Controllers\TasksController:reminder');
    });
    // MAILS //
    $app->group('/mail', function () use ($app) {
        $app->post('', '\App\Controllers\MailerController:mail');
    });

    // -- NATIVA -- //
    // USERS //
    $app->get('/users', '\App\Controllers\Nativa\UsersController:index');
})->add(new KeyMiddleware());

// DATA //
$app->get('/tables', '\App\Controllers\TablesController:tables')->add(new AtdbMiddleware());
$app->get('/down', '\App\Controllers\TablesController:down')->add(new AtdbMiddleware());
$app->get('/defaults', '\App\Controllers\TablesController:defaults')->add(new AtdbMiddleware());

// START AP //
$app->run();