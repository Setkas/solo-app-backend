<?php

require_once("user-controller.php");

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use \Commons\Authorization\Auth;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;

$app->get('/user', function (ServerRequestInterface $request, ResponseInterface $response) {
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

  $uc = new userController();

  $user = $uc->loadUser($auth['practice'], $auth['user']);

  if ($user === false) {
    return jsonResponse($response, 500, [
      'code' => 500,
      'message' => 'USER_LOAD_ERROR'
    ]);
  }

  $user["code"] = (int) $user["code"];

  $user["gender"] = (int) $user["gender"];

  $user["id"] = (int) $user["id"];

  $user["position_id"] = (int) $user["position_id"];

  $user["practice_id"] = (int) $user["practice_id"];

  $user["reset_password"] = (bool) $user["reset_password"];

  return jsonResponse($response, 200, $user);
});

$app->get('/users', function (ServerRequestInterface $request, ResponseInterface $response) {
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

  $uc = new userController();

  if (!$uc->isMasterUser($auth['user'])) {
    return jsonResponse($response, 401, [
      'code' => 401,
      'message' => 'ACTION_NOT_PERMITTED'
    ]);
  }

  $users = $uc->loadUsers($auth['practice']);

  if ($users === false) {
    return jsonResponse($response, 500, [
      'code' => 500,
      'message' => 'USERS_LOAD_ERROR'
    ]);
  }

  foreach ($users as $key => $user) {
    $users[$key]["code"] = (int) $user["code"];

    $users[$key]["gender"] = (int) $user["gender"];

    $users[$key]["id"] = (int) $user["id"];

    $users[$key]["position_id"] = (int) $user["position_id"];

    $users[$key]["practice_id"] = (int) $user["practice_id"];

    $users[$key]["reset_password"] = (bool) $user["reset_password"];
  }

  return jsonResponse($response, 200, $users);
});

$app->post('/user', function (ServerRequestInterface $request, ResponseInterface $response) {
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

  $userValidator = Validator::key('position_id', Validator::numeric()
    ->length(1, 9))
    ->key('password', Validator::regex('/^([a-zA-Z0-9]{6,30})$/'), false)
    ->key('title', Validator::stringType(), false)
    ->key('name', Validator::stringType())
    ->key('surname', Validator::stringType())
    ->key('gender', Validator::numeric()
      ->length(1));

  if (!$userValidator->validate($params)) {
    $messages = [];

    try {
      $userValidator->assert($params);
    } catch (NestedValidationException $exception) {
      $messages = $exception->getMessages();
    }

    return jsonResponse($response, 400, [
      "code" => 400,
      "message" => "INVALID_PARAMETERS_PROVIDED",
      "data" => $messages
    ]);
  }

  $uc = new userController();

  $pc = new practiceController();

  if (!$uc->isMasterUser($auth['user'])) {
    return jsonResponse($response, 401, [
      'code' => 401,
      'message' => 'ACTION_NOT_PERMITTED'
    ]);
  }

  if (!isset($params["password"])) {
    $params["password"] = $uc->generatePassword();

    $params["reset_password"] = true;
  }

  $practice = $pc->loadPractice($auth['practice']);

  $mailer = new \Commons\Mailer\mailer();

  $userCode = $uc->newUser($auth['practice'], $params);

  if ($practice == false || $userCode != false
      || !$mailer->sendMail($practice['contact_email'], "new_user", "New User Created", [
      "password" => $params["password"],
      "userNumber" => $userCode
    ])) {
    return jsonResponse($response, 500, [
      'code' => 500,
      'message' => 'USER_CREATION_ERROR'
    ]);
  }

  return jsonResponse($response, 200, [
    'code' => 200,
    'message' => 'NEW_USER_CREATED'
  ]);
});

$app->patch('/user', function (ServerRequestInterface $request, ResponseInterface $response) {
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

  $userValidator = Validator::key('position_id', Validator::numeric()
    ->length(1, 9), false)
    ->key('password', Validator::regex('/^([a-zA-Z0-9]{6,30})$/'), false)
    ->key('title', Validator::stringType(), false)
    ->key('name', Validator::stringType(), false)
    ->key('surname', Validator::stringType(), false)
    ->key('gender', Validator::numeric()
      ->length(1), false)
    ->key('reset_password', Validator::boolType(), false);

  if (!$userValidator->validate($params)) {
    $messages = [];

    try {
      $userValidator->assert($params);
    } catch (NestedValidationException $exception) {
      $messages = $exception->getMessages();
    }

    return jsonResponse($response, 400, [
      "code" => 400,
      "message" => "INVALID_PARAMETERS_PROVIDED",
      "data" => $messages
    ]);
  }

  $uc = new userController();

  if (!$uc->updateUser($auth['practice'], $auth['user'], $params)) {
    return jsonResponse($response, 500, [
      'code' => 500,
      'message' => 'USER_UPDATE_ERROR'
    ]);
  }

  return jsonResponse($response, 200, [
    'code' => 200,
    'message' => 'USER_EDIT_SUCCESS'
  ]);
});

$app->delete('/user/{id}', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
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
    ->validate($args['id'])) {
    return jsonResponse($response, 400, [
      "code" => 400,
      "message" => "INVALID_PARAMETERS_PROVIDED"
    ]);
  }

  $uc = new userController();

  if (!$uc->isMasterUser($auth['user']) || $uc->isMasterUser($args['id'])) {
    return jsonResponse($response, 401, [
      'code' => 401,
      'message' => 'ACTION_NOT_PERMITTED'
    ]);
  }

  if (!$uc->deleteUser($auth['practice'], $args['id'])) {
    return jsonResponse($response, 500, [
      'code' => 500,
      'message' => 'USER_DELETE_ERROR'
    ]);
  }

  return jsonResponse($response, 200, [
    'code' => 200,
    'message' => 'USER_DELETED'
  ]);
});

$app->get('/user/{id}', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
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
    ->validate($args['id'])) {
    return jsonResponse($response, 400, [
      "code" => 400,
      "message" => "INVALID_PARAMETERS_PROVIDED"
    ]);
  }

  $uc = new userController();

  if (!$uc->isMasterUser($auth['user']) || $uc->isMasterUser($args['id'])) {
    return jsonResponse($response, 401, [
      'code' => 401,
      'message' => 'ACTION_NOT_PERMITTED'
    ]);
  }

  if (!$uc->deleteUser($auth['practice'], $args['id'])) {
    return jsonResponse($response, 500, [
      'code' => 500,
      'message' => 'USER_DELETE_ERROR'
    ]);
  }

  return jsonResponse($response, 200, [
    'code' => 200,
    'message' => 'USER_DELETED'
  ]);
});

