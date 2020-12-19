<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\SourceFile\Converters;

use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFile;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileConverter;
use Parsedown;

class MarkdownConverter implements SourceFileConverter
{
    /** @var Parsedown */
    private $parsedown;

    public function __construct(Parsedown $parsedown)
    {
        $this->parsedown = $parsedown;
    }

    /**
     * @return string[]
     */
    public function getExtensions(): array
    {
        return ['md', 'markdown'];
    }

    public function convertSourceFile(SourceFile $sourceFile): string
    {
        return $this->parsedown->text($sourceFile->getContents());
    }
}
