<?php

require_once("setup-controller.php");

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use \Commons\Authorization\Auth;

$app->get('/setup', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
  if (!$request->hasHeader('Authorization')) {
    return jsonResponse($response, 401, [
      'code' => 401,
      'message' => 'INVALID_ACCESS'
    ]);
  }

  $auth = Auth::checkToken($request->getHeader('Authorization')[0]);

  if (!$auth) {
    return jsonResponse($response, 401, [
      'code' => 401,
      'message' => 'ACCESS_TOKEN_INVALID'
    ]);
  }

  $sc = new setupController();

  $setup = $sc->loadSetup($auth['user']);

  if ($setup === false) {
    return jsonResponse($response, 200, []);
  }

  return jsonResponse($response, 200, $setup);
});

$app->patch('/setup', function (ServerRequestInterface $request, ResponseInterface $response) {
  if (!$request->hasHeader('Authorization')) {
    return jsonResponse($response, 401, [
      'code' => 401,
      'message' => 'INVALID_ACCESS'
    ]);
  }

  $auth = Auth::checkToken($request->getHeader('Authorization')[0]);

  if (!$auth) {
    return jsonResponse($response, 401, [
      'code' => 401,
      'message' => 'ACCESS_TOKEN_INVALID'
    ]);
  }

  $sc = new setupController();

  $params = $request->getParsedBody();

  $result = $sc->saveSetup($auth['user'], $params);

  if ($result === false) {
    return jsonResponse($response, 500, [
      'code' => 500,
      'message' => 'SETUP_UPDATE_FAILED'
    ]);
  }

  return jsonResponse($response, 200, [
    'code' => 200,
    'message' => 'SETUP_SAVED'
  ]);
});
