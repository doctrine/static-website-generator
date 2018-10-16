<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\SourceFile;

use RuntimeException;
use Throwable;
use function sprintf;

class SourceFilesBuilder
{
    /** @var SourceFileBuilder */
    private $sourceFileBuilder;

    public function __construct(
        SourceFileBuilder $sourceFileBuilder
    ) {
        $this->sourceFileBuilder = $sourceFileBuilder;
    }

    /**
     * @throws RuntimeException
     */
    public function buildSourceFiles(SourceFiles $sourceFiles) : void
    {
        foreach ($sourceFiles as $sourceFile) {
            try {
                $this->sourceFileBuilder->buildFile($sourceFile);
            } catch (Throwable $e) {
                throw new RuntimeException(sprintf(
                    'Failed building file "%s" with error "%s',
                    $sourceFile->getSourcePath(),
                    $e->getMessage() . "\n\n" . $e->getTraceAsString()
                ));
            }
        }
    }
}
