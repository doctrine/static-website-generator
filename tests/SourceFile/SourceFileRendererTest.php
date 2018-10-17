<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\Tests\SourceFile;

use Doctrine\StaticWebsiteGenerator\Controller\ControllerExecutor;
use Doctrine\StaticWebsiteGenerator\Controller\Response;
use Doctrine\StaticWebsiteGenerator\Site;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFile;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileParameters;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileRenderer;
use Doctrine\StaticWebsiteGenerator\Twig\TwigRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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

    public function testRenderWithContentTwigBlock() : void
    {
        $sourceFile           = $this->createMock(SourceFile::class);
        $sourceFileParameters = new SourceFileParameters(['_controller' => ['TestController', 'index']]);
        $response             = new Response(['test' => true]);
        $contents             = 'Test';

        $sourceFile->expects(self::once())
            ->method('getParameters')
            ->willReturn($sourceFileParameters);

        $sourceFile->expects(self::once())
            ->method('hasController')
            ->willReturn(true);

        $this->controllerExecutor->expects(self::once())
            ->method('execute')
            ->with($sourceFile)
            ->willReturn($response);

        $this->twigRenderer->expects(self::once())
            ->method('render')
            ->with('Test', [
                'page' => [
                    '_controller' => ['TestController', 'index'],
                ],
                'site' => $this->site,
                'test' => true,
            ]);

        $this->sourceFileRenderer->render(
            $sourceFile,
            $contents
        );
    }

    public function testRenderWithoutContentTwigBlock() : void
    {
        $sourceFile           = $this->createMock(SourceFile::class);
        $sourceFileParameters = new SourceFileParameters(['_controller' => ['TestController', 'index']]);
        $response             = new Response(['test' => true]);
        $contents             = '{% block content %}Testing{% endblock %}';

        $sourceFile->expects(self::once())
            ->method('getParameters')
            ->willReturn($sourceFileParameters);

        $sourceFile->expects(self::once())
            ->method('hasController')
            ->willReturn(true);

        $this->controllerExecutor->expects(self::once())
            ->method('execute')
            ->with($sourceFile)
            ->willReturn($response);

        $this->twigRenderer->expects(self::once())
            ->method('render')
            ->with('{% block content %}Testing{% endblock %}', [
                'page' => [
                    '_controller' => ['TestController', 'index'],
                ],
                'site' => $this->site,
                'test' => true,
            ]);

        $this->sourceFileRenderer->render(
            $sourceFile,
            $contents
        );
    }

    protected function setUp() : void
    {
        $this->controllerExecutor = $this->createMock(ControllerExecutor::class);
        $this->twigRenderer       = $this->createMock(TwigRenderer::class);
        $this->site               = $this->createMock(Site::class);

        $this->sourceFileRenderer = new SourceFileRenderer(
            $this->controllerExecutor,
            $this->twigRenderer,
            $this->site,
            ''
        );
    }
}
