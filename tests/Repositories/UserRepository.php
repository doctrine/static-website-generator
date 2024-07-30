<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\Tests\Repositories;

use Doctrine\StaticWebsiteGenerator\Tests\Models\User;

use function array_filter;
use function array_pop;
use function assert;

/** @template T of User */
class UserRepository
{
    /** @param list<User> $users */
    public function __construct(private array $users)
    {
    }

    public function findOneByUsername(string $username): User
    {
        $users = array_filter($this->findAll(), static function (User $user) use ($username) {
            return $user->getUsername() === $username;
        });
        $user  = array_pop($users);

        assert($user instanceof User);

        return $user;
    }

    /** @return User[] */
    public function findAll(): array
    {
        return $this->users;
    }
}
