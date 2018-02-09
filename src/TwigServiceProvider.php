<?php

namespace Torann\SnazzyTwig;

use Twig_Loader_Chain;
use Twig_Extension_Sandbox;
use InvalidArgumentException;
use Illuminate\Support\ServiceProvider;
use Torann\SnazzyTwig\Extensions\Policies\SecurityPolicies;

abstract class TwigServiceProvider extends ServiceProvider
{
    /**
     * Twig view widgets.
     *
     * @var array
     */
    protected $widgets = [];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerCacheSystem();
        $this->registerLoaders();
        $this->registerEngine();
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/twig.php', 'twig'
        );

        if ($this->isLumen() === false) {
            $this->publishes([
                __DIR__ . '/../config/twig.php' => config_path('twig.php')
            ], 'config');
        }
    }

    /**
     * Register Twig cache system bindings.
     *
     * @return void
     */
    protected function registerCacheSystem()
    {
        $this->app->singleton('twig.cache', function ($app) {

            // Get class from the config
            $class = $app->config->get('twig.cache');

            if (class_exists($class) === false) {
                throw new InvalidArgumentException("Twig cache [{$class}] is not defined.");
            }

            // Create cache instance
            $loader = $app->make($class, [
                'directory' => $app->config->get('twig.cache_directory', 'twig-cache'),
            ]);

            // Set path and return
            return $loader->setWebsite($this->getWebsite());
        });
    }

    /**
     * Register Twig loader bindings.
     *
     * @return void
     */
    protected function registerLoaders()
    {
        $this->app->singleton('twig.loader', function ($app) {

            // Get class from the config
            $class = $app->config->get('twig.loader');

            if (class_exists($class) === false) {
                throw new InvalidArgumentException("Twig loader [{$class}] is not defined.");
            }

            // Create loader instance
            $loader = $app->make($class);

            // Set path and return
            $loader->setPath($this->getWebsite()->getStoragePath());

            return new Twig_Loader_Chain([
                $loader,
            ]);
        });
    }

    /**
     * Register Twig engine bindings.
     *
     * @return void
     */
    protected function registerEngine()
    {
        $this->app->singleton('twig', function ($app) {
            $options = [
                'debug' => env('APP_DEBUG', false),
                'base_template_class' => Template::class,
                'autoescape' => false,
            ];

            $twig = new Environment(
                $app['twig.loader'],
                $this->widgets,
                $options,
                $this->getWebsite(),
                $app['events']
            );

            // Set cache when not in debug mode
            if ($app->config->get('twig.cache') && $options['debug'] === false) {
                $twig->setCache($app['twig.cache']);
            }

            // Add security policy extension
            $twig->addExtension(new Twig_Extension_Sandbox(new SecurityPolicies, true));

            // Add core extension
            $twig->addExtension(new Extensions\Core(
                $this->getWebsite(),
                $app->config->get('twig')
            ));

            return $twig;
        });
    }

    /**
     * Check if package is running under Lumen app
     *
     * @return bool
     */
    protected function isLumen()
    {
        return str_contains($this->app->version(), 'Lumen') === true;
    }

    /**
     * Get the current website.
     *
     * @return \Torann\SnazzyTwig\Contracts\WebsiteInterface
     */
    abstract protected function getWebsite();

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'twig.loader',
            'twig.cache',
            'twig',
        ];
    }
}