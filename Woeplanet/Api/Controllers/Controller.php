<?php

namespace Woeplanet\API\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class Controller {
    protected $c;

    public function __construct(\Pimple\Container $container) {
        $this->c = $container;
    }

    protected function response(ResponseInterface $response, $code, $data) {
        return $response = $response->withStatus($code)
            ->withHeader('Content-Type', 'application/json;charset=UTF-8')
            ->write(json_encode($data));
    }

    protected function hasQueryParam($params, $key) {
        return (isset($params[$key]) && !empty($params[$key]));
    }

    protected function checkValidationErrors(ServerRequestInterface $request) {
        $status = [
            'api:status' => [
                'code' => 200,
                'reason' => 'OK'
            ]
        ];

        if ($request->getAttribute('has_errors')) {
            $errors = $request->getAttribute('errors');
            error_log(var_export($errors, true));

            $status = [
                'api:status' => [
                    'code' => 400,
                    'reason' => 'Bad Request',
                    'message' => $this->collateValidationErrors($errors)
                ]
            ];
        }

        return $status;
    }

    protected function collateValidationErrors($errors) {
        $entries = [];

        foreach ($errors as $param => $messages) {
            $entries[] = sprintf('%s: %s', $param, implode(', ', $messages));
        }

        return sprintf('Parameter validation failure: %s', implode(', ', $entries));
    }
}
?>
