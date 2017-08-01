<?php

namespace Woeplanet\API\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Woeplanet\API\Index as Index;
use Woeplanet\API\Routes as Routes;

class Meta extends Controller {
    public function __construct(\Pimple\Container $container) {
        parent::__construct($container);
    }

    public function getMeta(ServerRequestInterface $request, ResponseInterface $response, $args) {
        $status = $this->checkValidationErrors($request);
        if ($status['api:status']['code'] === 200) {
            try {
                $meta = $this->get_meta();
                $places = $this->get_place_count();
                $placetypes = $this->get_placetype_count();

                $doc = $meta['_source'];
                $doc['woe:places'] = $places['count'];
                $doc['woe:placetypes'] = $placetypes['count'];
                $doc['api:status'] = [
                        'code' => 200,
                        'reason' => 'OK'
                ];

                return $this->response($response, $doc['api:status']['code'], $doc);
            }

            catch (\Exception $e) {
                return $this->response($response, $status['code'], $status);
                $data = [
                    'api:status' => [
                        'code' => 503,
                        'reason' => 'Woe is me. Something has gone horribly wrong',
                        'message' => $e->getMessage()
                    ]
                ];
                return $this->response($response, $data['api:status']['code'], $data);
            }
        }

        return $this->response($response, $status['api:status']['code'], $status);
    }

    private function get_meta() {
        $id = 1;

        return $this->c['search']->get([
            'index' => Index::INDEX,
            'type' => Index::TYPE_META,
            'id' => intval($id)
        ]);
    }

    private function get_place_count() {
        return $this->c['search']->count([
            'index' => Index::INDEX,
            'type' => Index::TYPE_PLACES
        ]);
    }

    private function get_placetype_count() {
        return $this->c['search']->count([
            'index' => Index::INDEX,
            'type' => Index::TYPE_PLACETYPES
        ]);
    }
}

?>
