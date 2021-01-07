<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\SourceFile;

use Doctrine\StaticWebsiteGenerator\Controller\ControllerExecutor;
use Doctrine\StaticWebsiteGenerator\Site;
use Doctrine\StaticWebsiteGenerator\Twig\TwigRenderer;
use InvalidArgumentException;

use function assert;
use function file_exists;
use function file_get_contents;
use function preg_match_all;
use function sprintf;
use function str_replace;

class SourceFileRenderer
{
    /** @var ControllerExecutor */
    private $controllerExecutor;

    /** @var TwigRenderer */
    private $twigRenderer;

    /** @var Site */
    private $site;

    /** @var string */
    private $templatesDir;

    /** @var string */
    private $sourceDir;

    public function __construct(
        ControllerExecutor $controllerExecutor,
        TwigRenderer $twigRenderer,
        Site $site,
        string $templatesDir,
        string $sourceDir
    ) {
        $this->controllerExecutor = $controllerExecutor;
        $this->twigRenderer       = $twigRenderer;
        $this->site               = $site;
        $this->templatesDir       = $templatesDir;
        $this->sourceDir          = $sourceDir;
    }

    public function render(SourceFile $sourceFile, string $contents): string
    {
        $pageParameters = $this->preparePageParameters($sourceFile);

        $parameters = [
            'page' => $pageParameters,
            'site' => $this->site,
        ];

        if ($sourceFile->hasController()) {
            $controllerResult = $this->controllerExecutor->execute($sourceFile);

            $parameters += $controllerResult->getParameters();

            $controllerTemplate = $controllerResult->getTemplate();

            if ($controllerTemplate !== '') {
                $templatePath = $this->templatesDir . $controllerTemplate;

                if (! file_exists($templatePath)) {
                    throw new InvalidArgumentException(
                        sprintf('Could not find template "%s"', $controllerTemplate)
                    );
                }

                $contents = file_get_contents($templatePath);
            }
        }

        assert($contents !== false);

        $template = $this->prepareTemplate($sourceFile, $contents);

        return $this->twigRenderer->render($template, $parameters);
    }

    /**
     * @return mixed[]
     */
    private function preparePageParameters(SourceFile $sourceFile): array
    {
        return $sourceFile->getParameters()->getAll() + [
            'date' => $sourceFile->getDate(),
            'sourceFile' => $sourceFile,
            'sourcePath' => $this->getSourceRelativePath($sourceFile),
            'request' => $sourceFile->getRequest(),
        ];
    }

    public function getSourceRelativePath(SourceFile $sourceFile): string
    {
        return str_replace($this->sourceDir, '', $sourceFile->getSourcePath());
    }

    private function prepareTemplate(SourceFile $sourceFile, string $contents): string
    {
        if ($sourceFile->isLayoutNeeded()) {
            if ($contents !== '') {
                $regex = '/{%\s+block\s+(\w+)\s+%}(.*?){%\s+endblock\s+%}/si';

                if (preg_match_all($regex, $contents, $matches) === 0) {
                    $contents = '{% block content %}' . $contents . '{% endblock %}';
                }
            }

            $contents = '{% extends "layouts/' . $sourceFile->getParameter('layout') . '.html.twig" %}' . $contents;
        }

        return $contents;
    }
}
