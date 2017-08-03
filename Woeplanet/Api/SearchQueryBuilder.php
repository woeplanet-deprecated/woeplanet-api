<?php

namespace Woeplanet\API;

use Woeplanet\API\Parameters as Parameters;
use Woeplanet\API\Routes as Routes;

class SearchQueryBuilder {
    const PLACETYPE_SIZE = 50;
    const COUNTRY_SIZE = 275;

    private $facets;
    private $filters;
    private $sort;
    private $query_template;
    private $fields_template;

    public function __construct() {
        $this->facets = [
            'placetype' => [
                'terms' => [
                    'field' => 'properties.woe:placetype',
                    'size' => self::PLACETYPE_SIZE
                ]
            ],
            'country' => [
                'terms' => [
                    'field' => 'properties.woe:iso',
                    'size' => self::COUNTRY_SIZE
                ]
            ]
        ];

        $this->filters = [
            Parameters::PLACETYPE => [
                'term' => [
                    'properties.woe:placetype' => NULL
                ]
            ],
            Parameters::ISO => [
                'term' => [
                    'properties.woe:iso' => NULL
                ]
            ]
        ];

        $this->sort = [
            'properties.woe:area' => [
                'mode' => 'max',
                'order' => 'desc'
            ]
        ];

        $this->query_template = [
            'size' => Parameters::SIZE_DEFAULT,
            'from' => Parameters::FROM_DEFAULT,
            'query' => [
                'bool' => [
                    'must' => [],
                    'must_not' => [],
                    'should' => [],
                    'filter' => []
                ]
            ]
        ];

        $this->fields_template = [
            'size' => Parameters::SIZE_DEFAULT,
            'from' => Parameters::FROM_DEFAULT,
            'query' => [
                'function_score' => [
                    'boost_mode' => 'multiply',
                    'functions' => [
                        [
                            'filter' => [
                                'term' => [
                                    'names_preferred' => NULL
                                ]
                            ],
                            'weight' => 3.0
                        ],
                        [
                            'filter' => [
                                'term' => [
                                    'names_all' => NULL
                                ]
                            ],
                            'weight' => 1.0
                        ],
                        [
                            'filter' => [
                                'term' => [
                                    'woe:name' => NULL
                                ]
                            ],
                            'weight' => 2.0
                        ]
                    ],
                    'query' => [
                        'bool' => [
                            'must' => [],
                            'must_not' => [],
                            'should' => [],
                            'filter' => []
                        ]
                    ],
                    'score_mode' => 'multiply'
                ]
            ]
        ];
    }

