<?php

namespace Torann\SnazzyTwig;

use Twig_Loader_Chain;
use Twig_Extension_Sandbox;
use Illuminate\Support\ServiceProvider;
use Torann\SnazzyTwig\Extensions\Policies\SecurityPolicies;

class TwigServiceProvider extends ServiceProvider
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
            return new Cache\Filesystem(
                $app['filesystem'],
                $app['website'],
                $app->config->get('twig.cache_directory', 'twig-cache')
            );
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
            $filesystem = new Loaders\Filesystem(
                $app['filesystem'],
                $app['website']->getStoragePath()
            );

            return new Twig_Loader_Chain([
                $filesystem,
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
                $app['website'],
                $app['events']
            );

            // Set cache when not in debug mode
            if ($app->config->get('twig.cache', false) === true && $options['debug'] === false) {
                $twig->setCache($app['twig.cache']);
            }

            // Add security policy extension
            $twig->addExtension(new Twig_Extension_Sandbox(new SecurityPolicies, true));

            // Add core extension
            $twig->addExtension(new Extensions\Core(
                $app['website'],
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