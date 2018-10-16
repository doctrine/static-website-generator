<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\DataSource;

interface DataSource
{
    /**
     * @return mixed[][]
     */
    public function getSourceRows() : array;
}
