<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Preview Mode
    |--------------------------------------------------------------------------
    |
    | When a user is editing their website, this should be set to true in a
    | middleware or some other dynamic way by the system.
    |
    */

    'preview_mode' => false,


    /*
    |--------------------------------------------------------------------------
    | Custom Twig Functions
    |--------------------------------------------------------------------------
    |
    | Use this to define custom twig functions.
    |
    | Example:
    |
    | 'asset' => [
    |     'callable' => 'asset',
    |     'options' => ['is_safe' => ['html']],
    | ]
    |
    |
    */

    'functions' => [],

    /*
    |--------------------------------------------------------------------------
    | Custom Twig Filters
    |--------------------------------------------------------------------------
    |
    | Use this to define custom twig filters.
    |
    | Example:
    |
    | 'asset' => [
    |     'callable' => 'truncate',
    |     'options' => ['is_safe' => ['html']],
    | ]
    |
    |
    */

    'filters' => [],

    /*
    |--------------------------------------------------------------------------
    | Loader
    |--------------------------------------------------------------------------
    |
    | Loaders are responsible for loading templates from a resource such as
    | the file system. Use this to set a custom loader
    |
    */

    'loader' => \Torann\SnazzyTwig\Loaders\Filesystem::class,

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Here you define class that handles the caching.
    |
    */

    'cache' => \Torann\SnazzyTwig\Cache\Filesystem::class,

    /*
    |--------------------------------------------------------------------------
    | Twig Cache Directory
    |--------------------------------------------------------------------------
    |
    | The optional caching uses Laravel's Cloud storage system.
    |
    */

    'cache_directory' => 'twig-cache',

    /*
    |--------------------------------------------------------------------------
    | Branding
    |--------------------------------------------------------------------------
    |
    | Use this to set the branding message to show at the bottom of each page.
    |
    */

    'branding' => '<a id="powered-by" href="http://lyften.com/projects/skosh" target="_blank" class="powered-by">Powered by Skosh</a>',
];
