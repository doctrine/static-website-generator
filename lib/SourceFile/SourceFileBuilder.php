<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\SourceFile;

use Symfony\Component\Filesystem\Filesystem;

class SourceFileBuilder
{
    /** @var SourceFileRenderer */
    private $sourceFileRenderer;

    /** @var Filesystem */
    private $filesystem;

    /** @var SourceFileConverter[] */
    private $converters;

    /**
     * @param SourceFileConverter[] $converters
     */
    public function __construct(
        SourceFileRenderer $sourceFileRenderer,
        Filesystem $filesystem,
        array $converters
    ) {
        $this->sourceFileRenderer = $sourceFileRenderer;
        $this->filesystem         = $filesystem;

        foreach ($converters as $converter) {
            foreach ($converter->getExtensions() as $extension) {
                $this->converters[$extension] = $converter;
            }
        }
    }

    public function buildFile(SourceFile $sourceFile) : void
    {
        $renderedFile = $this->convertSourceFile($sourceFile);

        if ($sourceFile->isTwig()) {
            $renderedFile = $this->sourceFileRenderer->render(
                $sourceFile,
                $renderedFile
            );
        }

        $this->filesystem->dumpFile($sourceFile->getParameter('writePath'), $renderedFile);
    }

    private function convertSourceFile(SourceFile $sourceFile) : string
    {
        $extension = $sourceFile->getExtension();

        if (isset($this->converters[$extension])) {
            return $this->converters[$extension]->convertSourceFile($sourceFile);
        }

        return $sourceFile->getContents();
    }
}
