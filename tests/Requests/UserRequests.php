<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\Tests\Requests;

use Doctrine\StaticWebsiteGenerator\Request\ArrayRequestCollection;
use Doctrine\StaticWebsiteGenerator\Request\RequestCollection;
use Doctrine\StaticWebsiteGenerator\Tests\Models\User;
use Doctrine\StaticWebsiteGenerator\Tests\Repositories\UserRepository;

class UserRequests
{
    /** @param UserRepository<User> $userRepository */
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function getUsers(): RequestCollection
    {
        /** @var User[] $users */
        $users = $this->userRepository->findAll();

        $requests = [];

        foreach ($users as $user) {
            $requests[] = [
                'username' => $user->getUsername(),
            ];
        }

        return new ArrayRequestCollection($requests);
    }
}
