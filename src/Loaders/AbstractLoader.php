<?php

namespace Torann\SnazzyTwig\Loaders;

use Twig_Source;
use Twig_LoaderInterface;

abstract class AbstractLoader implements Twig_LoaderInterface
{
    /**
     * Path to the layouts.
     *
     * @var string
     */
    protected $path;

    /**
     * Set the path for the view.
     *
     * @param string $path
     *
     * @return self
     */
    public function setPath($path)
    {
        $this->path = rtrim($path, '/');

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSourceContext($name)
    {
        // Get source
        $source = $this->getSource($name);

        // Extend all non layout templates
        if ($name !== 'layout.twig' && strpos($name, '/') === false) {
            $source = '{% extends "layout.twig" %}' . $source;
        }

        return new Twig_Source($source, $name, $this->getTemplatePath($name));
    }

    /**
     * Returns the source context for a given template logical name.
     *
     * @param string $name
     *
     * @return string
     */
    abstract public function getSource($name);

    /**
     * @inheritdoc
     */
    public function getCacheKey($name)
    {
        return $this->getTemplatePath($name);
    }

    /**
     * @inheritdoc
     */
    public function exists($name)
    {
        config();

        return $this->filesystem->exists($this->getTemplatePath($name));
    }

    /**
     * @inheritdoc
     */
    public function isFresh($name, $time)
    {
        return true;
    }

    /**
     * Get the pull path to the template
     *
     * @param string $name
     *
     * @return string
     */
    protected function getTemplatePath($name)
    {
        $path =  $this->path . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $name;

        // If not requesting a preview then skip this
        if (config('twig.preview_mode', false) === false) {
            return $path;
        }

        // Set the preview file path
        $preview_path = preg_replace_callback('/[^.\/]+?\.[^.]+$/i', function($matches) {
            return "preview_{$matches[0]}";
        }, $path);

        // Only return the path if it exists
        return file_exists($preview_path)
            ? $preview_path
            : $path;
    }
}
