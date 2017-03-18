<?php

require_once("language-controller.php");

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use \Commons\Authorization\Auth;
use Respect\Validation\Validator;

$app->get('/language[/{id}]', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
    $lc = new languageController();

    if (!isset($args['id'])) {
        $languages = $lc->loadLanguages();

        if ($languages === false) {
            return jsonResponse($response, 500, [
                "code" => 500,
                "message" => "LANGUAGE_LOAD_ERROR"
            ]);
        }

        return jsonResponse($response, 200, $languages);
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

        $language = $lc->loadLanguage($args['id']);

        if ($language === false) {
            return jsonResponse($response, 404, [
                "code" => 404,
                "message" => "LANGUAGE_NOT_FOUND"
            ]);
        }

        return jsonResponse($response, 200, $language);
    }
});
