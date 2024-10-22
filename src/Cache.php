<?php

namespace Templar;

class Cache
{
    protected string $cachePath;

    public function __construct(string $cachePath)
    {
        $this->cachePath = $cachePath;
    }

    // Check if the template is already cached
    public function isCached(string $viewFile): bool
    {
        $cacheFile = $this->getCacheFile(viewFile: $viewFile);
        return file_exists($cacheFile) && filemtime($viewFile) <= filemtime($cacheFile);
    }

    // Get the cached file path
    public function getCacheFile(string $viewFile): string
    {
        return $this->cachePath . '/' . md5($viewFile) . '.php';
    }
}
