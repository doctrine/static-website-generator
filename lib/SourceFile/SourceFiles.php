<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\SourceFile;

use Doctrine\Common\Collections\ArrayCollection;

use function assert;
use function strpos;

class SourceFiles extends ArrayCollection
{
    public function in(string $path): self
    {
        $sourceFiles = $this->filter(static function (SourceFile $sourceFile) use ($path) {
            return strpos($sourceFile->getSourcePath(), $path) !== false;
        });
        assert($sourceFiles instanceof SourceFiles);

        return $sourceFiles;
    }
}
