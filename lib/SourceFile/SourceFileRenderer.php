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
use function sprintf;

class SourceFileRenderer
{
    /** @var ControllerExecutor */
    private $controllerExecutor;

    /** @var TwigRenderer */
    private $twigRenderer;

    /** @var Site */
    private $site;

    /** @var string */
    private $templatesPath;

    public function __construct(
        ControllerExecutor $controllerExecutor,
        TwigRenderer $twigRenderer,
        Site $site,
        string $templatesPath
    ) {
        $this->controllerExecutor = $controllerExecutor;
        $this->twigRenderer       = $twigRenderer;
        $this->site               = $site;
        $this->templatesPath      = $templatesPath;
    }

    public function render(SourceFile $sourceFile, string $contents) : string
    {
        $pageParameters = $sourceFile->getParameters()->getAll();

        $parameters = [
            'page' => $pageParameters,
            'site' => $this->site,
        ];

        if ($sourceFile->hasController()) {
            $controllerResult = $this->controllerExecutor->execute($sourceFile);

            $parameters += $controllerResult->getParameters();

            $controllerTemplate = $controllerResult->getTemplate();

            if ($controllerTemplate !== '') {
                $templatePath = $this->templatesPath . $controllerTemplate;

                if (! file_exists($templatePath)) {
                    throw new InvalidArgumentException(
                        sprintf('Could not find template "%s"', $controllerTemplate)
                    );
                }

                $contents = file_get_contents($templatePath);
            }
        }

        assert($contents !== false);

        return $this->twigRenderer->render($contents, $parameters);
    }
}
