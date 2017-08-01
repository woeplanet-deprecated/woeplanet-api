<?php

namespace Woeplanet\API\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Woeplanet\API\Index as Index;
use Woeplanet\API\Routes as Routes;

class PlaceType extends Controller {
    public function __construct(\Pimple\Container $container) {
        parent::__construct($container);
    }

    public function getPlaceType(ServerRequestInterface $request, ResponseInterface $response, $args) {
        $status = $this->checkValidationErrors($request);
        if ($status['api:status']['code'] === 200) {
            $ptid = $args['id'];

            try {
                $doc = $this->get_placetype($ptid);
                if (!$doc['found'] || !isset($doc['_source']) || empty($doc['_source'])) {
                    throw new \Elasticsearch\Common\Exceptions\Missing404Exception(sprintf('WOEID %d does not seem to exist', $woeid));
                }

                $doc = $doc['_source'];
                $doc['api:status'] = $status['api:status'];
                return $this->response($response, $doc['api:status']['code'], $doc);
            }

            catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
                $data = [
                    'api:status' => [
                        'code' => 404,
                        'reason' => 'Placetype not found',
                        'message' => $e->getMessage()
                    ]
                ];
                return $this->response($response, $data['api:status']['code'], $data);
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

    public function getPlaceTypes(ServerRequestInterface $request, ResponseInterface $response, $args) {
        $status = $this->checkValidationErrors($request);
        if ($status['api:status']['code'] === 200) {
            try {
                $data = $this->search_placetypes();
                if (!isset($data['hits']) || empty($data['hits'])) {
                    throw new \Elasticsearch\Common\Exceptions\Missing404Exception('Cannot find placetype definitions');
                }

                $doc = [
                    'api:status' => [
                        'code' => 200,
                        'reason' => 'OK'
                    ],
                    'api:total' => $data['hits']['total'],
                    'api:hits' => []
                ];

                foreach ($data['hits']['hits'] as $hit) {
                    $doc['api:hits'][] = $hit['_source'];
                }

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

    private function get_placetype($ptid) {
        return $this->c['search']->get([
            'index' => Index::INDEX,
            'type' => Index::TYPE_PLACETYPES,
            'id' => intval($ptid)
        ]);
    }

    private function search_placetypes() {
        return $this->c['search']->search([
            'index' => Index::INDEX,
            'type' => Index::TYPE_PLACETYPES,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            'match_all' => new \stdClass()
                        ]
                    ]
                ]
            ]
        ]);
    }

}

?>
