<?php

namespace Torann\SnazzyTwig\Extensions;

use Closure;
use App\Website;
use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;
use Illuminate\Cache\CacheManager;

class Core extends Twig_Extension
{
    /**
     * Website instance.
     *
     * @var \App\Website
     */
    protected $website;

    /**
     * Cache manager instance.
     *
     * @var \Illuminate\Cache\CacheManager
     */
    protected $cacheManager;

    /**
     * Site root path.
     *
     * @var string
     */
    private $rootPath;

    /**
     * Current URL path.
     *
     * @var string
     */
    private $currentPath;

    /**
     * Local cache for repeated calls.
     *
     * @var array
     */
    private $cache = [];

    /**
     * Create a new instance of AbstractExtension.
     *
     * @param Website      $website
     * @param CacheManager $cache
     */
    public function __construct(Website $website, CacheManager $cache)
    {
        $this->website = $website;
        $this->cacheManager = $cache;
        $this->rootPath = trim(url('/'), '/');
        $this->currentPath = app('request')->getPathInfo();
    }

    /**
     * Returns the token parser instances to add to the existing list.
     *
     * @return array
     */
    public function getTokenParsers()
    {
        return [
            new TokenParsers\Widget(),
        ];
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('isCurrent', [$this, 'functionIsCurrent']),
            new Twig_SimpleFunction('powered_by', [$this, 'functionGetPoweredBy'], ['is_safe' => ['twig', 'html']]),
            new Twig_SimpleFunction('list_pages', [$this, 'functionGetPageList'], ['is_safe' => ['twig', 'html']]),
            new Twig_SimpleFunction('url', [$this, 'functionGetUrl']),
            new Twig_SimpleFunction('csrf_token', [$this, 'functionGetCsrfToken']),
        ];
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array
     */
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('truncate', [$this, 'filterTruncate'], ['is_safe' => ['twig', 'html']]),
            new Twig_SimpleFilter('paragraph', [$this, 'filterRemoveParagraphs'], ['is_safe' => ['twig', 'html']]),
        ];
    }

    /**
     * Get the CSRF token input.
     *
     * @return string
     */
    public function functionGetCsrfToken()
    {
        return '<input type="hidden" name="_token" value="{{csrf_token}}">';
    }

    /**
     * Is given URL the current page?
     *
     * @return array
     */
    public function functionGetPageList($options = null)
    {
        // Get content
        $content = $this->getCache('functionGetPageList', function () {
            return $this->website->getRootPages();
        });

        if ($options === null) {
            return count($content) > 0;
        }

        $options = array_merge([
            'active_class' => 'current-page',
            'menu_class' => 'list-pages cf',
        ], $options);

        $html = [
            "<ul id=\"pages-nav\" class=\"{$options['menu_class']}\">"
        ];

        foreach ($content as $item) {
            // Skip the home page
            if ($item->is_home || $item->is_hidden) {
                continue;
            }

            // Get trimmed Permalink
            $url = '/' . trim(str_replace($this->rootPath, '', $item->permalink), '/');

            // Create a pattern
            $pattern = ($url === '/') ? '/' : "{$url}*";

            // Get active class
            $class = str_is($pattern, $this->currentPath) ? $options['active_class'] : '';

            $html[] = "<li class=\"{$class}\" role=\"presentation\"><a href=\"{$item->permalink}\" role=\"button\">{$item->title}</a></li>";
        }

        $html[] = "</ul>";

        return implode('', $html);
    }

    /**
     * Display branding tag.
     *
     * @return string
     */
    public function functionGetPoweredBy()
    {
        return '<a id="powered-by" href="#" target="_blank" class="powered-by">Powered by Skosh</a>';
    }

    /**
     * Is given URL the current page?
     *
     * @param  string $url
     * @param  string $pattern
     *
     * @return bool
     */
    public function functionIsCurrent($url, $pattern)
    {
        return str_is($pattern, '/' . str_replace($this->rootPath, '', $url));
    }

    /**
     * Generate a url for the site.
     *
     * @param  string $path
     *
     * @return string
     */
    public function functionGetUrl($path)
    {
        return url($path);
    }

    /**
     * Truncate text to given length.
     *
     * @param  string $string
     * @param  int    $width
     * @param  string $pad
     *
     * @return mixed
     */
    public function filterTruncate($string, $width, $pad = '&hellip;')
    {
        return truncate($string, $width, $pad);
    }

    /**
     * Remove wrapping paragraphs from string.
     *
     * @param  string $text
     *
     * @return string
     */
    public function filterRemoveParagraphs($text)
    {
        return preg_replace('~<p>(.*?)</p>~is', '$1', $text);
    }

    /**
     * Get an item from the cache manager, or store the default value.
     *
     * @param  string   $key
     * @param  \Closure $callback
     *
     * @return mixed
     */
    public function getCache($key, Closure $callback)
    {
        // Skip cache for development
        if (config('app.debug')) {
            return $this->getLocalCache($key, $callback);
        }

        $key = $this->website->id . '-' . $key;
        $tags = $this->getCacheTags('pages');

        return $this->cacheManager->tags($tags)->rememberForever($key, $callback);
    }

    /**
     * Get an item from local cache, or store the default value.
     *
     * @param  string   $key
     * @param  \Closure $callback
     *
     * @return mixed
     */
    public function getLocalCache($key, Closure $callback)
    {
        // If the item exists in the cache we will just return this immediately
        // otherwise we will execute the given Closure and cache the result.
        if (!is_null($value = array_get($this->cache, $key))) {
            return $value;
        }

        $callback->bindTo($this);

        return $this->cache[$key] = $callback();
    }

    /**
     * Returns the name of the extension.
     *
     * @return string
     */
    public function getName()
    {
        return 'base_extensions';
    }

    /**
     * Create a cache tags
     *
     * @param  string $name
     *
     * @return string
     */
    protected function getCacheTags($name)
    {
        $key = $this->website->getCacheKey();

        return [$key, "{$key}.{$name}"];
    }
}
