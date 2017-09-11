<?php

require_once("import-controller.php");

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;

$app->get('/import/{id}', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
  $getParams = $request->getParsedBody();

  try {
    if (!isset($getParams["import"])) {
      throw new Exception();
    }

    $params = json_decode($getParams, true);
  } catch (Exception $e) {
    return jsonResponse($response, 400, [
      "code" => 400,
      "message" => "INVALID_PARAMETERS_PROVIDED"
    ]);
  }

  $importValidator = Validator::key('birth_date', Validator::date(), false)
    ->key('address', Validator::stringType(), false)
    ->key('gender', Validator::numeric()
      ->length(1), false)
    ->key('phone', Validator::stringType(), false)
    ->key('email', Validator::email(), false)
    ->key('import_id', Validator::numeric()
      ->length(1, 9))
    ->key('name', Validator::stringType())
    ->key('surname', Validator::stringType())
    ->key('teeth_upper', Validator::stringType()
      ->length(16), false)
    ->key('teeth_lower', Validator::stringType()
      ->length(16), false)
    ->key('bleed_upper_inner', Validator::stringType()
      ->length(16), false)
    ->key('bleed_upper_outer', Validator::stringType()
      ->length(16), false)
    ->key('bleed_upper_middle', Validator::stringType()
      ->length(15), false)
    ->key('bleed_lower_inner', Validator::stringType()
      ->length(16), false)
    ->key('bleed_lower_outer', Validator::stringType()
      ->length(16), false)
    ->key('bleed_lower_middle', Validator::stringType()
      ->length(15), false)
    ->key('stix_upper', Validator::stringType()
      ->length(15), false)
    ->key('stix_lower', Validator::stringType()
      ->length(15), false)
    ->key('pass_upper', Validator::stringType()
      ->length(15), false)
    ->key('pass_lower', Validator::stringType()
      ->length(15), false)
    ->key('tartar', Validator::stringType()
      ->length(2), false);

  if (!Validator::numeric()
      ->length(1, 9)
      ->validate($args['id'])
      || !$importValidator->validate($params)) {
    $messages = [];

    try {
      $importValidator->assert($params);
    } catch (NestedValidationException $exception) {
      $messages = $exception->getMessages();
    }

    return jsonResponse($response, 400, [
      "code" => 400,
      "message" => "INVALID_PARAMETERS_PROVIDED",
      "data" => $messages
    ]);
  }

  $ic = new importController();

  $result = $ic->importClient($args['id'], $params);

  if ($result === false) {
    return jsonResponse($response, 500, [
      'code' => 500,
      'message' => 'CLIENT_IMPORT_ERROR'
    ]);
  }

  return jsonResponse($response, 200, [
    'code' => 200,
    'message' => 'CLIENT_IMPORT_SUCCESS'
  ]);
});
