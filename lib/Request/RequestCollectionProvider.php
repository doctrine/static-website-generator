<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\Request;

use InvalidArgumentException;
use function call_user_func;
use function get_class;
use function sprintf;

class RequestCollectionProvider
{
    /** @var object[] */
    private $providers;

    /**
     * @param object[] $providers
     */
    public function __construct(array $providers)
    {
        foreach ($providers as $provider) {
            $this->providers[get_class($provider)] = $provider;
        }
    }

    public function getRequestCollection(string $className, string $methodName) : RequestCollection
    {
        if (! isset($this->providers[$className])) {
            throw new InvalidArgumentException(
                sprintf('Could not find request collection provider for class named %s', $className)
            );
        }

        /** @var callable $callable */
        $callable = [$this->providers[$className], $methodName];

        return call_user_func($callable);
    }
}
