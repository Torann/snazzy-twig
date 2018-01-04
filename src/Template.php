<?php

namespace Torann\SnazzyTwig;

use Twig_Template;

abstract class Template extends Twig_Template
{
    /**
     * Template name
     *
     * @var string
     */
    protected $name = null;

    /**
     * Data that should be available to this template only.
     *
     * @var array
     */
    protected $with = [];

    /**
     * Add a piece of data to the view.
     *
     * @param array|string $key
     * @param mixed        $value
     *
     * @return mixed
     */
    public function with($key, $value = null)
    {
        if (!is_array($key)) {
            return $this->with[$key] = $value;
        }

        foreach ($key as $innerKey => $innerValue) {
            $this->with($innerKey, $innerValue);
        }
    }

    /**
     * Displays a widget block.
     *
     * @param string $name
     * @param array  $options
     * @param array  $context
     * @param array  $blocks
     *
     * @internal
     */
    public function displayWidget($name, array $options = [], array $context = [], array $blocks = [])
    {
        // Remove the unique widget name prefix and fire event
        $this->env->fire('composing: widgets.' . $name, [$this, $context, $options]);

        // Render widget with new context
        $this->displayBlock($name, array_merge($context, $this->with), $blocks);
    }

    /**
     * Set the name of this template, as called by the developer.
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @inheritdoc
     */
    protected function getAttribute($object, $item, array $arguments = [], $type = Twig_Template::ANY_CALL, $isDefinedTest = false, $ignoreStrictCheck = false)
    {
        // We need to handle accessing attributes on an model instances differently
        if (Twig_Template::METHOD_CALL !== $type && $this->isModel($object)) {

            // We can't easily find out if an attribute actually exists, so return true
            if ($isDefinedTest) {
                return true;
            }

            return $object->getAttribute($item);
        }
        else {
            return parent::getAttribute($object, $item, $arguments, $type, $isDefinedTest, $ignoreStrictCheck);
        }
    }

    /**
     * Determine is object is a valid model instance
     *
     * @param mixed $object
     *
     * @return bool
     */
    protected function isModel($object)
    {
        return ($object instanceof \Illuminate\Database\Eloquent\Model)
            || ($object instanceof \BaseApiClient\Models\Model);
    }
}