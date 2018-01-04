<?php

namespace Torann\SnazzyTwig\Loaders;

class Filesystem extends AbstractLoader
{
    /**
     * @inheritdoc
     */
    public function getSource($name)
    {
        return file_get_contents($this->getTemplatePath($name));
    }

    /**
     * @inheritdoc
     */
    public function exists($name)
    {
        return file_exists($this->getTemplatePath($name));
    }
}
