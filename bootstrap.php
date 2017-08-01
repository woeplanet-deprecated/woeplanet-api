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

$supercededValidator = v::optional(v::boolVal());
$boundaryValidator = v::optional(v::boolVal());
$propertiesValidator = v::optional(v::regex('/^(\*|.*:.*|.*:)(,(\*|.*:.*|.*:))*$/'));

$fromValidator = v::optional(v::intVal());
$sizeValidator = v::optional(v::intVal()->between(0, 100));

$unknownValidator = v::optional(v::boolVal());
$facetsValidator = v::optional(v::boolVal());
$queryValidator = v::optional(v::boolVal());

$placetypeValidator = v::optional(v::intVal());
$isoValidator = v::optional(v::countryCode());

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

$app->group('/v1', function() use ($validators) {
    // Code Health Warning
    // Inside a group closure $this is bound to the instance of \Slim\App

    $this->get('/place/{id}', \Woeplanet\API\Controllers\Place::class . ':getPlace')
        ->add($validators[Routes::PLACE]);

    $this->get('/placetypes', \Woeplanet\API\Controllers\PlaceType::class . ':getPlaceTypes');
    $this->get('/placetype/{id}', \Woeplanet\API\Controllers\PlaceType::class . ':getPlaceType');

    $this->get('/search', \Woeplanet\API\Controllers\Search::class . ':search')
        ->add($validators[Routes::SEARCH]);

    $this->get('/search/fields', \Woeplanet\API\Controllers\Search::class . ':searchFields')
        ->add($validators[Routes::SEARCH_FIELDS]);

    $this->get('/search/names', \Woeplanet\API\Controllers\Search::class . ':searchNames')
        ->add($validators[Routes::SEARCH_NAMES]);

    $this->get('/search/preferred', \Woeplanet\API\Controllers\Search::class . ':searchPreferred')
        ->add($validators[Routes::SEARCH_PREFERRED]);

    $this->get('/search/alternate', \Woeplanet\API\Controllers\Search::class . ':searchAlternate')
        ->add($validators[Routes::SEARCH_ALTERNATE]);

    $this->get('/search/name', \Woeplanet\API\Controllers\Search::class . ':searchName')
        ->add($validators[Routes::SEARCH_NAME]);

    $this->get('/search/null-island', \Woeplanet\API\Controllers\Search::class . ':searchNullIsland')
        ->add($validators[Routes::SEARCH_NULL_ISLAND]);

    $this->get('/meta', \Woeplanet\API\Controllers\Meta::class . ':getMeta');
});

?>
