<?php

namespace Woeplanet\API\Dependencies;

class ParamBuilderProvider implements \Pimple\ServiceProviderInterface {
    public function register(\Pimple\Container $container) {
        $container['param-builder'] = function($c) {
            return new \Woeplanet\API\ParamBuilder();
        };
    }
}
?>
