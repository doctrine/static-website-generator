<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\Tests\Controllers;

use Doctrine\StaticWebsiteGenerator\Controller\Response;
use Doctrine\StaticWebsiteGenerator\Controller\ResponseFactory;
use Doctrine\StaticWebsiteGenerator\Tests\Repositories\UserRepository;

class UserController
{
    /** @var UserRepository */
    private $userRepository;

    /** @var ResponseFactory */
    private $responseFactory;

    public function __construct(UserRepository $userRepository, ResponseFactory $responseFactory)
    {
        $this->userRepository  = $userRepository;
        $this->responseFactory = $responseFactory;
    }

    public function user(string $username) : Response
    {
        $user = $this->userRepository->findOneByUsername($username);

        return $this->responseFactory->createResponse(['user' => $user], '/user.html.twig');
    }
}
