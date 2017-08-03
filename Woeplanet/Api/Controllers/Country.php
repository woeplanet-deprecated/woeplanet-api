<?php

namespace Woeplanet\API\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Woeplanet\API\Index as Index;
use Woeplanet\API\Routes as Routes;

class Country extends Controller {
    public function __construct(\Pimple\Container $container) {
        parent::__construct($container);
    }

    public function getCountry(ServerRequestInterface $request, ResponseInterface $response, $args) {
        $status = $this->checkValidationErrors($request);
        if ($status['api:status']['code'] === 200) {
            $iso = strtoupper($args['iso']);
            $pb = $this->c['param-builder'];
            $params = $pb->buildParams(Routes::COUNTRY, $request);

            try {
                $data = $this->get_country($iso);
                if (!isset($data['hits']) || empty($data['hits'])) {
                    throw new \Elasticsearch\Common\Exceptions\Missing404Exception('Cannot find country definitions');
                }

                $doc = $data['hits']['hits'][0]['_source'];
                $doc['properties']['api:status'] = [
                    'code' => 200,
                    'reason' => 'OK'
                ];

                $formatter = $this->c['response-formatter'];
                $doc = $formatter->format($doc, $params['properties']);

                return $this->response($response, $doc['properties']['api:status']['code'], $doc);
            }

            catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
                $data = [
                    'api:status' => [
                        'code' => 404,
                        'reason' => 'Country not found',
                        'message' => $e->getMessage()
                    ]
                ];
                return $this->response($response, $data['api:status']['code'], $data);
            }

            catch (\Exception $e) {
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

    public function getCountries(ServerRequestInterface $request, ResponseInterface $response, $args) {
        $status = $this->checkValidationErrors($request);
        if ($status['api:status']['code'] === 200) {
            $pb = $this->c['param-builder'];
            $params = $pb->buildParams(Routes::COUNTRIES, $request);

            try {
                $data = $this->get_countries($params);
                if (!isset($data['hits']) || empty($data['hits'])) {
                    throw new \Elasticsearch\Common\Exceptions\Missing404Exception('Cannot find country definitions');
                }

                $doc = [
                    'api:status' => [
                        'code' => 200,
                        'reason' => 'OK'
                    ],
                    'api:total' => $data['hits']['total'],
                    'api:hits' => []
                ];

                $formatter = $this->c['response-formatter'];
                foreach ($data['hits']['hits'] as $hit) {
                    $doc['api:hits'][] = $formatter->format($hit['_source'], $params['properties']);
                }

                return $this->response($response, $doc['api:status']['code'], $doc);
            }

            catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
                $data = [
                    'api:status' => [
                        'code' => 404,
                        'reason' => 'Country not found',
                        'message' => $e->getMessage()
                    ]
                ];
                return $this->response($response, $data['api:status']['code'], $data);
            }

            catch (\Exception $e) {
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

    private function get_country($iso) {
        $params = [
            'index' => Index::INDEX,
            'type' => Index::TYPE_PLACES,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'term' => [
                                    'properties.woe:iso' => $iso
                                ]
                            ],
                            [
                                'term' => [
                                    'properties.woe:placetype' => 12
                                ]
                            ]
                        ],
                        'must_not' => [
                            [
                                'term' => [
                                    'properties.woe:placetype' => 0
                                ]
                            ]
                        ],
                        'should' => [
                            [
                                'match_all' => new \stdClass()
                            ]
                        ],
                        'filter' => []
                    ]
                ],
                'sort' => [
                    [
                        'properties.woe:area' => [
                            'mode' => 'max',
                            'order' => 'desc'
                        ]
                    ]
                ]
            ]
        ];
        return $this->c['search']->search($params);
    }

    private function get_countries($params) {
        $params = [
            'index' => Index::INDEX,
            'type' => Index::TYPE_PLACES,
            'body' => [
                'size' => $params['size'],
                'from' => $params['from'],
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'term' => [
                                    'properties.woe:placetype' => 12
                                ]
                            ]
                        ],
                        'must_not' => [
                            [
                                'term' => [
                                    'properties.woe:placetype' => 0
                                ]
                            ]
                        ],
                        'should' => [
                            [
                                'match_all' => new \stdClass()
                            ]
                        ],
                        'filter' => []
                    ]
                ],
                'sort' => [
                    [
                        'properties.woe:area' => [
                            'mode' => 'max',
                            'order' => 'desc'
                        ]
                    ]
                ]
            ]
        ];
        return $this->c['search']->search($params);
    }
}
?>
