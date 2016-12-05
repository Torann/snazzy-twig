<?php

namespace Torann\SnazzyTwig\Loaders;

use App\Website;
use Twig_LoaderInterface;
use Illuminate\Filesystem\FilesystemManager;

class Filesystem implements Twig_LoaderInterface
{
    /**
     * Path to the layouts.
     *
     * @var string
     */
    protected $path;

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
     * @param FilesystemManager $filesystem
     * @param string           $path
     */
    public function __construct(FilesystemManager $filesystem, $path)
    {
        $this->filesystem = $filesystem;
        $this->path = trim($path, '/');
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
        if ($name !== 'layout.twig' && strpos($name, '/') === false) {
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
     * @param string @name
     *
     * @return string
     */
    protected function getTemplatePath($name)
    {
        return $this->path . '/layouts/' . $name;
    }
}
