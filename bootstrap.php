<?php

require_once dirname(__FILE__) . '/vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Woeplanet\API\Parameters as Parameters;
use Woeplanet\API\Routes as Routes;

use Woeplanet\Utils\Path as Path;

use Respect\Validation\Validator as v;
use DavidePastore\Slim\Validation\Validation as Validation;

$config = new \Woeplanet\API\Config();

$settings = [
    'settings' => $config->settings()
];
$app = new \Slim\App($settings);

$c = $app->getContainer();

// Dependency Injection

$c->register(new \Woeplanet\API\Dependencies\LoggerProvider());
$c->register(new \Woeplanet\API\Dependencies\SearchProvider());
$c->register(new \Woeplanet\API\Dependencies\PlaceTypesProvider());
$c->register(new \Woeplanet\API\Dependencies\ParamBuilderProvider());
$c->register(new \Woeplanet\API\Dependencies\SearchQueryBuilderProvider());
$c->register(new \Woeplanet\API\Dependencies\ResponseFormatterProvider());

$c->register(new \Woeplanet\API\ErrorHandlers\NotFoundHandler());
$c->register(new \Woeplanet\API\ErrorHandlers\ErrorHandler());

// App Middleware

$app->add(new \Woeplanet\API\Middleware\LoggingProvider($c));
$app->add(new \Woeplanet\API\Middleware\CORSProvider());

// Per route validation Middleware

/**
 * @apiDefine SupercededParam
 * @apiParam {Boolean} [superceded=true] Toggle whether to "follow" superceded WOEIDs in the response body
 */
$supercededValidator = v::optional(v::boolVal());
/**
 * @apiDefine BoundaryParam
 * @apiParam {Boolean} [boundary=false] Toggle whether to replace a place's Centroid with a boundary Polygon, if one exists
 */
$boundaryValidator = v::optional(v::boolVal());
/**
 * @apiDefine PropertiesParam
 * @apiParam {String} [properties=default] Specifies the GeoJSON properties to be included in a Place response.
 * The <code>properties</code> parameter is a comma separated list of field names, which can be
 * fully qualified (<code>woe:name</code>) or a prefix (<code>woe:</code>). A value of <code>*</code> includes <em>all</em>
 * fields.
 */
$propertiesValidator = v::optional(v::regex('/^(\*|.*:.*|.*:)(,(\*|.*:.*|.*:))*$/'));

/**
 * @apiDefine FromParam
 * @apiParam {Number} [from=0] Start search results at hit number <code>from</code>
 */
$fromValidator = v::optional(v::intVal());
/**
 * @apiDefine SizeParam
 * @apiParam {Number{1-100}} [size=50] Number of search results to be returned
 */
$sizeValidator = v::optional(v::intVal()->between(0, 100));

/**
 * @apiDefine UnknownParam
 * @apiParam {Boolean} [unknown=false] Toggle the inclusion of unknown Placetypes in the response body
 */
$unknownValidator = v::optional(v::boolVal());
/**
 * @apiDefine FacetsParam
 * @apiParam {Boolean} [facets=false] Toggle the inclusion of summary facets for Country Code and Placetype in the response body
 */
$facetsValidator = v::optional(v::boolVal());
/**
 * @apiDefine QueryParam
 * @apiParam {Boolean} [query=false] Toggle the inclusion of the raw ElasticSearch query string in the response body
 */
$queryValidator = v::optional(v::boolVal());

/**
 * @apiDefine PlaceTypeFilter
 * @apiParam {Number} [placetype] Filter search results by placetype
 */
$placetypeValidator = v::optional(v::intVal());
/**
 * @apiDefine CountryCodeFilter
 * @apiParam {String} [iso] Filter search results by ISO 3166-1 Alpha 2 country code
 */
$isoValidator = v::optional(v::countryCode());

/**
 * @apiDefine QueryStringParam
 * @apiParam {String} q URL encoded query string
 */
$qValidator = v::stringType();

