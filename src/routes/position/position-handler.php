<?php

require_once("position-controller.php");

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator;

$app->get('/position[/{id}]', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
    $lc = new positionController();

    if (!isset($args['id'])) {
        $positions = $lc->loadPositions();

        if ($positions === false) {
            return jsonResponse($response, 500, [
                "code" => 500,
                "message" => "POSITION_LOAD_ERROR"
            ]);
        }

        return jsonResponse($response, 200, $positions);
    } else {
        if (!Validator::numeric()
            ->length(1, 9)
            ->validate($args['id'])
        ) {
            return jsonResponse($response, 400, [
                "code" => 400,
                "message" => "INVALID_PARAMETERS_PROVIDED"
            ]);
        }

        $position = $lc->loadPosition($args['id']);

        if ($position === false) {
            return jsonResponse($response, 404, [
                "code" => 404,
                "message" => "POSITION_NOT_FOUND"
            ]);
        }

        return jsonResponse($response, 200, $position);
    }
});
