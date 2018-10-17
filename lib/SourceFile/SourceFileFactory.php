<?php

declare(strict_types=1);

namespace Doctrine\StaticWebsiteGenerator\SourceFile;

use Doctrine\StaticWebsiteGenerator\Routing\Router;
use const PATHINFO_EXTENSION;
use function assert;
use function file_get_contents;
use function in_array;
use function pathinfo;
use function str_replace;
use function strrpos;
use function substr;

class SourceFileFactory
{
    private const CONVERTED_HTML_EXTENSIONS = ['md', 'rst'];

    /** @var Router */
    private $router;

    /** @var SourceFileParametersFactory */
    private $sourceFileParametersFactory;

    /** @var string */
    private $sourceDir;

    public function __construct(
        Router $router,
        SourceFileParametersFactory $sourceFileParametersFactory,
        string $sourceDir
    ) {
        $this->router                      = $router;
        $this->sourceFileParametersFactory = $sourceFileParametersFactory;
        $this->sourceDir                   = $sourceDir;
    }

    public function createSourceFileFromPath(
        string $buildDir,
        string $sourcePath
    ) : SourceFile {
        return $this->createSourceFile(
            $buildDir,
            $sourcePath,
            $this->getFileContents($sourcePath)
        );
    }

    public function createSourceFile(
        string $buildDir,
        string $sourcePath,
        string $contents = ''
    ) : SourceFile {
        $sourceFileParameters = $this->sourceFileParametersFactory
            ->createSourceFileParameters($contents);

        $url       = $this->buildUrl($buildDir, $sourcePath, $sourceFileParameters->getAll());
        $writePath = $buildDir . $url;

        $sourceFileParameters->setParameter('url', $url);
        $sourceFileParameters->setParameter('writePath', $writePath);

        $route = $this->router->match($url);

        if ($route !== null) {
            $sourceFileParameters->merge($route);
        }

        return new SourceFile(
            $sourcePath,
            $contents,
            $sourceFileParameters
        );
    }

    /**
     * @param mixed[] $parameters
     */
    private function buildUrl(string $buildDir, string $sourcePath, array $parameters) : string
    {
        $permalink = $parameters['permalink'] ?? '';

        if ($permalink !== '' && $permalink !== 'none') {
            return $permalink;
        }

        $writePath = $this->buildWritePath($buildDir, $sourcePath);

        return str_replace($buildDir, '', $writePath);
    }

    private function buildWritePath(string $buildDir, string $sourcePath) : string
    {
        $writePath = $buildDir . str_replace($this->sourceDir, '', $sourcePath);

        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);

        if (in_array($extension, self::CONVERTED_HTML_EXTENSIONS, true)) {
            $writePath = substr($writePath, 0, (int) strrpos($writePath, '.')) . '.html';
        }

        return $writePath;
    }

    private function getFileContents(string $path) : string
    {
        $contents = file_get_contents($path);
        assert($contents !== false);

        return $contents;
    }
}
