<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\Request;

class ArrayRequestCollection implements RequestCollection
{
    /** @var mixed[] */
    private $requests;

    /**
     * @param mixed[] $requests
     */
    public function __construct(array $requests)
    {
        $this->requests = $requests;
    }

    /**
     * @return mixed[]
     */
    public function getRequests(): iterable
    {
        return $this->requests;
    }
}
