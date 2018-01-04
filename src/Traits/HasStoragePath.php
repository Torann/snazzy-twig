<?php

namespace Torann\SnazzyTwig\Traits;

trait HasStoragePath
{
    /**
     * The storage page for the resource.
     *
     * @var string
     */
    protected $storage_path = 'websites/{id}';

    /**
     * Get storage path for resource.
     *
     * @param string $path
     *
     * @return string
     */
    public function getStoragePath($path = '')
    {
        $storage = str_replace('{id}', $this->getKey(), $this->storage_path);

        return $storage . ($path ? '/' . $path : $path);
    }
}