<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\Tests\Controllers;

use Doctrine\StaticWebsiteGenerator\Controller\Response;
use Doctrine\StaticWebsiteGenerator\Controller\ResponseFactory;
use Doctrine\StaticWebsiteGenerator\Tests\Models\User;
use Doctrine\StaticWebsiteGenerator\Tests\Repositories\UserRepository;
use Symfony\Component\HttpFoundation\Request;

class HomepageController
{
    /** @param UserRepository<User> $userRepository */
    public function __construct(private UserRepository $userRepository, private ResponseFactory $responseFactory)
    {
    }

    public function index(Request $request): Response
    {
        $user = $this->userRepository->findOneByUsername('jwage');

        return $this->responseFactory->createResponse([
            'controllerData' => 'This data came from the controller',
            'requestPathInfo' => $request->getPathInfo(),
            'user' => $user,
        ]);
    }
}
