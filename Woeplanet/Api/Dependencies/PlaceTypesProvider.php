<?php

namespace Woeplanet\API\Dependencies;

class PlaceTypesProvider implements \Pimple\ServiceProviderInterface {
    public function register(\Pimple\Container $container) {
        $container['placetypes'] = function($c) {
            return new \Woeplanet\Types\PlaceTypes();
        };
    }
}
?>
