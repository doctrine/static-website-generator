<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\SourceFile;

interface SourceFileReader
{
    public function getSourceFiles(string $buildDir = ''): SourceFiles;
}
