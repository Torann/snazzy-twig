<?php

namespace Torann\SnazzyTwig;

use App\Website;
use Twig_Environment;
use Twig_LoaderInterface;
use Symfony\Component\Yaml\Yaml;
use Illuminate\View\ViewFinderInterface;
use Illuminate\Contracts\Events\Dispatcher;

class Environment extends Twig_Environment
{
    /**
     * Website instance.
     *
     * @var \App\Website
     */
    protected $website;

    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * File extensions to ignore the template.
     *
     * @var array
     */
    protected $ignoreTemplate = [
        'xml'
    ];

    /**
     * Create a new Twig environment instance.
     *
     * @param Twig_LoaderInterface $loader
     * @param array                $widgets
     * @param array                $options
     * @param Website              $website
     * @param Dispatcher           $events
     */
    public function __construct(
        Twig_LoaderInterface $loader,
        $widgets = [],
        $options = [],
        Website $website,
        Dispatcher $events
    ) {
        parent::__construct($loader, $options);

        $this->website = $website;
        $this->events = $events;

        // Register view widgets
        $this->widgets($widgets);
    }

    /**
     * Add a piece of shared data to the environment.
     *
     * @param  array|string $key
     * @param  mixed        $value
     * @return mixed
     */
    public function share($key, $value = null)
    {
        if (!is_array($key)) {
            return $this->addGlobal($key, $value);
        }

        foreach ($key as $innerKey => $innerValue) {
            $this->addGlobal($innerKey, $innerValue);
        }
    }

    /**
     * Register multiple view widgets via an array.
     *
     * @param  array  $widgets
     * @return array
     */
    public function widgets(array $widgets)
    {
        $registered = [];

        foreach ($widgets as $callback => $view) {
            $registered[] = $this->addViewEvent('widgets.twig_doodad_' . $view, $callback, 'composing: ');
        }

        return $registered;
    }

    /**
     * Renders a template from source.
     *
     * @param  object|string $source
     * @param  array         $context
     * @return string
     */
    public function make($source, array $context = [])
    {
        if (is_object($source)) {
            $source = $this->getSource($source, $context);
        }
        else {
            if (!is_string($source)) {
                abort(404);
            }
        }

        // Set page meta data
        $context = $this->setMetadata($context);

        // Render template
        $template = $this->createTemplate($source)->render($context);

        // Insert CSRF token meta tag
        $template = str_replace('</head>', '<meta name="csrf-token" content="{{csrf_token}}"></head>', $template);

        return $template;
    }

    /**
     * Set meta data from context.
     *
     * This helps remove all of the crazy conditional
     * crap in the template. Makes it easier on the
     * less tech savvy users out there.
     *
     * @param  array $context
     * @return array
     */
    public function setMetadata(array $context = [])
    {
        // Get page
        $page = array_get($context, 'page');

        // Set page title
        $context['page_title'] = $page->is_home
            ? $this->website->title
            : "{$page->title} - " . ($page->parent ? "{$page->parent->title} - " : '') . $this->website->title;

        // Set page description
        $context['page_description'] = $page->excerpt ?: htmlentities($this->website->description);

        return $context;
    }

    /**
     * {@inheritdoc}
     */
    public function loadTemplate($name, $index = null)
    {
        $template = parent::loadTemplate($name, $index);

        $template->setName($this->normalizeName($name));

        return $template;
    }

    /**
     * Merges a context with the shared variables, same as mergeGlobals()
     *
     * @param  array $context
     * @return array
     */
    public function mergeShared(array $context)
    {
        // We don't use array_merge as the context being generally
        // bigger than globals, this code is faster.
        foreach ($this->shared as $key => $value) {
            if (!array_key_exists($key, $context)) {
                $context[$key] = $value;
            }
        }

        return $context;
    }

    /**
     * Get source from file.
     *
     * @param  object $model
     * @return string
     */
    protected function getSource($model)
    {
        // All pages extend the primary layout
        $source = "{% extends \"{$model->template}.twig\" %}";

        // Special features for layout extending
        if ($model->template !== 'layout') {
            $source .= "{% block content %}";
            $source .= $model->body;
            $source .= "{% endblock %}";
        }
        else {
            $source .= "{% block layout %}";
            $source .= $model->body;
            $source .= "{% endblock %}";
        }

        return $source;
    }

