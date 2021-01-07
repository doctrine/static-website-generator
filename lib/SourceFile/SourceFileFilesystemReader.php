<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\SourceFile;

use Symfony\Component\Finder\Finder;

use function assert;
use function is_string;

class SourceFileFilesystemReader implements SourceFileReader
{
    /** @var string */
    private $sourceDir;

    /** @var SourceFileFactory */
    private $sourceFileFactory;

    public function __construct(
        string $sourceDir,
        SourceFileFactory $sourceFileFactory
    ) {
        $this->sourceDir         = $sourceDir;
        $this->sourceFileFactory = $sourceFileFactory;
    }

    /**
     * @return SourceFiles<SourceFile>
     */
    public function getSourceFiles(string $buildDir = ''): SourceFiles
    {
        $sourceFiles = [];

        foreach ($this->createFinder() as $splFileInfo) {
            $sourcePath = $splFileInfo->getRealPath();
            assert(is_string($sourcePath));

            $sourceFiles[] = $this->sourceFileFactory->createSourceFileFromPath(
                $buildDir,
                $sourcePath
            );
        }

        return new SourceFiles($sourceFiles);
    }

    private function createFinder(): Finder
    {
        $finder = new Finder();

        $finder
            ->in($this->sourceDir)
            ->files();

        return $finder;
    }
}
