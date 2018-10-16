<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\SourceFile;

use Doctrine\Common\Collections\ArrayCollection;
use function strpos;

class SourceFiles extends ArrayCollection
{
    public function in(string $path) : self
    {
        /** @var SourceFiles $sourceFiles */
        $sourceFiles = $this->filter(static function (SourceFile $sourceFile) use ($path) {
            return strpos($sourceFile->getSourcePath(), $path) !== false;
        });

        return $sourceFiles;
    }
}
