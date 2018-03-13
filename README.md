# Snazzy Twig

[![Latest Stable Version](https://poser.pugx.org/torann/snazzy-twig/v/stable.png)](https://packagist.org/packages/torann/snazzy-twig) [![Total Downloads](https://poser.pugx.org/torann/snazzy-twig/downloads.png)](https://packagist.org/packages/torann/snazzy-twig)

Laravel implantation of [Skosh](https://github.com/Torann/skosh)'s Twig templating engine for use in a multi-tenant environment.

- [Snazzy Twig on Packagist](https://packagist.org/packages/torann/snazzy-twig)
- [Snazzy Twig on GitHub](https://github.com/torann/snazzy-twig)

## Installation

### Composer

From the command line run:

```
$ composer require torann/snazzy-twig
```

### The Service Provider

You will need to extend the built in service provider so that you can add your custom widgets and get the website instance. To do this create a service provider named `TwigServiceProvider` in the `\app\Providers` directory and extend the Snazzy Twig provider like below:

```php
<?php

namespace App\Providers;

use Torann\SnazzyTwig\TwigServiceProvider as ServiceProvider;

class TwigServiceProvider extends ServiceProvider
{
    /**
     * Twig view widgets.
     *
     * @var array
     */
    protected $widgets = [
        //
    ];
    
    /**
     * Get the current website.
     *
     * @return \Torann\SnazzyTwig\Contracts\WebsiteInterface
     */
    protected function getWebsite()
    {
        return $this->app['website'];
    }
}
```

> **Note:** the `getWebsite()` method is needed to get the website model to use for generating the views

Once this is done you need to register the new service provider with the application.

#### Laravel

Open up `config/app.php` and find the `providers` key.

``` php
'providers' => [

    \App\Providers\TwigServiceProvider::class,

]
```

#### Lumen

For Lumen register the service provider in `bootstrap/app.php`.

``` php
$app->register(\App\Providers\TwigServiceProvider::class);
```