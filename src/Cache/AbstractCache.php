<?php

namespace Torann\SnazzyTwig\Cache;

use Exception;
use Twig_CacheInterface;
use Illuminate\Filesystem\FilesystemManager;
use Torann\SnazzyTwig\Contracts\WebsiteInterface;

abstract class AbstractCache implements Twig_CacheInterface
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
     * Set website.
     *
     * @param WebsiteInterface $website
     *
     * @return self
     */
    public function setWebsite(WebsiteInterface $website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function generateKey($name, $className)
    {
        $hash = hash('sha256', $className);

        return $this->website->getCacheKey() . '/' . $hash;
    }

    /**
     * @inheritdoc
     */
    public function load($key)
    {
        try {
            $content = $this->getSource($key);

            eval('?>' . $content);
        }
        catch (Exception $e) {
        }
    }

    /**
     * Get the cached source.
     *
     * @param string $key
     *
     * @return string
     */
    abstract public function getSource($key);
}