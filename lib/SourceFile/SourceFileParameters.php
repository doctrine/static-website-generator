<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\SourceFile;

use function array_merge;

class SourceFileParameters
{
    /** @var mixed[] */
    private $parameters = [];

    /**
     * @param mixed[] $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return mixed[]
     */
    public function getAll() : array
    {
        return $this->parameters;
    }

    /**
     * @return mixed
     */
    public function getParameter(string $key)
    {
        return $this->parameters[$key] ?? null;
    }

    /**
     * @param mixed $value
     */
    public function setParameter(string $key, $value) : void
    {
        $this->parameters[$key] = $value;
    }

    /**
     * @param mixed[] $parameters
     */
    public function merge(array $parameters) : void
    {
        $this->parameters = array_merge($this->parameters, $parameters);
    }
}
