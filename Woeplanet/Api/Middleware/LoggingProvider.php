<?php

namespace Woeplanet\API\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class LoggingProvider {
    protected $c;

    public function __construct(\Pimple\Container $container) {
        $this->c = $container;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next) {
        $this->c->logger->debug(sprintf('Starting %s', $request->getUri()));

        $next_response = $next($request, $response);

        $this->c->logger->debug(sprintf('Finished %s (%d)', $request->getUri(), $next_response->getStatusCode()));

        return $next_response;
    }
}
?>
