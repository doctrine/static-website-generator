<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\Tests\SourceFile;

use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFile;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileFactory;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileFilesystemReader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function assert;
use function is_string;
use function realpath;

class SourceFileFilesystemReaderTest extends TestCase
{
    private bool|string $rootDir;

    private SourceFileFactory&MockObject $sourceFileFactory;

    private SourceFileFilesystemReader $sourceFileFilesystemReader;

    public function testGetFilesWithoutCustomInPath(): void
    {
        $buildDir = '/build/dir';

        $sourceFile1 = $this->createMock(SourceFile::class);

        $this->sourceFileFactory->expects(self::atLeastOnce())
            ->method('createSourceFileFromPath')
            ->willReturn($sourceFile1);

        $sourceFiles = $this->sourceFileFilesystemReader->getSourceFiles($buildDir);

        self::assertSame($sourceFile1, $sourceFiles[0]);
    }

    protected function setUp(): void
    {
        $this->rootDir = realpath(__DIR__ . '/../fixtures');
        assert(is_string($this->rootDir));

        $this->sourceFileFactory = $this->createMock(SourceFileFactory::class);

        $this->sourceFileFilesystemReader = new SourceFileFilesystemReader(
            $this->rootDir,
            $this->sourceFileFactory,
        );
    }
}