$validators = [
    Routes::PLACE => new Validation([
        Parameters::BOUNDARY => $boundaryValidator,
        Parameters::SUPERCEDED => $supercededValidator,
        Parameters::PROPERTIES => $propertiesValidator
    ]),
    Routes::SEARCH => new Validation([
        Parameters::FROM => $fromValidator,
        Parameters::SIZE => $sizeValidator,
        Parameters::UNKNOWN => $unknownValidator,
        Parameters::BOUNDARY => $boundaryValidator,
        Parameters::SUPERCEDED => $supercededValidator,
        Parameters::PROPERTIES => $propertiesValidator,
        Parameters::FACETS => $facetsValidator,
        Parameters::QUERY => $queryValidator,
        Parameters::PLACETYPE => $placetypeValidator,
        Parameters::ISO => $isoValidator,
    ]),
    Routes::SEARCH_FIELDS => new Validation([
        Parameters::Q => $qValidator,
        Parameters::FROM => $fromValidator,
        Parameters::SIZE => $sizeValidator,
        Parameters::UNKNOWN => $unknownValidator,
        Parameters::BOUNDARY => $boundaryValidator,
        Parameters::SUPERCEDED => $supercededValidator,
        Parameters::PROPERTIES => $propertiesValidator,
        Parameters::FACETS => $facetsValidator,
        Parameters::QUERY => $queryValidator,
        Parameters::PLACETYPE => $placetypeValidator,
        Parameters::ISO => $isoValidator,
    ]),
    Routes::SEARCH_NAMES => new Validation([
        Parameters::Q => $qValidator,
        Parameters::FROM => $fromValidator,
        Parameters::SIZE => $sizeValidator,
        Parameters::UNKNOWN => $unknownValidator,
        Parameters::BOUNDARY => $boundaryValidator,
        Parameters::SUPERCEDED => $supercededValidator,
        Parameters::PROPERTIES => $propertiesValidator,
        Parameters::FACETS => $facetsValidator,
        Parameters::QUERY => $queryValidator,
        Parameters::PLACETYPE => $placetypeValidator,
        Parameters::ISO => $isoValidator,
    ]),
    Routes::SEARCH_PREFERRED => new Validation([
        Parameters::Q => $qValidator,
        Parameters::FROM => $fromValidator,
        Parameters::SIZE => $sizeValidator,
        Parameters::UNKNOWN => $unknownValidator,
        Parameters::BOUNDARY => $boundaryValidator,
        Parameters::SUPERCEDED => $supercededValidator,
        Parameters::PROPERTIES => $propertiesValidator,
        Parameters::FACETS => $facetsValidator,
        Parameters::QUERY => $queryValidator,
        Parameters::PLACETYPE => $placetypeValidator,
        Parameters::ISO => $isoValidator,
    ]),
    Routes::SEARCH_ALTERNATE => new Validation([
        Parameters::Q => $qValidator,
        Parameters::FROM => $fromValidator,
        Parameters::SIZE => $sizeValidator,
        Parameters::UNKNOWN => $unknownValidator,
        Parameters::BOUNDARY => $boundaryValidator,
        Parameters::SUPERCEDED => $supercededValidator,
        Parameters::PROPERTIES => $propertiesValidator,
        Parameters::FACETS => $facetsValidator,
        Parameters::QUERY => $queryValidator,
        Parameters::PLACETYPE => $placetypeValidator,
        Parameters::ISO => $isoValidator,
    ]),
    Routes::SEARCH_NAME => new Validation([
        Parameters::Q => $qValidator,
        Parameters::FROM => $fromValidator,
        Parameters::SIZE => $sizeValidator,
        Parameters::UNKNOWN => $unknownValidator,
        Parameters::BOUNDARY => $boundaryValidator,
        Parameters::SUPERCEDED => $supercededValidator,
        Parameters::PROPERTIES => $propertiesValidator,
        Parameters::FACETS => $facetsValidator,
        Parameters::QUERY => $queryValidator,
        Parameters::PLACETYPE => $placetypeValidator,
        Parameters::ISO => $isoValidator,
    ]),
    Routes::SEARCH_NULL_ISLAND => new Validation([
        Parameters::FROM => $fromValidator,
        Parameters::SIZE => $sizeValidator,
        Parameters::UNKNOWN => $unknownValidator,
        Parameters::BOUNDARY => $boundaryValidator,
        Parameters::SUPERCEDED => $supercededValidator,
        Parameters::PROPERTIES => $propertiesValidator,
        Parameters::FACETS => $facetsValidator,
        Parameters::QUERY => $queryValidator,
        Parameters::PLACETYPE => $placetypeValidator,
        Parameters::ISO => $isoValidator,
    ])
];

// Routing

