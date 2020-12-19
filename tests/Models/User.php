<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\Tests\Models;

use Doctrine\SkeletonMapper\Hydrator\HydratableInterface;
use Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface;
use Doctrine\SkeletonMapper\Mapping\LoadMetadataInterface;
use Doctrine\SkeletonMapper\ObjectManagerInterface;

class User implements HydratableInterface, LoadMetadataInterface
{
    private string $username;

    public static function loadMetadata(ClassMetadataInterface $metadata): void
    {
        $metadata->setIdentifier(['username']);
    }

    /**
     * @param mixed[] $project
     */
    public function hydrate(array $project, ObjectManagerInterface $objectManager): void
    {
        $this->username = (string) $project['username'] ?? '';
    }

    public function getUsername(): string
    {
        return $this->username;
    }
}
