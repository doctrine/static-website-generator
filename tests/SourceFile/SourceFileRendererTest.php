<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\Tests\SourceFile;

use DateTimeImmutable;
use Doctrine\StaticWebsiteGenerator\Controller\ControllerExecutor;
use Doctrine\StaticWebsiteGenerator\Controller\Response;
use Doctrine\StaticWebsiteGenerator\Site;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFile;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileParameters;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileRenderer;
use Doctrine\StaticWebsiteGenerator\Twig\TwigRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class SourceFileRendererTest extends TestCase
{
    /** @var ControllerExecutor|MockObject */
    private $controllerExecutor;

    /** @var TwigRenderer|MockObject */
    private $twigRenderer;

    /** @var Site|MockObject */
    private $site;

    /** @var SourceFileRenderer */
    private $sourceFileRenderer;

    public function testRenderWithContentTwigBlock(): void
    {
        $date                 = new DateTimeImmutable('2018-09-01');
        $sourceFile           = $this->createMock(SourceFile::class);
        $sourceFileParameters = new SourceFileParameters(['_controller' => ['TestController', 'index']]);
        $request              = $this->createMock(Request::class);
        $response             = new Response(['test' => true]);
        $contents             = 'Test';

        $sourceFile->expects(self::once())
            ->method('getDate')
            ->willReturn($date);

        $sourceFile->expects(self::once())
            ->method('getSourcePath')
            ->willReturn('/test/source/index.md');

        $sourceFile->expects(self::once())
            ->method('getParameters')
            ->willReturn($sourceFileParameters);

        $sourceFile->expects(self::once())
            ->method('isLayoutNeeded')
            ->willReturn(true);

        $sourceFile->expects(self::once())
            ->method('getParameter')
            ->with('layout')
            ->willReturn('default');

        $sourceFile->expects(self::once())
            ->method('getRequest')
            ->willReturn($request);

        $sourceFile->expects(self::once())
            ->method('hasController')
            ->willReturn(true);

        $this->controllerExecutor->expects(self::once())
            ->method('execute')
            ->with($sourceFile)
            ->willReturn($response);

        $this->twigRenderer->expects(self::once())
            ->method('render')
            ->with('{% extends "layouts/default.html.twig" %}{% block content %}Test{% endblock %}', [
                'page' => [
                    'date' => $date,
                    '_controller' => ['TestController', 'index'],
                    'sourceFile' => $sourceFile,
                    'sourcePath' => '/index.md',
                    'request' => $request,
                ],
                'site' => $this->site,
                'test' => true,
            ]);

        $this->sourceFileRenderer->render(
            $sourceFile,
            $contents
        );
    }

    public function testRenderWithoutContentTwigBlock(): void
    {
        $date                 = new DateTimeImmutable('2018-09-01');
        $sourceFile           = $this->createMock(SourceFile::class);
        $sourceFileParameters = new SourceFileParameters(['_controller' => ['TestController', 'index']]);
        $request              = $this->createMock(Request::class);
        $response             = new Response(['test' => true]);
        $contents             = '{% block content %}Testing{% endblock %}';

        $sourceFile->expects(self::once())
            ->method('getDate')
            ->willReturn($date);

        $sourceFile->expects(self::once())
            ->method('getSourcePath')
            ->willReturn('/test/source/index.md');

        $sourceFile->expects(self::once())
            ->method('getParameters')
            ->willReturn($sourceFileParameters);

        $sourceFile->expects(self::once())
            ->method('isLayoutNeeded')
            ->willReturn(true);

        $sourceFile->expects(self::once())
            ->method('getParameter')
            ->with('layout')
            ->willReturn('default');

        $sourceFile->expects(self::once())
            ->method('getRequest')
            ->willReturn($request);

        $sourceFile->expects(self::once())
            ->method('hasController')
            ->willReturn(true);

        $this->controllerExecutor->expects(self::once())
            ->method('execute')
            ->with($sourceFile)
            ->willReturn($response);

        $this->twigRenderer->expects(self::once())
            ->method('render')
            ->with('{% extends "layouts/default.html.twig" %}{% block content %}Testing{% endblock %}', [
                'page' => [
                    'date' => $date,
                    '_controller' => ['TestController', 'index'],
                    'sourceFile' => $sourceFile,
                    'sourcePath' => '/index.md',
                    'request' => $request,
                ],
                'site' => $this->site,
                'test' => true,
            ]);

        $this->sourceFileRenderer->render(
            $sourceFile,
            $contents
        );
    }

    protected function setUp(): void
    {
        $this->controllerExecutor = $this->createMock(ControllerExecutor::class);
        $this->twigRenderer       = $this->createMock(TwigRenderer::class);
        $this->site               = $this->createMock(Site::class);

        $this->sourceFileRenderer = new SourceFileRenderer(
            $this->controllerExecutor,
            $this->twigRenderer,
            $this->site,
            '/test/templates',
            '/test/source'
        );
    }
}
