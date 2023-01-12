<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\SourceFile\Converters;

use Doctrine\RST\Parser as RSTParser;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFile;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileConverter;

class ReStructuredTextConverter implements SourceFileConverter
{
    public function __construct(private RSTParser $rstParser)
    {
    }

    /** @return string[] */
    public function getExtensions(): array
    {
        return ['rst'];
    }

    public function convertSourceFile(SourceFile $sourceFile): string
    {
        return $this->rstParser->parse($sourceFile->getContents())->render();
    }
}
