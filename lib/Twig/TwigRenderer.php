<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\Twig;

interface TwigRenderer
{
    /** @param mixed[] $parameters */
    public function render(string $twig, array $parameters): string;
}
