<?php

namespace Torann\SnazzyTwig\Extensions\Policies;

use Twig_Markup;
use Twig_Sandbox_SecurityPolicyInterface;

use Twig_Sandbox_SecurityError;
use Twig_Sandbox_SecurityNotAllowedTagError;
use Twig_Sandbox_SecurityNotAllowedFilterError;
use Twig_Sandbox_SecurityNotAllowedFunctionError;

class SecurityPolicies implements Twig_Sandbox_SecurityPolicyInterface
{
    /**
     * Tags to block.
     *
     * @var array
     */
    protected $blockedTags = [
        'flush',
        'import',
        'use',
    ];

    /**
     * Filters to block.
     *
     * @var array
     */
    protected $blockedFilters = [
        'json_encode',
    ];

    /**
     * Methods to allow.
     *
     * @var array
     */
    protected $allowedMethods = [
        \Illuminate\Pagination\LengthAwarePaginator::class => [
            'render', 'nextPageUrl', 'hasMorePages', 'total', 'lastPage'
        ],
        \Torann\RemoteApi\Models\Collection::class => [
            'render', 'nextPageUrl', 'hasMorePages', 'total', 'lastPage'
        ],
        \Illuminate\Support\HtmlString::class => [
            '__tostring', 'toHtml',
        ],
    ];

    /**
     * Properties to allow.
     *
     * @var array
     */
    protected $allowedProperties = [];

    /**
     * Functions to block.
     *
     * @var array
     */
    protected $blockedFunctions = [
        'dump',
    ];

    /**
     * {@inheritdoc}
     */
    public function checkSecurity($tags, $filters, $functions)
    {
        foreach ($tags as $tag) {
            if (in_array($tag, $this->blockedTags)) {
                throw new Twig_Sandbox_SecurityNotAllowedTagError(sprintf('Tag "%s" is not allowed.', $tag), $tag);
            }
        }

        foreach ($filters as $filter) {
            if (in_array($filter, $this->blockedFilters)) {
                throw new Twig_Sandbox_SecurityNotAllowedFilterError(sprintf('Filter "%s" is not allowed.', $filter),
                    $filter);
            }
        }

        foreach ($functions as $function) {
            if (in_array($function, $this->blockedFunctions)) {
                throw new Twig_Sandbox_SecurityNotAllowedFunctionError(sprintf('Function "%s" is not allowed.',
                    $function), $function);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkMethodAllowed($obj, $method)
    {
        if ($obj instanceof Twig_Markup || $obj instanceof \Illuminate\Support\Carbon) {
            return true;
        }

        $allowed = false;
        $method = strtolower($method);

        foreach ($this->allowedMethods as $class => $methods) {
            if ($obj instanceof $class) {
                $allowed = in_array($method, $methods);

                break;
            }
        }

        if (!$allowed) {
            throw new Twig_Sandbox_SecurityError(sprintf('Calling "%s" method on a "%s" object is not allowed.',
                $method, get_class($obj)));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkPropertyAllowed($obj, $property)
    {
        //
    }
}
