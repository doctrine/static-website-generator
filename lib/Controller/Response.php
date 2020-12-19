<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\Controller;

class Response
{
    /** @var mixed[] */
    private array $parameters;

    private string $template;

    /**
     * @param mixed[] $parameters
     */
    public function __construct(array $parameters, string $template = '')
    {
        $this->parameters = $parameters;
        $this->template   = $template;
    }

    /**
     * @return mixed[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }
}
