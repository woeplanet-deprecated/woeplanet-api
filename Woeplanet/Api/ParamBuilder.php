<?php

namespace Woeplanet\API;

use Psr\Http\Message\ServerRequestInterface;

use Woeplanet\API\Parameters as Parameters;
use Woeplanet\API\Routes as Routes;

class ParamBuilder {
    public function buildParams($route, ServerRequestInterface $request) {
        $params = [];

        switch ($route) {
            case Routes::PLACE:
                $params = [
                    Parameters::BOUNDARY => $this->toBoolean($request->getQueryParam(Parameters::BOUNDARY, false)),
                    Parameters::SUPERCEDED => $this->toBoolean($request->getQueryParam(Parameters::SUPERCEDED, true)),
                    Parameters::UNKNOWN => $this->toBoolean($request->getQueryParam(Parameters::UNKNOWN, false)),
                    Parameters::PROPERTIES => $request->getQueryParam(Parameters::PROPERTIES, Parameters::PROPERTIES_DEFAULT)
                ];
                break;

            case Routes::SEARCH:
            case Routes::SEARCH_NULL_ISLAND:
                $params = [
                    Parameters::FROM => intval($request->getQueryParam(Parameters::FROM, Parameters::FROM_DEFAULT)),
                    Parameters::SIZE => intval($request->getQueryParam(Parameters::SIZE, Parameters::SIZE_DEFAULT)),
                    Parameters::SUPERCEDED => $this->toBoolean($request->getQueryParam(Parameters::SUPERCEDED, true)),
                    Parameters::UNKNOWN => $this->toBoolean($request->getQueryParam(Parameters::UNKNOWN, false)),
                    Parameters::FACETS => $this->toBoolean($request->getQueryParam(Parameters::FACETS, false)),
                    Parameters::QUERY => $this->toBoolean($request->getQueryParam(Parameters::QUERY, false)),
                    Parameters::PLACETYPE => intval($request->getQueryParam(Parameters::PLACETYPE, Parameters::PLACETYPE_DEFAULT)),
                    Parameters::ISO => $request->getQueryParam(Parameters::ISO, NULL),
                    Parameters::PROPERTIES => $request->getQueryParam(Parameters::PROPERTIES, Parameters::PROPERTIES_DEFAULT)
                ];
                break;

            case Routes::SEARCH_FIELDS:
            case Routes::SEARCH_NAMES:
            case Routes::SEARCH_PREFERRED:
            case Routes::SEARCH_ALTERNATE:
            case Routes::SEARCH_NAME:
                $params = [
                    Parameters::Q => $request->getQueryParam(Parameters::Q, NULL),
                    Parameters::FROM => intval($request->getQueryParam(Parameters::FROM, Parameters::FROM_DEFAULT)),
                    Parameters::SIZE => intval($request->getQueryParam(Parameters::SIZE, Parameters::SIZE_DEFAULT)),
                    Parameters::SUPERCEDED => $this->toBoolean($request->getQueryParam(Parameters::SUPERCEDED, true)),
                    Parameters::UNKNOWN => $this->toBoolean($request->getQueryParam(Parameters::UNKNOWN, false)),
                    Parameters::FACETS => $this->toBoolean($request->getQueryParam(Parameters::FACETS, false)),
                    Parameters::QUERY => $this->toBoolean($request->getQueryParam(Parameters::QUERY, false)),
                    Parameters::PLACETYPE => intval($request->getQueryParam(Parameters::PLACETYPE, Parameters::PLACETYPE_DEFAULT)),
                    Parameters::ISO => $request->getQueryParam(Parameters::ISO, NULL),
                    Parameters::PROPERTIES => $request->getQueryParam(Parameters::PROPERTIES, Parameters::PROPERTIES_DEFAULT)
                ];
                break;

            case Routes::PLACETYPES:
            case Routes::PLACETYPE:
            case Routes::META:
                break;

            default:
                throw new \Exception(sprintf('%s::%s: unknown route %s', __CLASS__, __METHOD__, $route));
                break;
        }

        return $params;
    }

    private function toBoolean($value) {
        if (!is_string($value)) {
            return (bool)$value;
        }
        switch (strtolower($value)) {
            case '1':
            case 'true':
            case 'on':
            case 'yes':
            case 'y':
                return true;
            default:
                return false;
        }
    }

}
