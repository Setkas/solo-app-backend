<?php

require_once("term-controller.php");
require_once("./src/routes/client/client-controller.php");

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use \Commons\Authorization\Auth;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;

$app->get('/term/{clientId}/{id}', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
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

  if (!Validator::numeric()
      ->length(1, 9)
      ->validate($args['id'])
      || !Validator::numeric()
      ->length(1, 9)
      ->validate($args['clientId'])) {
    return jsonResponse($response, 400, [
      "code" => 400,
      "message" => "INVALID_PARAMETERS_PROVIDED"
    ]);
  }

  $tc = new termController();

  $term = $tc->loadTerm($args['clientId'], $args['id']);

  if ($term === false) {
    return jsonResponse($response, 500, [
      'code' => 500,
      'message' => 'TERM_LOAD_ERROR'
    ]);
  }

  $term["teeth"] = json_decode($term["teeth"]);

  $term["bleed_inner"] = json_decode($term["bleed_inner"]);

  $term["bleed_outer"] = json_decode($term["bleed_outer"]);

  $term["bleed_middle"] = json_decode($term["bleed_middle"]);

  $term["stix"] = json_decode($term["stix"]);

  $term["pass"] = json_decode($term["pass"]);

  $term["tartar"] = json_decode($term["tartar"]);

  return jsonResponse($response, 200, $term);
});

$app->get('/terms/{clientId}', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
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

  if (!Validator::numeric()
    ->length(1, 9)
    ->validate($args['clientId'])) {
    return jsonResponse($response, 400, [
      "code" => 400,
      "message" => "INVALID_PARAMETERS_PROVIDED"
    ]);
  }

  $tc = new termController();

  $terms = $tc->loadTerms($args['clientId']);

  if ($terms === false) {
    return jsonResponse($response, 404, [
      'code' => 404,
      'message' => 'TERM_LOAD_ERROR'
    ]);
  }

  foreach ($terms as $key => $term) {
    $terms[$key]["teeth"] = json_decode($term["teeth"]);

    $terms[$key]["bleed_inner"] = json_decode($term["bleed_inner"]);

    $terms[$key]["bleed_outer"] = json_decode($term["bleed_outer"]);

    $terms[$key]["bleed_middle"] = json_decode($term["bleed_middle"]);

    $terms[$key]["stix"] = json_decode($term["stix"]);

    $terms[$key]["pass"] = json_decode($term["pass"]);

    $terms[$key]["tartar"] = json_decode($term["tartar"]);
  }

  return jsonResponse($response, 200, $terms);
});

$app->post('/term/{clientId}', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
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

  $params = $request->getParsedBody();

  $termValidator = Validator::key('date', Validator::date())
    ->key('teeth', Validator::arrayType()
      ->length(32, 32))
    ->key('bleed_inner', Validator::arrayType()
      ->length(32, 32))
    ->key('bleed_outer', Validator::arrayType()
      ->length(32, 32))
    ->key('bleed_middle', Validator::arrayType()
      ->length(30, 30))
    ->key('stix', Validator::arrayType()
      ->length(30, 30))
    ->key('pass', Validator::arrayType()
      ->length(30, 30))
    ->key('tartar', Validator::arrayType()
      ->length(2, 2))
    ->key('next_date', Validator::date())
    ->key('note', Validator::stringType(), false);

  if (!$termValidator->validate($params)
      || !Validator::numeric()
      ->length(1, 9)
      ->validate($args['clientId'])) {
    $messages = [];

    try {
      $termValidator->assert($params);
    } catch (NestedValidationException $exception) {
      $messages = $exception->getMessages();
    }

    return jsonResponse($response, 400, [
      "code" => 400,
      "message" => "INVALID_PARAMETERS_PROVIDED",
      "data" => $messages
    ]);
  }

  $tc = new termController();

  $lastId = $tc->newTerm($auth["user"], $args['clientId'], $params);

  if (!$lastId) {
    return jsonResponse($response, 500, [
      'code' => 500,
      'message' => 'TERM_CREATION_ERROR'
    ]);
  }

  return jsonResponse($response, 200, [
    'code' => 200,
    'message' => 'NEW_TERM_CREATED',
    'data' => $lastId
  ]);
});

