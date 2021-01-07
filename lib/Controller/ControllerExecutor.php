<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\Controller;

use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFile;
use RuntimeException;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;

use function assert;
use function call_user_func_array;
use function is_callable;
use function sprintf;

class ControllerExecutor
{
    /** @var ControllerProvider */
    private $controllerProvider;

    /** @var ArgumentResolver */
    private $argumentResolver;

    public function __construct(
        ControllerProvider $controllerProvider,
        ArgumentResolver $argumentResolver
    ) {
        $this->controllerProvider = $controllerProvider;
        $this->argumentResolver   = $argumentResolver;
    }

    public function execute(SourceFile $sourceFile): Response
    {
        $controller = $sourceFile->getController();

        if ($controller === null) {
            throw new RuntimeException('SourceFile::getController() should not return null here.');
        }

        [$className, $methodName] = $controller;

        $controller = $this->controllerProvider->getController($className);

        $callable = [$controller, $methodName];
        assert(is_callable($callable));

        $arguments = $this->argumentResolver->getArguments(
            $sourceFile->getRequest(),
            $callable
        );

        $controllerResult = call_user_func_array($callable, $arguments);

        if (! $controllerResult instanceof Response) {
            throw new RuntimeException(sprintf(
                'Controller %s did not return a %s instance.',
                $className,
                Response::class
            ));
        }

        return $controllerResult;
    }
}
