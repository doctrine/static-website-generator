<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\Tests\SourceFile;

use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFile;
use Doctrine\StaticWebsiteGenerator\SourceFile\SourceFileParameters;
use PHPUnit\Framework\TestCase;

class SourceFileTest extends TestCase
{
    /** @var SourceFile */
    private $sourceFile;

    public function testGetSourcePath() : void
    {
        self::assertSame('/tmp/test.md', $this->sourceFile->getSourcePath());
    }

    public function testGetUrl() : void
    {
        self::assertSame('/2019/01/01/test.html', $this->sourceFile->getUrl());
    }

    public function testGetDate() : void
    {
        self::assertEquals('2019-01-01', $this->sourceFile->getDate()->format('Y-m-d'));
    }

    public function testGetExtension() : void
    {
        self::assertEquals('md', $this->sourceFile->getExtension());
    }

    public function testIsMarkdown() : void
    {
        $sourceFile = new SourceFile(
            '/tmp/test.rst',
            'test',
            new SourceFileParameters(['url' => '/2019/01/01/test.html'])
        );

        self::assertFalse($sourceFile->isMarkdown());

        self::assertTrue($this->sourceFile->isMarkdown());
    }

    public function testIsRestructuredText() : void
    {
        $sourceFile = new SourceFile(
            '/tmp/test.rst',
            'test',
            new SourceFileParameters(['url' => '/2019/01/01/test.html'])
        );

        self::assertTrue($sourceFile->isRestructuredText());

        self::assertFalse($this->sourceFile->isRestructuredText());
    }

    public function testIsTwig() : void
    {
        $sourceFile = new SourceFile(
            '/tmp/test.jpg',
            'test',
            new SourceFileParameters(['url' => '/test.jpg'])
        );

        self::assertFalse($sourceFile->isTwig());

        self::assertTrue($this->sourceFile->isTwig());
    }

    public function isApiDocs() : void
    {
        $sourceFile = new SourceFile(
            '/tmp/api/test.html',
            'test',
            new SourceFileParameters(['url' => '/api/test.html'])
        );

        self::assertTrue($sourceFile->isApiDocs());

        self::assertFalse($this->sourceFile->isApiDocs());
    }

    public function testGetContents() : void
    {
        self::assertSame('Test content.', $this->sourceFile->getContents());
    }

    public function testGetParameters() : void
    {
        self::assertEquals(
            new SourceFileParameters(['url' => '/2019/01/01/test.html']),
            $this->sourceFile->getParameters()
        );
    }

    public function testGetParameter() : void
    {
        self::assertSame('/2019/01/01/test.html', $this->sourceFile->getParameter('url'));
    }

    public function testGetRequest() : void
    {
        $request = $this->sourceFile->getRequest();

        self::assertSame('/2019/01/01/test.html', $request->getPathInfo());
        self::assertSame($this->sourceFile, $request->attributes->get('sourceFile'));
        self::assertSame('/2019/01/01/test.html', $request->attributes->get('url'));
    }

    protected function setUp() : void
    {
        $contents = <<<CONTENTS
---
test: true
---

Test content.
CONTENTS;

        $this->sourceFile = new SourceFile(
            '/tmp/test.md',
            $contents,
            new SourceFileParameters(['url' => '/2019/01/01/test.html'])
        );
    }
}
