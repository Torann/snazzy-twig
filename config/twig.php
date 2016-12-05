<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Resource Path
    |--------------------------------------------------------------------------
    |
    | Use this to define the storage path of a website's resources.
    |
    */

    'resource_path' => 'websites/{id}',

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
    | Twig Caching
    |--------------------------------------------------------------------------
    |
    | Here you define the caching options for twig.
    |
    | NOTE: This is experimental
    |
    */

    'cache' => false,

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
