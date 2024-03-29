<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\SourceFile;

use Doctrine\Common\Collections\ArrayCollection;

use function assert;
use function strpos;

/** @template-extends ArrayCollection<int, SourceFile> */
class SourceFiles extends ArrayCollection
{
    /** @return SourceFiles<SourceFile> */
    public function in(string $path): self
    {
        $sourceFiles = $this->filter(static function (SourceFile $sourceFile) use ($path): bool {
            return strpos($sourceFile->getSourcePath(), $path) !== false;
        });
        assert($sourceFiles instanceof SourceFiles);

        return $sourceFiles;
    }
}
