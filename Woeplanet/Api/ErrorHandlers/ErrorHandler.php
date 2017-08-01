<?php

namespace Woeplanet\API\ErrorHandlers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class ErrorHandler implements \Pimple\ServiceProviderInterface {
    public function register(\Pimple\Container $container) {
        $container['errorHandler'] = function($c) {
            return function(ServerRequestInterface $request, ResponseInterface $response) use($c) {
                $status = [
                    'code' => 500,
                    'reason' => 'Something went wrong'
                ];

                return $response = $response->withStatus($status['code'])
                    ->withHeader('Content-Type', 'application/json;charset=UTF-8')
                    ->write(json_encode($status));

            };
        };
    }
}
?>
