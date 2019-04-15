<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\Controller;

use InvalidArgumentException;
use function get_class;
use function sprintf;

class ControllerProvider
{
    /** @var object[] */
    private $controllers;

    /**
     * @param object[] $controllers
     */
    public function __construct(array $controllers)
    {
        foreach ($controllers as $controller) {
            $this->controllers[get_class($controller)] = $controller;
        }
    }

    public function getController(string $className) : object
    {
        if (! isset($this->controllers[$className])) {
            throw new InvalidArgumentException(sprintf('Could not find controller class %s', $className));
        }

        return $this->controllers[$className];
    }
}
