<?php

namespace Torann\SnazzyTwig\Loaders;

use App\Website;
use Twig_LoaderInterface;
use Illuminate\Filesystem\FilesystemManager;

class Filesystem implements Twig_LoaderInterface
{
    /**
     * Loader type.
     *
     * @var string
     */
    protected $type;

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
    public $filesystem;

    /**
     * Cached templates.
     *
     * @var array
     */
    protected $cache;

    /**
     * Create new Twig filesystem instance.
     *
     * @param string            $type
     * @param FilesystemManager $filesystem
     * @param \App\Website      $website
     */
    public function __construct($type, FilesystemManager $filesystem, Website $website)
    {
        $this->type = $type;
        $this->filesystem = $filesystem;
        $this->website = $website;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource($name)
    {
        $key = $this->getCacheKey($name);

        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        // Get source
        $source = $this->filesystem->get($this->getTemplatePath($name));

        // Extend all non layout templates
        if ($name !== 'layout.twig' && substr($name, 0, 8) !== 'widgets/') {
            return $this->cache[$key] = '{% extends "layout.twig" %}' . $source;
        }

        return $this->cache[$key] = $source;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKey($name)
    {
        return $this->getTemplatePath($name);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        return $this->filesystem->exists($this->getTemplatePath($name));
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh($name, $time)
    {
        return true;
    }

    /**
     * Get the pull path to the template
     *
     * @param  string @name
     *
     * @return string
     */
    protected function getTemplatePath($name)
    {
        return $this->website->getPath($this->type, $name);
    }
}
