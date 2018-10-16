<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\Tests\Repositories;

use Doctrine\SkeletonMapper\ObjectRepository\BasicObjectRepository;
use Doctrine\StaticWebsiteGenerator\Tests\Models\User;

class UserRepository extends BasicObjectRepository
{
    public function findOneByUsername(string $username) : User
    {
        /** @var User $user */
        $user = $this->findOneBy(['username' => $username]);

        return $user;
    }
}
