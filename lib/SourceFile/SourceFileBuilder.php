<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\SourceFile;

use Symfony\Component\Filesystem\Filesystem;

use function preg_match;

class SourceFileBuilder
{
    /** @var SourceFileRenderer */
    private $sourceFileRenderer;

    /** @var Filesystem */
    private $filesystem;

    /** @var SourceFileConverter[] */
    private $converters;

    /** @var string[] */
    private $nonRenderablePatterns = [];

    /**
     * @param SourceFileConverter[] $converters
     * @param string[]              $nonRenderablePatterns
     */
    public function __construct(
        SourceFileRenderer $sourceFileRenderer,
        Filesystem $filesystem,
        array $converters,
        array $nonRenderablePatterns
    ) {
        $this->sourceFileRenderer = $sourceFileRenderer;
        $this->filesystem         = $filesystem;

        foreach ($converters as $converter) {
            foreach ($converter->getExtensions() as $extension) {
                $this->converters[$extension] = $converter;
            }
        }

        $this->nonRenderablePatterns = $nonRenderablePatterns;
    }

    public function buildFile(SourceFile $sourceFile): void
    {
        $renderedFile = $this->convertSourceFile($sourceFile);

        if ($this->isSourceFileRenderable($sourceFile)) {
            $renderedFile = $this->sourceFileRenderer->render(
                $sourceFile,
                $renderedFile
            );
        }

        $this->filesystem->dumpFile($sourceFile->getParameter('writePath'), $renderedFile);
    }

    private function convertSourceFile(SourceFile $sourceFile): string
    {
        $extension = $sourceFile->getExtension();

        if (isset($this->converters[$extension])) {
            return $this->converters[$extension]->convertSourceFile($sourceFile);
        }

        return $sourceFile->getContents();
    }

    private function isSourceFileRenderable(SourceFile $sourceFile): bool
    {
        if (! $sourceFile->isTwig()) {
            return false;
        }

        foreach ($this->nonRenderablePatterns as $nonRenderablePattern) {
            if (preg_match($nonRenderablePattern, $sourceFile->getSourcePath()) > 0) {
                return false;
            }
        }

        return true;
    }
}
