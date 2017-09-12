<?php

require_once("practice-controller.php");

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use \Commons\Authorization\Auth;
use Respect\Validation\Validator;
use Respect\Validation\Exceptions\NestedValidationException;

$app->get('/practice', function (ServerRequestInterface $request, ResponseInterface $response) {
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

  $pc = new practiceController();

  $practice = $pc->loadPractice($auth['practice']);

  if ($practice === false) {
    return jsonResponse($response, 404, [
      'code' => 404,
      'message' => 'PRACTICE_NOT_FOUND'
    ]);
  }

  $practice["id"] = (int) $practice["id"];

  $practice["language_id"] = (int) $practice["language_id"];

  $practice["valid_reminder"] = (bool) $practice["valid_reminder"];

  return jsonResponse($response, 200, $practice);
});

$app->patch('/practice', function (ServerRequestInterface $request, ResponseInterface $response) {
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

  $practiceValidator = Validator::key('company', Validator::stringType()
    ->length(4, 50), false)
    ->key('address', Validator::stringType(), false)
    ->key('phone', Validator::stringType(), false)
    ->key('contact_email', Validator::email(), false)
    ->key('system_email', Validator::email(), false)
    ->key('webpages', Validator::url(), false)
    ->key('language_id', Validator::numeric(), false);

  if (!$practiceValidator->validate($params)) {
    $messages = [];

    try {
      $practiceValidator->assert($params);
    } catch (NestedValidationException $exception) {
      $messages = $exception->getMessages();
    }

    return jsonResponse($response, 400, [
      "code" => 400,
      "message" => "INVALID_PARAMETERS_PROVIDED",
      "data" => $messages
    ]);
  }

  $pc = new practiceController();

  if (!$pc->editPractice($auth['practice'], $params)) {
    return jsonResponse($response, 500, [
      'code' => 500,
      'message' => 'PRACTICE_EDIT_ERROR'
    ]);
  }

  return jsonResponse($response, 200, [
    'code' => 200,
    'message' => 'PRACTICE_SAVED'
  ]);
});

$app->post('/practice', function (ServerRequestInterface $request, ResponseInterface $response) {
  $params = $request->getParsedBody();

  $practiceValidator = Validator::key('company', Validator::stringType()
    ->length(4, 50))
    ->key('address', Validator::stringType())
    ->key('phone', Validator::stringType())
    ->key('contact_email', Validator::email())
    ->key('webpages', Validator::url(), false)
    ->key('language_id', Validator::numeric())
    ->key('password', Validator::regex('/^([a-zA-Z0-9]{6,30})$/'))
    ->key('title', Validator::stringType(), false)
    ->key('name', Validator::stringType())
    ->key('surname', Validator::stringType())
    ->key('position_id', Validator::numeric()
      ->length(1, 9))
    ->key('gender', Validator::stringType()
      ->length(1));

  if (!$practiceValidator->validate($params)) {
    $messages = [];

    try {
      $practiceValidator->assert($params);
    } catch (NestedValidationException $exception) {
      $messages = $exception->getMessages();
    }

    return jsonResponse($response, 400, [
      "code" => 400,
      "message" => "INVALID_PARAMETERS_PROVIDED",
      "data" => $messages
    ]);
  }

  $pc = new practiceController();

  if (!$pc->newPractice($params)) {
    return jsonResponse($response, 500, [
      'code' => 500,
      'message' => 'PRACTICE_CREATION_ERROR'
    ]);
  }

  //@TODO: Send email about practice creation

  return jsonResponse($response, 200, [
    'code' => 200,
    'message' => 'PRACTICE_CREATED_EMAIL_SENT',
    'email' => $params['contact_email']
  ]);
});

$app->delete('/practice', function (ServerRequestInterface $request, ResponseInterface $response) {
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

  $pc = new practiceController();

  if (!$pc->deletePractice($auth['practice'], $auth['user'])) {
    return jsonResponse($response, 500, [
      'code' => 500,
      'message' => 'PRACTICE_DELETE_ERROR'
    ]);
  }

  return jsonResponse($response, 200, [
    'code' => 200,
    'message' => 'PRACTICE_DELETED'
  ]);
});
