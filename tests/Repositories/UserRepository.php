<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\Tests\Repositories;

use Doctrine\SkeletonMapper\ObjectRepository\BasicObjectRepository;
use Doctrine\StaticWebsiteGenerator\Tests\Models\User;

use function assert;

class UserRepository extends BasicObjectRepository
{
    public function findOneByUsername(string $username): User
    {
        $user = $this->findOneBy(['username' => $username]);
        assert($user instanceof User);

        return $user;
    }
}
