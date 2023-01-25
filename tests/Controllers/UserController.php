<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\Tests\Controllers;

use Doctrine\StaticWebsiteGenerator\Controller\Response;
use Doctrine\StaticWebsiteGenerator\Controller\ResponseFactory;
use Doctrine\StaticWebsiteGenerator\Tests\Models\User;
use Doctrine\StaticWebsiteGenerator\Tests\Repositories\UserRepository;

class UserController
{
    /** @param UserRepository<User> $userRepository */
    public function __construct(private UserRepository $userRepository, private ResponseFactory $responseFactory)
    {
    }

    public function user(string $username): Response
    {
        $user = $this->userRepository->findOneByUsername($username);

        return $this->responseFactory->createResponse(['user' => $user], '/user.html.twig');
    }
}
