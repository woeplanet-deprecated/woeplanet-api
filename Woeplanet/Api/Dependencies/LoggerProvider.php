<?php

namespace Woeplanet\API\Dependencies;

class LoggerProvider implements \Pimple\ServiceProviderInterface {
    public function register(\Pimple\Container $container) {
        $container['logger'] = function($c) {
            $settings = $c->get('settings')['logger'];

            $logger = new \Monolog\Logger($settings['name']);
            $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
            $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['log.severity']));
            return $logger;
        };
    }
}

?>
