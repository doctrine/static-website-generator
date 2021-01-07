<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\SourceFile;

use Doctrine\StaticWebsiteGenerator\Request\RequestCollectionProvider;
use Doctrine\StaticWebsiteGenerator\Routing\Router;
use Symfony\Component\Routing\Route;

use function array_filter;
use function assert;
use function is_string;

class SourceFileRouteReader implements SourceFileReader
{
    /** @var Router */
    private $router;

    /** @var RequestCollectionProvider */
    private $requestCollectionProvider;

    /** @var SourceFileFactory */
    private $sourceFileFactory;

    public function __construct(
        Router $router,
        RequestCollectionProvider $requestCollectionProvider,
        SourceFileFactory $sourceFileFactory
    ) {
        $this->router                    = $router;
        $this->requestCollectionProvider = $requestCollectionProvider;
        $this->sourceFileFactory         = $sourceFileFactory;
    }

    public function getSourceFiles(string $buildDir = ''): SourceFiles
    {
        $sourceFiles = [];

        foreach ($this->getRoutesWithProvider() as $routeName => $route) {
            assert(is_string($routeName));

            [$className, $methodName] = $route->getDefault('_provider');

            $requestCollection = $this->requestCollectionProvider->getRequestCollection(
                $className,
                $methodName
            );

            foreach ($requestCollection->getRequests() as $request) {
                $sourcePath = $this->router->generate($routeName, $request);

                $sourceFiles[] = $this->sourceFileFactory->createSourceFile(
                    $buildDir,
                    $sourcePath
                );
            }
        }

        return new SourceFiles($sourceFiles);
    }

    /**
     * @return Route[]
     */
    private function getRoutesWithProvider(): array
    {
        return array_filter($this->router->getRouteCollection()->all(), static function (Route $route): bool {
            return $route->getDefault('_provider') !== null;
        });
    }
}
