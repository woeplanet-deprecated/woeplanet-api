<?php

namespace Woeplanet\API\ErrorHandlers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class NotFoundHandler implements \Pimple\ServiceProviderInterface {
    public function register(\Pimple\Container $container) {
        $container['notFoundHandler'] = function($c) {
            return function(ServerRequestInterface $request, ResponseInterface $response) use($c) {
                $status = [
                    'code' => 404,
                    'reason' => 'Method not found'
                ];

                return $response = $response->withStatus($status['code'])
                    ->withHeader('Content-Type', 'application/json;charset=UTF-8')
                    ->write(json_encode($status));

            };
        };
    }
}
?>
