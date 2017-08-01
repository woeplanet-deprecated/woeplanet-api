<?php

namespace Woeplanet\API\Dependencies;

use Elasticsearch\ClientBuilder;

class SearchProvider implements \Pimple\ServiceProviderInterface {
    public function register(\Pimple\Container $container) {
        $container['search'] = function($c) {
            $index = $c->get('settings')['search-index'];
            $hosts = [
                $index
            ];
            $search = ClientBuilder::create()
                ->setHosts($hosts)
                ->build();
            return $search;
        };
    }
}
?>