$app->group('/v1', function () use ($validators) {
    // Code Health Warning
    // Inside a group closure $this is bound to the instance of \Slim\App

    /**
     * @api {get} /v1/place/:id Get a place
     * @apiName getPlace
     * @apiGroup Places
     * @apiParam {Number} id The place's unique WOEID
     * @apiUse BoundaryParam
     * @apiUse SupercededParam
     * @apiUse PropertiesParam
     */
    $this->get('/place/{id}', \Woeplanet\API\Controllers\Place::class . ':getPlace')
    ->add($validators[Routes::PLACE]);

    /**
     * @api {get} /v1/placetypes Get all placetypes
     * @apiName getPlaceTypes
     * @apiGroup Placetypes
     */
    $this->get('/placetypes', \Woeplanet\API\Controllers\PlaceType::class . ':getPlaceTypes');
    /**
     * @api {get} /v1/placetype/:id Get a placetype
     * @apiName getPlaceType
     * @apiGroup Placetypes
     * @apiParam {Number} id The placetype's unique ID
     */
    $this->get('/placetype/{id}', \Woeplanet\API\Controllers\PlaceType::class . ':getPlaceType');

    /**
     * @api {get} /v1/search Search and page through all places
     * @apiName search
     * @apiGroup Search
     * @apiUse FromParam
     * @apiUse SizeParam
     * @apiUse UnknownParam
     * @apiUse BoundaryParam
     * @apiUse SupercededParam
     * @apiUse PropertiesParam
     * @apiUse FacetsParam
     * @apiUse QueryParam
     * @apiUse PlaceTypeFilter
     * @apiUse CountryCodeFilter
     */
    $this->get('/search', \Woeplanet\API\Controllers\Search::class . ':search')
    ->add($validators[Routes::SEARCH]);

    /**
     * @api {get} /v1/search/fields Search all place fields for a query string
     * @apiName searchFields
     * @apiGroup Search
     * @apiUse QueryStringParam
     * @apiUse FromParam
     * @apiUse SizeParam
     * @apiUse UnknownParam
     * @apiUse BoundaryParam
     * @apiUse SupercededParam
     * @apiUse PropertiesParam
     * @apiUse FacetsParam
     * @apiUse QueryParam
     * @apiUse PlaceTypeFilter
     * @apiUse CountryCodeFilter
     */
    $this->get('/search/fields', \Woeplanet\API\Controllers\Search::class . ':searchFields')
    ->add($validators[Routes::SEARCH_FIELDS]);

    /**
     * @api {get} /v1/search/names Search all place name fields for a query string
     * @apiName searchNames
     * @apiGroup Search
     * @apiUse QueryStringParam
     * @apiUse FromParam
     * @apiUse SizeParam
     * @apiUse UnknownParam
     * @apiUse BoundaryParam
     * @apiUse SupercededParam
     * @apiUse PropertiesParam
     * @apiUse FacetsParam
     * @apiUse QueryParam
     * @apiUse PlaceTypeFilter
     * @apiUse CountryCodeFilter
     */
    $this->get('/search/names', \Woeplanet\API\Controllers\Search::class . ':searchNames')
    ->add($validators[Routes::SEARCH_NAMES]);

    /**
     * @api {get} /v1/search/preferred Search all preferred place name fields for a query string
     * @apiName searchPreferred
     * @apiGroup Search
     * @apiUse QueryStringParam
     * @apiUse FromParam
     * @apiUse SizeParam
     * @apiUse UnknownParam
     * @apiUse BoundaryParam
     * @apiUse SupercededParam
     * @apiUse PropertiesParam
     * @apiUse FacetsParam
     * @apiUse QueryParam
     * @apiUse PlaceTypeFilter
     * @apiUse CountryCodeFilter
     */
    $this->get('/search/preferred', \Woeplanet\API\Controllers\Search::class . ':searchPreferred')
    ->add($validators[Routes::SEARCH_PREFERRED]);

    /**
     * @api {get} /v1/search/alternate Search all alternate place name fields for a query string
     * @apiName searchAlternate
     * @apiGroup Search
     * @apiUse QueryStringParam
     * @apiUse FromParam
     * @apiUse SizeParam
     * @apiUse UnknownParam
     * @apiUse BoundaryParam
     * @apiUse SupercededParam
     * @apiUse PropertiesParam
     * @apiUse FacetsParam
     * @apiUse QueryParam
     * @apiUse PlaceTypeFilter
     * @apiUse CountryCodeFilter
     */
    $this->get('/search/alternate', \Woeplanet\API\Controllers\Search::class . ':searchAlternate')
    ->add($validators[Routes::SEARCH_ALTERNATE]);

    /**
     * @api {get} /v1/search/name Search the default place name field for a query string
     * @apiName searchName
     * @apiGroup Search
     * @apiUse QueryStringParam
     * @apiUse FromParam
     * @apiUse SizeParam
     * @apiUse UnknownParam
     * @apiUse BoundaryParam
     * @apiUse SupercededParam
     * @apiUse PropertiesParam
     * @apiUse FacetsParam
     * @apiUse QueryParam
     * @apiUse PlaceTypeFilter
     * @apiUse CountryCodeFilter
     */
    $this->get('/search/name', \Woeplanet\API\Controllers\Search::class . ':searchName')
    ->add($validators[Routes::SEARCH_NAME]);

    /**
     * @api {get} /v1/search/null-island Search all places that visit Null Island (have no coordinates)
     * @apiName searchNullIsland
     * @apiGroup Search
     * @apiUse FromParam
     * @apiUse SizeParam
     * @apiUse UnknownParam
     * @apiUse BoundaryParam
     * @apiUse SupercededParam
     * @apiUse PropertiesParam
     * @apiUse FacetsParam
     * @apiUse QueryParam
     * @apiUse PlaceTypeFilter
     * @apiUse CountryCodeFilter
     */
    $this->get('/search/null-island', \Woeplanet\API\Controllers\Search::class . ':searchNullIsland')
    ->add($validators[Routes::SEARCH_NULL_ISLAND]);

    /**
     * @api {get} /v1/meta Returns supporting metadata about Woeplanet
     * @apiName meta
     * @apiGroup Meta
     */
    $this->get('/meta', \Woeplanet\API\Controllers\Meta::class . ':getMeta');
});
