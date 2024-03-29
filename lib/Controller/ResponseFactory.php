<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\Controller;

class ResponseFactory
{
    /** @param mixed[] $parameters */
    public function createResponse(array $parameters, string $template = ''): Response
    {
        return new Response($parameters, $template);
    }
}