    /**
     * Create a cache key from model
     *
     * @param  object $model
     * @return string
     */
    protected function getCacheKey($model)
    {
        return $this->website->getCacheKey() . '.' . $model->getTable() . '.' . $model->getKey();
    }

    /**
     * Create a cache tags from model
     *
     * @param  object $model
     * @return string
     */
    protected function getCacheTags($model)
    {
        $key = $this->website->getCacheKey();

        return [$key, $key . '.' . $model->getTable()];
    }

    /**
     * Parse metadata and content
     *
     * @param  string $source
     * @return array
     */
    protected function parseSource($source)
    {
        // Remove Byte Order Mark (BOM)
        $data = preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $source);

        // Pattern for detecting a metadata separator (---)
        // Using ^ and $ in this way requires the PCRE_MULTILINE modifier
        $pattern = '/' // Pattern start
            . '^'       // Beginning of line
            . '---'     // Literal ---
            . '\\s*'    // Zero or more whitespace characters
            . '$'       // End of line
            . '/m';     // Pattern end, PCRE_MULTILINE modifier

        // Separate the meta-data from the content
        $data = trim($data);

        if (
            (substr($data, 0, 3) === '---') &&
            (preg_match($pattern, $data, $matches, PREG_OFFSET_CAPTURE, 3))
        ) {
            $pos = $matches[0][1];
            $len = strlen($matches[0][0]);

            $meta = Yaml::parse(trim(substr($data, 3, $pos - 3)));
            $content = trim(substr($data, $pos + $len));
        }
        else {
            $content = $data;
            $meta = [];
        }

        return [$content, $meta];
    }

    /**
     * Normalize a view name.
     *
     * @param  string $name
     * @return string
     */
    protected function normalizeName($name)
    {
        // Get name without extension
        if (substr($name, -5, 5) === '.twig') {
            $name = substr($name, 0, -5);
        }

        // Normalize namespace and delimiters
        $delimiter = ViewFinderInterface::HINT_PATH_DELIMITER;
        if (strpos($name, $delimiter) === false) {
            return str_replace('/', '.', $name);
        }

        list($namespace, $name) = explode($delimiter, $name);

        return $namespace . $delimiter . str_replace('/', '.', $name);
    }

    /**
     * Add an event for a given view.
     *
     * @param  string   $view
     * @param  string   $class
     * @param  string   $prefix
     * @return \Closure
     */
    protected function addViewEvent($view, $class, $prefix = 'composing: ')
    {
        $name = $prefix . $this->normalizeName($view);

        // When registering a class based view "composer", we will simply resolve the
        // classes from the application IoC container then call the compose method
        // on the instance. This allows for convenient, testable view composers.
        $callback = $this->buildClassEventCallback($class, $prefix);

        $this->events->listen($name, $callback);

        return $callback;
    }

    /**
     * Fire an event and call the listeners.
     *
     * @param  string $name
     * @param  mixed  $payload
     */
    public function fire($name, $payload = [])
    {
        $this->events->fire($name, $payload);
    }

    /**
     * Build a class based container callback Closure.
     *
     * @param  string $class
     * @param  string $prefix
     * @return \Closure
     */
    protected function buildClassEventCallback($class, $prefix)
    {
        list($class, $method) = $this->parseClassEvent($class, $prefix);

        // Once we have the class and method name, we can build the Closure to resolve
        // the instance out of the IoC container and call the method on it with the
        // given arguments that are passed to the Closure as the composer's data.
        return function () use ($class, $method) {
            $callable = [app($class), $method];

            return call_user_func_array($callable, func_get_args());
        };
    }

    /**
     * Parse a class based composer name.
     *
     * @param  string $class
     * @param  string $prefix
     * @return array
     */
    protected function parseClassEvent($class, $prefix)
    {
        if (str_contains($class, '@')) {
            return explode('@', $class);
        }

        $method = str_contains($prefix, 'composing') ? 'compose' : 'create';

        return [$class, $method];
    }
}