<?php

namespace Woeplanet\API\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Woeplanet\API\Index as Index;
use Woeplanet\API\QueryBuilder as QueryBuilder;
use Woeplanet\API\Routes as Routes;

class Search extends Controller {
    public function __construct(\Pimple\Container $container) {
        parent::__construct($container);
    }

    public function search(ServerRequestInterface $request, ResponseInterface $response, $args) {
        $status = $this->checkValidationErrors($request);
        if ($status['api:status']['code'] === 200) {
            $pb = $this->c['param-builder'];
            $params = $pb->buildParams(Routes::SEARCH, $request);
            $qb = $this->c->get('search-query');
            $query = $qb->buildQuery(Routes::SEARCH, $params);
            return $this->dispatchQuery($response, $params, $query);
        }
        return $this->response($response, $status['api:status']['code'], $status);
    }


    public function searchFields(ServerRequestInterface $request, ResponseInterface $response, $args) {
        $status = $this->checkValidationErrors($request);
        if ($status['api:status']['code'] === 200) {
            $pb = $this->c['param-builder'];
            $params = $pb->buildParams(Routes::SEARCH_FIELDS, $request);
            $qb = $this->c->get('search-query');
            $query = $qb->buildQuery(Routes::SEARCH_FIELDS, $params);
            return $this->dispatchQuery($response, $params, $query);
        }
        return $this->response($response, $status['api:status']['code'], $status);
    }

    public function searchNames(ServerRequestInterface $request, ResponseInterface $response, $args) {
        $status = $this->checkValidationErrors($request);
        if ($status['api:status']['code'] === 200) {
            $pb = $this->c['param-builder'];
            $params = $pb->buildParams(Routes::SEARCH_NAMES, $request);
            $qb = $this->c->get('search-query');
            $query = $qb->buildQuery(Routes::SEARCH_NAMES, $params);
            return $this->dispatchQuery($response, $params, $query);
        }
        return $this->response($response, $status['api:status']['code'], $status);
    }

    public function searchPreferred(ServerRequestInterface $request, ResponseInterface $response, $args) {
        $status = $this->checkValidationErrors($request);
        if ($status['api:status']['code'] === 200) {
            $pb = $this->c['param-builder'];
            $params = $pb->buildParams(Routes::SEARCH_PREFERRED, $request);
            $qb = $this->c->get('search-query');
            $query = $qb->buildQuery(Routes::SEARCH_PREFERRED, $params);
            return $this->dispatchQuery($response, $params, $query);
        }
        return $this->response($response, $status['api:status']['code'], $status);
    }

    public function searchAlternate(ServerRequestInterface $request, ResponseInterface $response, $args) {
        $status = $this->checkValidationErrors($request);
        if ($status['api:status']['code'] === 200) {
            $pb = $this->c['param-builder'];
            $params = $pb->buildParams(Routes::SEARCH_ALTERNATE, $request);
            $qb = $this->c->get('search-query');
            $query = $qb->buildQuery(Routes::SEARCH_ALTERNATE, $params);
            return $this->dispatchQuery($response, $params, $query);
        }
        return $this->response($response, $status['api:status']['code'], $status);
    }

    public function searchName(ServerRequestInterface $request, ResponseInterface $response, $args) {
        $status = $this->checkValidationErrors($request);
        if ($status['api:status']['code'] === 200) {
            $pb = $this->c['param-builder'];
            $params = $pb->buildParams(Routes::SEARCH_NAME, $request);
            $qb = $this->c->get('search-query');
            $query = $qb->buildQuery(Routes::SEARCH_NAME, $params);
            return $this->dispatchQuery($response, $params, $query);
        }
        return $this->response($response, $status['api:status']['code'], $status);
    }

    public function searchNullIsland(ServerRequestInterface $request, ResponseInterface $response, $args) {
        $status = $this->checkValidationErrors($request);
        if ($status['api:status']['code'] === 200) {
            $pb = $this->c['param-builder'];
            $params = $pb->buildParams(Routes::SEARCH_NULL_ISLAND, $request);
            $qb = $this->c->get('search-query');
            $query = $qb->buildQuery(Routes::SEARCH_NULL_ISLAND, $params);
            return $this->dispatchQuery($response, $params, $query);
        }
        return $this->response($response, $status['api:status']['code'], $status);
    }

    private function dispatchQuery(ResponseInterface $response, $params, $query) {
        $search = $this->c->get('search');

        try {
            $results = $search->search([
                'index' => Index::INDEX,
                'type' => Index::TYPE_PLACES,
                'body' =>  $query
            ]);

            $doc = [
                'api:status' => [
                    'code' => 200,
                    'reason' => 'OK'
                ],
                'api:from' => $query['from'],
                'api:size' => $query['size'],
                'api:total' => $results['hits']['total'],
                'api:hits' => []
            ];

            $formatter = $this->c['response-formatter'];

            foreach ($results['hits']['hits'] as $hit) {
                $doc['api:hits'][] = $formatter->format($hit['_source'], $params['properties']);
            }

            if ($params['facets']) {
                $doc['api:facets'] = [];

                $doc['api:facets']['countries'] = [];
                foreach ($results['aggregations']['country']['buckets'] as $facet) {
                    $doc['api:facets']['countries'][] = [
                        'key' => $facet['key'],
                        'count' => $facet['doc_count']
                    ];
                }

                $doc['api:facets']['placetypes'] = [];
                foreach ($results['aggregations']['placetype']['buckets'] as $facet) {
                    $doc['api:facets']['placetypes'][] = [
                        'key' => $facet['key'],
                        'count' => $facet['doc_count']
                    ];
                }
            }

            if ($params['query']) {
                $doc['api:query'] = $query;
            }

            return $this->response($response, $doc['api:status']['code'], $doc);
        }

        catch (\Exception $e) {
            $status = [
                'api:status' => [
                    'code' => 503,
                    'reason' => 'Woe is me. Something has gone horribly wrong',
                    'message' => $e->getMessage()
                ]
            ];
            return $this->response($response, $status['api:status']['code'], $status);
        }

        $status = [
            'api:status' => [
                'code' => 503,
                'reason' => 'Woe is me. Something has gone horribly wrong'
            ]
        ];
        return $this->response($response, $status['api:status']['code'], $status);
    }
}

?>
