<?php

require_once("term-controller.php");

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
            ->validate($args['id']) || !Validator::numeric()
            ->length(1, 9)
            ->validate($args['clientId'])
    ) {
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
        ->validate($args['clientId'])
    ) {
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
        ->key('teeth_upper', Validator::stringType()
            ->length(16))
        ->key('teeth_lower', Validator::stringType()
            ->length(16))
        ->key('bleed_upper_inner', Validator::stringType()
            ->length(16))
        ->key('bleed_upper_outer', Validator::stringType()
            ->length(16))
        ->key('bleed_upper_middle', Validator::stringType()
            ->length(15))
        ->key('bleed_lower_inner', Validator::stringType()
            ->length(16))
        ->key('bleed_lower_outer', Validator::stringType()
            ->length(16))
        ->key('bleed_lower_middle', Validator::stringType()
            ->length(15))
        ->key('stix_upper', Validator::stringType()
            ->length(15))
        ->key('stix_lower', Validator::stringType()
            ->length(15))
        ->key('pass_upper', Validator::stringType()
            ->length(15))
        ->key('pass_lower', Validator::stringType()
            ->length(15))
        ->key('tartar', Validator::stringType()
            ->length(2))
        ->key('next_date', Validator::date())
        ->key('note', Validator::stringType(), false);

    if (!$termValidator->validate($params) || !Validator::numeric()
            ->length(1, 9)
            ->validate($args['clientId'])
    ) {
        return jsonResponse($response, 400, [
            "code" => 400,
            "message" => "INVALID_PARAMETERS_PROVIDED"
        ]);
    }

    $tc = new termController();

    if (!$tc->newTerm($auth["user"], $args['clientId'], $params)) {
        return jsonResponse($response, 500, [
            'code' => 500,
            'message' => 'TERM_CREATION_ERROR'
        ]);
    }

    return jsonResponse($response, 200, [
        'code' => 200,
        'message' => 'NEW_TERM_CREATED'
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
            ->length(2), false)
        ->key('next_date', Validator::date(), false)
        ->key('note', Validator::stringType(), false);

    if (!Validator::numeric()
            ->length(1, 9)
            ->validate($args['id']) || !Validator::numeric()
            ->length(1, 9)
            ->validate($args['clientId']) || !$termValidator->validate($params)
    ) {
        return jsonResponse($response, 400, [
            "code" => 400,
            "message" => "INVALID_PARAMETERS_PROVIDED"
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