$app->patch('/term/{clientId}/{id}', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
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

  $params = $request->getParsedBody();

  $termValidator = Validator::key('date', Validator::date(), false)
    ->key('teeth', Validator::arrayType()
      ->length(32, 32), false)
    ->key('bleed_inner', Validator::arrayType()
      ->length(32, 32), false)
    ->key('bleed_outer', Validator::arrayType()
      ->length(32, 32), false)
    ->key('bleed_middle', Validator::arrayType()
      ->length(30, 30), false)
    ->key('stix', Validator::arrayType()
      ->length(30, 30), false)
    ->key('pass', Validator::arrayType()
      ->length(30, 30), false)
    ->key('tartar', Validator::arrayType()
      ->length(2, 2), false)
    ->key('next_date', Validator::date(), false)
    ->key('note', Validator::stringType(), false);

  if (!Validator::numeric()
      ->length(1, 9)
      ->validate($args['id'])
      || !Validator::numeric()
      ->length(1, 9)
      ->validate($args['clientId'])
      || !$termValidator->validate($params)) {
    $messages = [];

    try {
      $termValidator->assert($params);
    } catch (NestedValidationException $exception) {
      $messages = $exception->getMessages();
    }

    return jsonResponse($response, 400, [
      "code" => 400,
      "message" => "INVALID_PARAMETERS_PROVIDED",
      "data" => $messages
    ]);
  }

  $tc = new termController();

  if (!$tc->updateTerm($auth['user'], $args['clientId'], $args['id'], $params)) {
    return jsonResponse($response, 500, [
      'code' => 500,
      'message' => 'TERM_UPDATE_ERROR'
    ]);
  }

  return jsonResponse($response, 200, [
    'code' => 200,
    'message' => 'TERM_EDIT_SUCCESS'
  ]);
});

$app->get('/term-image/{clientId}/{id}', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
  if (!Validator::numeric()
      ->length(1, 9)
      ->validate($args['id'])
      || !Validator::numeric()
      ->length(1, 9)
      ->validate($args['clientId'])) {
    $handler = $this->notFoundHandler;

    return $handler($request, $response);
  }

  $cc = new clientController();

  $client = $cc->getClientName($args['clientId']);

  if ($client === false) {
    $handler = $this->notFoundHandler;

    return $handler($request, $response);
  }

  $tc = new termController();

  $image = $tc->generateImage($args['clientId'], $args['id'], $client);

  if ($image === false) {
    return jsonResponse($response, 400, [
      "code" => 404,
      "message" => "TERM_IMAGE_LOAD_FAILED"
    ]);
  }

  $base64 = 'data:image/png;base64,' . base64_encode($image);

  return jsonResponse($response, 200, [
    'code' => 200,
    'message' => 'TERM_IMAGE_LOADED',
    'data' => $base64
  ]);
});

$app->post('/term-email/{clientId}/{id}', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
  if (!Validator::numeric()
      ->length(1, 9)
      ->validate($args['id'])
      || !Validator::numeric()
      ->length(1, 9)
      ->validate($args['clientId'])) {
    return jsonResponse($response, 400, [
      "code" => 400,
      "message" => "INVALID_PARAMETERS_PROVIDED"
    ]);
  }

  $cc = new clientController();

  $client = $cc->getClientName($args['clientId']);

  if ($client === false || $client["email"] === null || strlen($client["email"]) === 0) {
    return jsonResponse($response, 400, [
      "code" => 400,
      "message" => "INVALID_CLIENT_PROVIDED"
    ]);
  }

  $tc = new termController();

  $image = $tc->generateImage($args['clientId'], $args['id'], $client);

  if ($image === false) {
    return jsonResponse($response, 500, [
      'code' => 500,
      'message' => 'TERM_EMAIL_FAILED'
    ]);
  }

  //@TODO: Send email with image to client

  return jsonResponse($response, 200, [
    'code' => 200,
    'message' => 'TERM_EMAIL_SENT'
  ]);
});
