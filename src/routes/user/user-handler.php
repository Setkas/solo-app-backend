<?php

require_once("user-controller.php");

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

$app->get('/user', function (ServerRequestInterface $request, ResponseInterface $response) {
    return jsonResponse($response, 200, userController::getData());
});