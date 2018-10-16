<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\Tests\Requests;

use Doctrine\StaticWebsiteGenerator\Request\ArrayRequestCollection;
use Doctrine\StaticWebsiteGenerator\Request\RequestCollection;
use Doctrine\StaticWebsiteGenerator\Tests\Models\User;
use Doctrine\StaticWebsiteGenerator\Tests\Repositories\UserRepository;

class UserRequests
{
    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getUsers() : RequestCollection
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
