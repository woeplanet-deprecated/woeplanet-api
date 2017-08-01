<?php

namespace Woeplanet\API\Dependencies;

class SearchQueryBuilderProvider implements \Pimple\ServiceProviderInterface {
    public function register(\Pimple\Container $container) {
        $container['search-query'] = function($c) {
            return new \Woeplanet\API\SearchQueryBuilder();
        };
    }
}
?>
