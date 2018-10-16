<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\SourceFile;

class SourceFileRepository implements SourceFileReader
{
    /** @var SourceFileReader[] */
    private $sourceFileReaders = [];

    /**
     * @param SourceFileReader[] $sourceFileReaders
     */
    public function __construct(array $sourceFileReaders)
    {
        $this->sourceFileReaders = $sourceFileReaders;
    }

    public function getSourceFiles(string $buildDir = '') : SourceFiles
    {
        $sourceFiles = [];

        foreach ($this->sourceFileReaders as $sourceFileReader) {
            foreach ($sourceFileReader->getSourceFiles($buildDir) as $sourceFile) {
                $sourceFiles[] = $sourceFile;
            }
        }

        return new SourceFiles($sourceFiles);
    }
}
