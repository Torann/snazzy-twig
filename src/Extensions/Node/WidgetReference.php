<?php

namespace Torann\SnazzyTwig\Extensions\Node;

use Twig_Node;
use Twig_Compiler;
use Twig_Node_Expression;
use Twig_NodeOutputInterface;

class WidgetReference extends Twig_Node implements Twig_NodeOutputInterface
{
    public function __construct($name, array $options = [], $lineno, $tag = null)
    {
        parent::__construct([], ['name' => $name, 'options' => $options], $lineno, $tag);
    }

    public function compile(Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write(sprintf("\$this->displayWidget('%s', %s, \$context, \$blocks);\n",
                $this->getAttribute('name'),
                $this->optionsToString($this->getAttribute('options'))
            ));
    }

    /**
     * Convert an array to a string.
     *
     * @param array $options
     *
     * @return string
     */
    public function optionsToString(array $options)
    {
        $values = [];

        foreach ($options as $key => $value) {
            $values[] = $this->escape($key) . '=>' . $this->escape($value);
        }

        return '[' . implode(',', $values) . ']';
    }

    /**
     * Escape value for array string.
     *
     * @param $value
     *
     * @return string
     */
    public function escape($value)
    {
        if (is_numeric($value)) {
            return $value;
        }

        return "'" . str_replace("'", "\'", $value) . "'";
    }
}
