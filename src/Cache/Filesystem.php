<?php

namespace Torann\SnazzyTwig\Cache;

use Exception;
use Illuminate\Filesystem\FilesystemManager;
use Torann\SnazzyTwig\Contracts\WebsiteInterface;

class Filesystem extends AbstractCache
{
    /**
     * Website instance.
     *
     * @var WebsiteInterface
     */
    protected $website;

    /**
     * Filesystem instance
     *
     * @var FilesystemManager
     */
    protected $filesystem;

    /**
     * Base directory for storing the cache.
     *
     * @var string
     */
    protected $directory;

    /**
     * Create new filesystem cache instance.
     *
     * @param FilesystemManager $filesystem
     * @param string            $directory
     */
    public function __construct(FilesystemManager $filesystem, $directory = 'twig-cache')
    {
        $this->filesystem = $filesystem;
        $this->directory = $directory;
    }

    /**
     * @inheritdoc
     */
    public function generateKey($name, $className)
    {
        return $this->directory . '/' . parent::generateKey($name, $className);
    }

    /**
     * Get the cached source.
     * 
     * @param string $key
     * 
     * @return string
     */
    public function getSource($key)
    {
        try {
            return $this->filesystem->get($key);
        }
        catch (Exception $e) {
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function write($key, $content)
    {
        $this->filesystem->put($key, $content);

        // Compile cached file into bytecode cache
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($key, true);
        }
        elseif (function_exists('apc_compile_file')) {
            apc_compile_file($key);
        }
    }

    /**
     * @inheritdoc
     */
    public function getTimestamp($key)
    {
        if ($this->filesystem->exists($key) === false) {
            return 0;
        }

        return $this->filesystem->lastModified($key);
    }
}