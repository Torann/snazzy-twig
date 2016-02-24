<?php

namespace Torann\SnazzyTwig\Cache;

use Exception;
use App\Website;
use Twig_CacheInterface;
use Illuminate\Filesystem\FilesystemManager;

class Filesystem implements Twig_CacheInterface
{
    /**
     * Website instance.
     *
     * @var \App\Website
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
     * @param \App\Website      $website
     * @param string           $directory
     */
    public function __construct(FilesystemManager $filesystem, Website $website, $directory = 'twig-cache')
    {
        $this->filesystem = $filesystem;
        $this->website = $website;
        $this->directory = $directory;
    }

    /**
     * {@inheritdoc}
     */
    public function generateKey($name, $className)
    {
        $hash = hash('sha256', $className);

        return $this->directory . '/' . $this->website->getCacheKey() . '/' . $hash;
    }

    /**
     * {@inheritdoc}
     */
    public function load($key)
    {
        try {
            $content = $this->filesystem->get($key);

            eval('?>' . $content);
        } catch (Exception $e) {
        }
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getTimestamp($key)
    {
        if ($this->filesystem->exists($key) === false) {
            return 0;
        }

        return $this->filesystem->lastModified($key);
    }
}