<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\Tests;

use Doctrine\RST\Parser as RSTParser;
use Doctrine\StaticWebsiteGenerator\Controller\ControllerExecutor;
use Doctrine\StaticWebsiteGenerator\Controller\ControllerProvider;
use Doctrine\StaticWebsiteGenerator\Controller\ResponseFactory;
use Doctrine\StaticWebsiteGenerator\Request\RequestCollectionProvider;
use Doctrine\StaticWebsiteGenerator\Routing\Router;
use Doctrine\StaticWebsiteGenerator\Site;
use Doctrine\StaticWebsiteGenerator\SourceFile\Converters\MarkdownConverter;
use Doctrine\StaticWebsiteGenerator\SourceFile\Converters\ReStructuredTextConverter;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileBuilder;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileFactory;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileFilesystemReader;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileParametersFactory;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileRenderer;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileRepository;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileRouteReader;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFilesBuilder;
use Doctrine\StaticWebsiteGenerator\Tests\Controllers\HomepageController;
use Doctrine\StaticWebsiteGenerator\Tests\Controllers\UserController;
use Doctrine\StaticWebsiteGenerator\Tests\Models\User;
use Doctrine\StaticWebsiteGenerator\Tests\Repositories\UserRepository;
use Doctrine\StaticWebsiteGenerator\Tests\Requests\UserRequests;
use Doctrine\StaticWebsiteGenerator\Twig\RoutingExtension;
use Doctrine\StaticWebsiteGenerator\Twig\StringTwigRenderer;
use Parsedown;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;

use function assert;
use function file_exists;
use function file_get_contents;
use function trim;

class FunctionalTest extends TestCase
{
    public function testBuild(): void
    {
        $rootDir      = __DIR__ . '/fixtures';
        $sourceDir    = $rootDir . '/source';
        $templatesDir = $rootDir . '/templates';
        $buildDir     = $rootDir . '/build';

        $responseFactory = new ResponseFactory();

        $user1 = new User('jwage');
        $user2 = new User('ocramius');

        $userRepository = new UserRepository([$user1, $user2]);

        $controllerProvider = new ControllerProvider([
            HomepageController::class => new HomepageController($userRepository, $responseFactory),
            UserController::class => new UserController($userRepository, $responseFactory),
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
            '',
        );

        $routes = [
            'homepage' => [
                'path' => '/index.html',
                'defaults' => [
                    '_controller' => [HomepageController::class, 'index'],
                ],
            ],
            'user' => [
                'path' => '/user/{username}.html',
                'defaults' => [
                    '_controller' => [UserController::class, 'user'],
                    '_provider' => [UserRequests::class, 'getUsers'],
                ],
            ],
        ];

        $router = new Router($routes, $site);

        $routingExtension = new RoutingExtension($router);
        $twigRenderer     = new StringTwigRenderer($templatesDir, [$routingExtension]);

        $sourceFileRenderer = new SourceFileRenderer(
            $controllerExecutor,
            $twigRenderer,
            $site,
            $templatesDir,
            $sourceDir,
        );

        $filesystem = new Filesystem();

        $parsedown = new Parsedown();

        $rstParser = new RSTParser();

        $sourceFileBuilder = new SourceFileBuilder(
            $sourceFileRenderer,
            $filesystem,
            [
                new MarkdownConverter($parsedown),
                new ReStructuredTextConverter($rstParser),
            ],
            ['/\/api\//'],
        );

        $sourceFileParametersFactory = new SourceFileParametersFactory();

        $sourceFileFactory = new SourceFileFactory($router, $sourceFileParametersFactory, $sourceDir);

        $requestCollectionProvider = new RequestCollectionProvider([new UserRequests($userRepository)]);

        $sourceFileFilesystemReader = new SourceFileFilesystemReader($sourceDir, $sourceFileFactory);
        $sourceFileRouteReader      = new SourceFileRouteReader($router, $requestCollectionProvider, $sourceFileFactory);

        $sourceFileRepository = new SourceFileRepository([
            $sourceFileFilesystemReader,
            $sourceFileRouteReader,
        ]);

        $sourceFilesBuilder = new SourceFilesBuilder($sourceFileBuilder);

        $sourceFiles = $sourceFileRepository->getSourceFiles($buildDir);

        $sourceFilesBuilder->buildSourceFiles($sourceFiles);

        $indexContents = $this->getFileContents($buildDir, 'index.html');

        self::assertStringContainsString('This is a test file.', $indexContents);
        self::assertStringContainsString('Homepage: /index.html', $indexContents);
        self::assertStringContainsString('Controller data: This data came from the controller', $indexContents);
        self::assertStringContainsString('Request path info: /index.html', $indexContents);
        self::assertStringContainsString('User: jwage', $indexContents);
        self::assertStringContainsString('Source File URL: /index.html', $indexContents);
        self::assertStringContainsString('Source Path: /index.md', $indexContents);
        self::assertStringContainsString('Request Pathinfo: /index.html', $indexContents);
        self::assertStringContainsString('Page Title: Test Title', $indexContents);

        $apiIndexContents = $this->getFileContents($buildDir, 'api/index.html');

        self::assertSame('This should not be rendered by Twig!', trim($apiIndexContents));

        $jwageContents = $this->getFileContents($buildDir, 'user/jwage.html');

        self::assertStringContainsString('jwage', $jwageContents);

        $ocramiusContents = $this->getFileContents($buildDir, 'user/ocramius.html');

        self::assertStringContainsString('ocramius', $ocramiusContents);
    }

    private function getFileContents(string $buildDir, string $file): string
    {
        $path = $buildDir . '/' . $file;

        self::assertTrue(file_exists($path));

        $contents = file_get_contents($path);
        assert($contents !== false);

        return $contents;
    }
}
