<?php

namespace Torann\SnazzyTwig\Contracts;

interface WebsiteInterface
{
    /**
     * Get storage path for resource.
     *
     * @param string $path
     *
     * @return string
     */
    public function getStoragePath($path = '');

    /**
     * Return website base cache key.
     *
     * @return string
     */
    public function getCacheKey();
}