<?php

namespace Torann\SnazzyTwig\Extensions;

use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;
use Illuminate\Support\Arr;
use Torann\SnazzyTwig\Contracts\WebsiteInterface;

class Core extends Twig_Extension
{
    /**
     * Website instance.
     *
     * @var WebsiteInterface
     */
    protected $website;

    /**
     * Branding message.
     *
     * @var string
     */
    private $branding = '';

    /**
     * Custom functions array.
     *
     * @var array
     */
    private $customFunctions = [];

    /**
     * Custom filters array.
     *
     * @var array
     */
    private $customFilters = [];

    /**
     * Create a new instance of AbstractExtension.
     *
     * @param WebsiteInterface $website
     * @param array            $config
     */
    public function __construct(WebsiteInterface $website, array $config = [])
    {
        $this->website = $website;

        $this->customFunctions = Arr::get($config, 'functions', []);
        $this->customFilters = Arr::get($config, 'filters', []);
        $this->branding = Arr::get($config, 'branding', '');
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
            new TokenParsers\Contact(),
        ];
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array
     */
    public function getFunctions()
    {
        return $this->mergeCustomFunctions([
            new Twig_SimpleFunction('powered_by', [$this, 'functionGetPoweredBy'], ['is_safe' => ['twig', 'html']]),
            new Twig_SimpleFunction('url', [$this, 'functionGetUrl']),
            new Twig_SimpleFunction('csrf_token', [$this, 'functionGetCsrfToken']),
        ]);
    }

    /**
     * Merge custom functions into existing list.
     *
     * @param array $functions
     *
     * @return array
     */
    protected function mergeCustomFunctions($functions)
    {
        foreach ($this->customFunctions as $name => $fn) {
            if (is_string($fn)) {
                $functions[$name] = new Twig_SimpleFunction($name, $fn);
            }
            else {
                $functions[$name] = new Twig_SimpleFunction($name, $fn['callable'], Arr::get($fn, 'options', []));
            }
        }

        return $functions;
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->mergeCustomFilters([
            new Twig_SimpleFilter('truncate', [$this, 'filterTruncate'], ['is_safe' => ['twig', 'html']]),
            new Twig_SimpleFilter('paragraph', [$this, 'filterRemoveParagraphs'], ['is_safe' => ['twig', 'html']]),
        ]);
    }

    /**
     * Merge custom functions into existing list.
     *
     * @param array $filters
     *
     * @return array
     */
    protected function mergeCustomFilters($filters)
    {
        foreach ($this->customFilters as $name => $fn) {
            if (is_string($fn)) {
                $filters[$name] = new Twig_SimpleFilter($name, $fn);
            }
            else {
                $filters[$name] = new Twig_SimpleFilter($name, $fn['callable'], Arr::get($fn, 'options', []));
            }
        }

        return $filters;
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
     * Display branding tag.
     *
     * @return string
     */
    public function functionGetPoweredBy()
    {
        return $this->branding;
    }

    /**
     * Generate a url for the site.
     *
     * @param string $path
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
     * @param string $string
     * @param int    $limit
     * @param string $pad
     *
     * @return mixed
     */
    public function filterTruncate($string, $limit, $pad = '&hellip;')
    {
        // return with no change if string is shorter than $limit
        if (strlen($string) <= $limit) return $string;

        // is $break present between $limit and the end of the string?
        if (false !== ($breakpoint = strpos($string, ' ', $limit))) {
            if ($breakpoint < strlen($string) - 1) {
                $string = substr($string, 0, $breakpoint) . $pad;
            }
        }

        return $string;
    }

    /**
     * Remove wrapping paragraphs from string.
     *
     * @param string $text
     *
     * @return string
     */
    public function filterRemoveParagraphs($text)
    {
        return preg_replace('~<p>(.*?)</p>~is', '$1', $text);
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
}
