<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\Request;

interface RequestCollection
{
    /**
     * @return mixed[]
     */
    public function getRequests(): iterable;
}
