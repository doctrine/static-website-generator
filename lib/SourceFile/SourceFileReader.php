<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\SourceFile;

interface SourceFileReader
{
    /**
     * @return SourceFiles<SourceFile>
     */
    public function getSourceFiles(string $buildDir = ''): SourceFiles;
}
