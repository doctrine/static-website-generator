<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\Tests;

use Doctrine\Common\EventManager;
use Doctrine\RST\Parser as RSTParser;
use Doctrine\SkeletonMapper\Hydrator\BasicObjectHydrator;
use Doctrine\SkeletonMapper\Mapping\ClassMetadataFactory;
use Doctrine\SkeletonMapper\Mapping\ClassMetadataInstantiator;
use Doctrine\SkeletonMapper\ObjectFactory;
use Doctrine\SkeletonMapper\ObjectIdentityMap;
use Doctrine\SkeletonMapper\ObjectManager;
use Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryFactory;
use Doctrine\SkeletonMapper\Persister\ObjectPersisterFactory;
use Doctrine\StaticWebsiteGenerator\Controller\ControllerExecutor;
use Doctrine\StaticWebsiteGenerator\Controller\ControllerProvider;
use Doctrine\StaticWebsiteGenerator\Controller\ResponseFactory;
use Doctrine\StaticWebsiteGenerator\DataSource\DataSourceObjectDataRepository;
use Doctrine\StaticWebsiteGenerator\Routing\Router;
use Doctrine\StaticWebsiteGenerator\Site;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileBuilder;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileFactory;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileFilesystemReader;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileParametersFactory;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileRenderer;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileRepository;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFilesBuilder;
use Doctrine\StaticWebsiteGenerator\Tests\Controllers\HomepageController;
use Doctrine\StaticWebsiteGenerator\Tests\DataSources\Users;
use Doctrine\StaticWebsiteGenerator\Tests\Models\User;
use Doctrine\StaticWebsiteGenerator\Tests\Repositories\UserRepository;
use Doctrine\StaticWebsiteGenerator\Twig\RoutingExtension;
use Doctrine\StaticWebsiteGenerator\Twig\StringTwigRenderer;
use Parsedown;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use function file_exists;
use function file_get_contents;

class FunctionalTest extends TestCase
{
    public function testBuild() : void
    {
        $rootDir       = __DIR__ . '/fixtures';
        $sourcePath    = $rootDir . '/source';
        $templatesPath = $rootDir . '/templates';
        $buildPath     = $rootDir . '/build';

        $responseFactory = new ResponseFactory();

        $objectManager = $this->createObjectManager();

        /** @var UserRepository $userRepository */
        $userRepository = $objectManager->getRepository(User::class);

        $controllerProvider = new ControllerProvider([
            HomepageController::class => new HomepageController($userRepository, $responseFactory),
        ]);
        $argumentResolver   = new ArgumentResolver();

        $controllerExecutor = new ControllerExecutor($controllerProvider, $argumentResolver);

        $site = new Site(
            'Doctrine Static Website Generator Title',
            'Doctrine Static Website Generator Subtitle',
            'http://localhost',
            [],
            '',
            'test',
            ''
        );

        $routes = [
            'homepage' => [
                'path' => '/index.html',
                'defaults' => [
                    '_controller' => [HomepageController::class, 'index'],
                    'title' => 'Homepage',
                ],
            ],
        ];

        $router = new Router($routes, $site);

        $routingExtension = new RoutingExtension($router);
        $twigRenderer     = new StringTwigRenderer($templatesPath, [$routingExtension]);

        $sourceFileRenderer = new SourceFileRenderer(
            $controllerExecutor,
            $twigRenderer,
            $site,
            $sourcePath,
            $templatesPath
        );

        $filesystem = new Filesystem();

        $parsedown = new Parsedown();

        $rstParser = new RSTParser();

        $sourceFileBuilder = new SourceFileBuilder(
            $sourceFileRenderer,
            $filesystem,
            $parsedown,
            $rstParser
        );

        $sourceFileParametersFactory = new SourceFileParametersFactory();

        $sourceFileFactory = new SourceFileFactory($router, $sourceFileParametersFactory, $rootDir);

        $sourceFileFilesystemReader = new SourceFileFilesystemReader($rootDir, $sourceFileFactory);

        $sourceFileRepository = new SourceFileRepository([$sourceFileFilesystemReader]);

        $sourceFilesBuilder = new SourceFilesBuilder($sourceFileBuilder);

        $sourceFiles = $sourceFileRepository->getSourceFiles($buildPath);

        $sourceFilesBuilder->buildSourceFiles($sourceFiles);

        $indexPath = $buildPath . '/index.html';

        self::assertTrue(file_exists($indexPath));

        $indexContents = file_get_contents($indexPath);

        self::assertContains('This is a test file.', $indexContents);
        self::assertContains('Homepage: /index.html', $indexContents);
        self::assertContains('Controller data: This data came from the controller', $indexContents);
        self::assertContains('Request path info: /index.html', $indexContents);
        self::assertContains('User: jwage', $indexContents);
    }

    private function createObjectManager() : ObjectManager
    {
        $objectRepositoryFactory = new ObjectRepositoryFactory();

        $objectPersisterFactory = new ObjectPersisterFactory();

        $classMetadataFactory = new ClassMetadataFactory(
            new ClassMetadataInstantiator()
        );

        $objectIdentityMap = new ObjectIdentityMap($objectRepositoryFactory);

        $eventManager = new EventManager();

        $objectManager = new ObjectManager(
            $objectRepositoryFactory,
            $objectPersisterFactory,
            $objectIdentityMap,
            $classMetadataFactory,
            $eventManager
        );

        $objectFactory  = new ObjectFactory();
        $objectHydrator = new BasicObjectHydrator($objectManager);

        $objectRepositoryFactory->addObjectRepository(User::class, new UserRepository(
            $objectManager,
            new DataSourceObjectDataRepository($objectManager, new Users(), User::class),
            $objectFactory,
            $objectHydrator,
            $eventManager,
            User::class
        ));

        return $objectManager;
    }
}