    public function buildQuery($route, $params) {
        $query = [];

        switch ($route) {
            case Routes::SEARCH:
                $query = $this->query_template;
                $query['size'] = $params['size'];
                $query['from'] = $params['from'];
                $query['query']['bool']['must'][] = [
                    'match_all' => new \stdClass()
                ];
                if (!$params['superceded']) {
                    $query['query']['bool']['must_not'][] = [
                        'exists' => [
                            'field' => 'properties.woe:superceded'
                        ]
                    ];
                }
                if (!$params['unknown']) {
                    $query['query']['bool']['must_not'][] = [
                        'term' => [
                            'woe:placetype' => 0
                        ]
                    ];
                }
                if ($params['placetype'] !== Parameters::PLACETYPE_DEFAULT) {
                    $query['query']['bool']['filter'][] = $this->addPlacetypeFilter($params['placetype']);
                }
                if ($params['iso'] !== NULL) {
                    $query['query']['bool']['filter'][] = $this->addIsoFilter($params['iso']);
                }
                $query['sort'][] = $this->sort;
                if ($params['facets']) {
                    $query['aggregations'] = $this->facets;
                }
                break;

            case Routes::SEARCH_FIELDS:
                $query = $this->fields_template;
                $query['size'] = $params['size'];
                $query['from'] = $params['from'];
                $query['query']['function_score']['functions'][0]['filter']['term']['names_preferred'] = $params['q'];
                $query['query']['function_score']['functions'][1]['filter']['term']['names_all'] = $params['q'];
                $query['query']['function_score']['functions'][2]['filter']['term']['woe:name'] = $params['q'];
                $query['query']['function_score']['query']['bool']['must'][] = [
                    'match' => [
                        '_all' => [
                            'query' => $params['q'],
                            'operator' => 'and'
                        ]
                    ]
                ];
                if (!$params['superceded']) {
                    $query['query']['function_score']['query']['bool']['must_not'][] = [
                        'exists' => [
                            'field' => 'properties.woe:superceded'
                        ]
                    ];
                }
                if (!$params['unknown']) {
                    $query['query']['function_score']['query']['bool']['must_not'][] = [
                        'term' => [
                            'woe:placetype' => 0
                        ]
                    ];
                }

                if ($params['placetype'] !== Parameters::PLACETYPE_DEFAULT) {
                    $query['query']['function_score']['query']['bool']['filter'][] = $this->addPlacetypeFilter($params['placetype']);
                }
                if ($params['iso'] !== NULL) {
                    $query['query']['function_score']['query']['bool']['filter'][] = $this->addIsoFilter($params['iso']);
                }
                $query['sort'][] = $this->sort;
                if ($params['facets']) {
                    $query['aggregations'] = $this->facets;
                }
                break;

            case Routes::SEARCH_NAMES:
                $query = $this->query_template;
                $query['size'] = $params['size'];
                $query['from'] = $params['from'];
                $query['query']['bool']['must'][] = [
                    'match' => [
                        'names_all' => [
                            'query' => $params['q'],
                            'operator' => 'and'
                        ]
                    ]
                ];
                $query['query']['bool']['should'][] = [
                    'match_all' => new \stdClass()
                ];
                if (!$params['superceded']) {
                    $query['query']['bool']['must_not'][] = [
                        'exists' => [
                            'field' => 'properties.woe:superceded'
                        ]
                    ];
                }
                if (!$params['unknown']) {
                    $query['query']['bool']['must_not'][] = [
                        'term' => [
                            'woe:placetype' => 0
                        ]
                    ];
                }
                if ($params['placetype'] !== Parameters::PLACETYPE_DEFAULT) {
                    $query['query']['bool']['filter'][] = $this->addPlacetypeFilter($params['placetype']);
                }
                if ($params['iso'] !== NULL) {
                    $query['query']['bool']['filter'][] = $this->addIsoFilter($params['iso']);
                }
                $query['sort'][] = $this->sort;
                if ($params['facets']) {
                    $query['aggregations'] = $this->facets;
                }
                break;

            case Routes::SEARCH_PREFERRED:
                $query = $this->query_template;
                $query['size'] = $params['size'];
                $query['from'] = $params['from'];
                $query['query']['bool']['must'][] = [
                    'match' => [
                        'names_preferred' => [
                            'query' => $params['q'],
                            'operator' => 'and'
                        ]
                    ]
                ];
                $query['query']['bool']['should'][] = [
                    'match_all' => new \stdClass()
                ];
                if (!$params['superceded']) {
                    $query['query']['bool']['must_not'][] = [
                        'exists' => [
                            'field' => 'properties.woe:superceded'
                        ]
                    ];
                }
                if (!$params['unknown']) {
                    $query['query']['bool']['must_not'][] = [
                        'term' => [
                            'woe:placetype' => 0
                        ]
                    ];
                }
                if ($params['placetype'] !== Parameters::PLACETYPE_DEFAULT) {
                    $query['query']['bool']['filter'][] = $this->addPlacetypeFilter($params['placetype']);
                }
                if ($params['iso'] !== NULL) {
                    $query['query']['bool']['filter'][] = $this->addIsoFilter($params['iso']);
                }
                $query['sort'][] = $this->sort;
                if ($params['facets']) {
                    $query['aggregations'] = $this->facets;
                }
                break;

            case Routes::SEARCH_ALTERNATE:
                $query = $this->query_template;
                $query['size'] = $params['size'];
                $query['from'] = $params['from'];
                $query['query']['bool']['must'][] = [
                    'match' => [
                        'names_alt' => [
                            'query' => $params['q'],
                            'operator' => 'and'
                        ]
                    ]
                ];
                $query['query']['bool']['should'][] = [
                    'match_all' => new \stdClass()
                ];
                if (!$params['superceded']) {
                    $query['query']['bool']['must_not'][] = [
                        'exists' => [
                            'field' => 'properties.woe:superceded'
                        ]
                    ];
                }
                if (!$params['unknown']) {
                    $query['query']['bool']['must_not'][] = [
                        'term' => [
                            'woe:placetype' => 0
                        ]
                    ];
                }
                if ($params['placetype'] !== Parameters::PLACETYPE_DEFAULT) {
                    $query['query']['bool']['filter'][] = $this->addPlacetypeFilter($params['placetype']);
                }
                if ($params['iso'] !== NULL) {
                    $query['query']['bool']['filter'][] = $this->addIsoFilter($params['iso']);
                }
                $query['sort'][] = $this->sort;
                if ($params['facets']) {
                    $query['aggregations'] = $this->facets;
                }
                break;

            case Routes::SEARCH_NAME:
                $query = $this->query_template;
                $query['size'] = $params['size'];
                $query['from'] = $params['from'];
                $query['query']['bool']['must'][] = [
                    'match' => [
                        'properties.woe:name' => [
                            'query' => $params['q'],
                            'operator' => 'and'
                        ]
                    ]
                ];
                $query['query']['bool']['should'][] = [
                    'match_all' => new \stdClass()
                ];
                if (!$params['superceded']) {
                    $query['query']['bool']['must_not'][] = [
                        'exists' => [
                            'field' => 'properties.woe:superceded'
                        ]
                    ];
                }
                if (!$params['unknown']) {
                    $query['query']['bool']['must_not'][] = [
                        'term' => [
                            'woe:placetype' => 0
                        ]
                    ];
                }
                if ($params['placetype'] !== Parameters::PLACETYPE_DEFAULT) {
                    $query['query']['bool']['filter'][] = $this->addPlacetypeFilter($params['placetype']);
                }
                if ($params['iso'] !== NULL) {
                    $query['query']['bool']['filter'][] = $this->addIsoFilter($params['iso']);
                }
                $query['sort'][] = $this->sort;
                if ($params['facets']) {
                    $query['aggregations'] = $this->facets;
                }
                break;

            case Routes::SEARCH_NULL_ISLAND:
                $query = $this->query_template;
                $query['size'] = $params['size'];
                $query['from'] = $params['from'];
                $query['query']['bool']['must'][] = [
                    'term' => [
                        'properties.woe:lat' => 0
                    ]
                ];
                $query['query']['bool']['must'][] = [
                    'term' => [
                        'properties.woe:lon' => 0
                    ]
                ];
                $query['query']['bool']['should'][] = [
                    'match_all' => new \stdClass()
                ];
                if (!$params['superceded']) {
                    $query['query']['bool']['must_not'][] = [
                        'exists' => [
                            'field' => 'properties.woe:superceded'
                        ]
                    ];
                }
                if (!$params['unknown']) {
                    $query['query']['bool']['must_not'][] = [
                        'term' => [
                            'woe:placetype' => 0
                        ]
                    ];
                }
                $query['query']['bool']['must_not'][] = [
                    'term' => [
                        'properties.woe:woeid' => 1
                    ]
                ];
                if ($params['placetype'] !== Parameters::PLACETYPE_DEFAULT) {
                    $query['query']['bool']['filter'][] = $this->addPlacetypeFilter($params['placetype']);
                }
                if ($params['iso'] !== NULL) {
                    $query['query']['bool']['filter'][] = $this->addIsoFilter($params['iso']);
                }
                $query['sort'][] = $this->sort;
                if ($params['facets']) {
                    $query['aggregations'] = $this->facets;
                }
                break;

            default:
                throw new \Exception(sprintf('%s: Unsupported route type "%s"', __METHOD__, $route));
                break;
        }

        return $query;
    }

    private function addPlacetypeFilter($placetype) {
        $filter = $this->filters[Parameters::PLACETYPE];
        $filter['term']['properties.woe:placetype'] = intval($placetype);
        return $filter;
    }

    private function addIsoFilter($iso) {
        $filter = $this->filters[Parameters::ISO];
        $filter['term']['properties.woe:iso'] = strtoupper($iso);
        return $filter;
    }
}

?>
