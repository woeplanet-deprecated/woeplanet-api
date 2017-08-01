<?php

namespace Woeplanet\API;

class Config {
    private $config;

    public function __construct() {
        $defaults = [
            'displayErrorDetails' => true,
            'logger' => [
                'name' => 'woeplanet-api',
                'path' => dirname(__FILE__) . '/../../logs/api-' . date('Y-m-d') . '.log',
                'log.severity' => \Monolog\Logger::DEBUG
            ],
            'data-stores' => [
                'whosonfirst' => '/var/woeplanet/data/whosonfirst'
            ],
            'search-index' => 'localhost:9200'
        ];

        $host_settings = [];
        switch ($_SERVER['HTTP_HOST']) {
            case 'api.woeplanet.dev':
                $host_settings = [
                    'data-stores' => [
                        'whosonfirst' => '/Users/gary/data/whosonfirst/data'
                    ]
                ];
                break;

            default:
                break;
        }

        $this->config = [
            'settings' => array_replace_recursive($defaults, $host_settings)
        ];
    }

    public function settings() {
        return $this->config['settings'];
    }
}

?>
