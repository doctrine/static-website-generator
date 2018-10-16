<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\Tests;

use Doctrine\RST\Parser as RSTParser;
use Doctrine\StaticWebsiteGenerator\Controller\ControllerExecutor;
use Doctrine\StaticWebsiteGenerator\Controller\ControllerProvider;
use Doctrine\StaticWebsiteGenerator\Routing\Router;
use Doctrine\StaticWebsiteGenerator\Site;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileBuilder;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileFactory;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileFilesystemReader;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileParametersFactory;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileRenderer;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileRepository;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFilesBuilder;
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

        $controllerProvider = new ControllerProvider([]);
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
                'defaults' => ['title' => 'Homepage'],
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
    }
}
