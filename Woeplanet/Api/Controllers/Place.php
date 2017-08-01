<?php

namespace Woeplanet\API\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Woeplanet\API\Index as Index;
use Woeplanet\API\Routes as Routes;
use Woeplanet\Utils\Path as Path;

class Place extends Controller {
    public function __construct(\Pimple\Container $container) {
        parent::__construct($container);
    }

    public function getPlace(ServerRequestInterface $request, ResponseInterface $response, $args) {
        $status = $this->checkValidationErrors($request);
        if ($status['api:status']['code'] === 200) {
            $woeid = $args['id'];
            $status['woeid'] = $woeid;
            $pb = $this->c['param-builder'];
            $params = $pb->buildParams(Routes::PLACE, $request);
            $status['params'] = $params;

            try {
                $doc = $this->get_woeid($woeid);
                if (!$doc['found'] || !isset($doc['_source']) || empty($doc['_source'])) {
                    throw new \Elasticsearch\Common\Exceptions\Missing404Exception(sprintf('WOEID %d does not seem to exist', $woeid));
                }

                if ($params['superceded'] && !empty($doc['_source']['properties']['woe:superceded'])) {
                    $woeid = $doc['_source']['properties']['woe:superceded']['woeid'];
                    $doc = $this->get_woeid($woeid);
                }

                $doc = $doc['_source'];

                if ($params['boundary'] && !empty($doc['properties']['woe:concordance'])) {
                    $concordance = $doc['properties']['woe:concordance'][0];
                    list($type, $wofid) = explode(':', $concordance);
                    $wof = $this->get_whosonfirst($wofid);
                    if (NULL !== $wof) {
                        if (isset($wof['geometry']['type']) && isset($wof['bbox']) && $wof['geometry']['type'] === 'Polygon') {
                            $doc['bbox'] = $wof['bbox'];
                            $doc['geometry'] = $wof['geometry'];
                        }
                    }
                }

                $doc['params'] = $params;
                $doc['properties']['api:status'] = $status['api:status'];

                $formatter = $this->c['response-formatter'];
                $doc = $formatter->format($doc, $params['properties']);

                return $this->response($response, $doc['properties']['api:status']['code'], $doc);
            }

            catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
                $data = [
                    'api:status' => [
                        'code' => 404,
                        'reason' => 'Place not found',
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

    private function get_woeid($woeid) {
        return $this->c['search']->get([
            'index' => Index::INDEX,
            'type' => Index::TYPE_PLACES,
            'id' => intval($woeid)
        ]);
    }

    private function get_whosonfirst($wofid) {
        $path = Path::build_enpathified_filespec(
            $this->c->get('settings')['data-stores']['whosonfirst'],
            $wofid
        );

        if (file_exists($path)) {
            $data = file_get_contents($path);
            if ($data === false) {
                error_log(sprintf("Failed to read %s\n", $path));
                return NULL;
            }

            $data = utf8_encode($data);
            $json = json_decode($data, true);
            if ($json === NULL) {
                error_log(sprintf("Failed to JSON-ify %s (%s)\n", $path, json_last_error_msg()));
                return NULL;
            }

            unset($data);
            return $json;
        }
        else {
            error_log(sprintf('%s does not exist!', $path));
        }

        return NULL;
    }

}

?>
