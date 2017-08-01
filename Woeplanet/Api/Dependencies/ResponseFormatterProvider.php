<?php

namespace Woeplanet\API\Dependencies;

class ResponseFormatterProvider implements \Pimple\ServiceProviderInterface {
    public function register(\Pimple\Container $container) {
        $container['response-formatter'] = function($c) {
            return new \Woeplanet\API\ResponseFormatter($c);
        };
    }
}
?>
