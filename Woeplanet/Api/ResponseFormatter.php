<?php

namespace Woeplanet\API;

class ResponseFormatter {
    public function format($geojson, $properties) {
        $response = [];

        if ($properties === Parameters::PROPERTIES_ALL) {
            $response = $geojson;
        }

        elseif ($properties === Parameters::PROPERTIES_DEFAULT) {
            $response = $this->minimalResponse($geojson);
        }

        else {
            $response = $this->minimalResponse($geojson);

            $fields = explode(',', $properties);
            foreach ($fields as $field) {
                $is_prefix = (substr(trim($field), -1) === ':');
                if ($is_prefix) {
                    foreach ($geojson['properties'] as $key => $value) {
                        if (strstr($key, $field) !== false) {
                            $response['properties'][$key] = $geojson['properties'][$key];
                        }
                    }

                }

                else {
                    if (isset($geojson['properties'][$field])) {
                        $response['properties'][$field] = $geojson['properties'][$field];
                    }
                }
            }

        }

        return $response;
    }

    private function minimalResponse($geojson) {
        $doc= [
            'type' => $geojson['type'],
            'properties' => [
                'woe:repo' => $geojson['properties']['woe:repo'],
                'woe:woeid' => $geojson['properties']['woe:woeid'],
                'woe:iso' => $geojson['properties']['woe:iso'],
                'woe:name' => $geojson['properties']['woe:name'],
                'woe:lang' => $geojson['properties']['woe:lang'],
                'woe:placetype' => $geojson['properties']['woe:placetype'],
                'woe:parent' => $geojson['properties']['woe:parent']
            ],
            'bbox' => $geojson['bbox'],
            'geometry' => $geojson['geometry'],
            'id' => $geojson['id']
        ];

        if (isset($geojson['properties']['api:status']) && !empty($geojson['properties']['api:status'])) {
            $doc['properties']['api:status'] = $geojson['properties']['api:status'];
        }
        return $doc;
    }
}
?>
