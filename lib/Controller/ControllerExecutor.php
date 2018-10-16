<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\Controller;

use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFile;
use RuntimeException;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use function call_user_func_array;
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

    public function execute(SourceFile $sourceFile) : Response
    {
        [$className, $methodName] = $sourceFile->getController();

        $controller = $this->controllerProvider->getController($className);

        /** @var callable $callable */
        $callable = [$controller, $methodName];

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
