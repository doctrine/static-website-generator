<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\Routing;

use Doctrine\StaticWebsiteGenerator\Site;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

use function explode;
use function parse_url;

class Router
{
    private RequestContext $context;

    private RouteCollection $routes;

    private UrlMatcher $urlMatcher;

    private UrlGenerator $urlGenerator;

    /**
     * @param mixed[] $routes
     */
    public function __construct(array $routes, Site $site)
    {
        $this->context = $this->createRequestContext($site);

        $this->routes = new RouteCollection();

        foreach ($routes as $routeName => $routeData) {
            $this->routes->add($routeName, $this->createRoute($routeData));
        }

        $this->urlMatcher   = new UrlMatcher($this->routes, $this->context);
        $this->urlGenerator = new UrlGenerator($this->routes, $this->context);
    }

    public function getRouteCollection(): RouteCollection
    {
        return $this->routes;
    }

    /**
     * @return mixed[]
     */
    public function match(string $pathinfo): ?array
    {
        try {
            return $this->urlMatcher->match($pathinfo);
        } catch (ResourceNotFoundException $e) {
            return null;
        }
    }

    /**
     * @param mixed[] $parameters
     */
    public function generate(
        string $name,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        return $this->urlGenerator->generate($name, $parameters, $referenceType);
    }

    public function setContext(RequestContext $context): void
    {
        $this->context = $context;
    }

    public function getContext(): ?RequestContext
    {
        return $this->context;
    }

    /**
     * @param mixed[] $routeData
     */
    private function createRoute(array $routeData): Route
    {
        if (isset($routeData['controller']) && ! isset($routeData['defaults']['_controller'])) {
            $routeData['defaults']['_controller'] = explode('::', $routeData['controller']);
        }

        if (isset($routeData['provider']) && ! isset($routeData['defaults']['_provider'])) {
            $routeData['defaults']['_provider'] = explode('::', $routeData['provider']);
        }

        return new Route(
            $routeData['path'],
            $routeData['defaults'] ?? [],
            $routeData['requirements'] ?? []
        );
    }

    private function createRequestContext(Site $site): RequestContext
    {
        $url = parse_url($site->getUrl());

        return new RequestContext(
            '',
            'GET',
            $url['host'],
            $url['scheme'],
            $url['port'] ?? 80,
            443,
            '/',
            ''
        );
    }
}
