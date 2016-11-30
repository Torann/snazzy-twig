<?php

namespace Torann\SnazzyTwig;

use Twig_Loader_Chain;
use Illuminate\Support\ServiceProvider;

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
                $this->app['filesystem'],
                $this->app['website'],
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
        $this->app->singleton('twig.loader', function () {
            $filesystem = new Loaders\Filesystem(
                'layouts',
                $this->app['filesystem'],
                $this->app['website']
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
                $this->app['twig.loader'],
                $this->widgets,
                $options,
                $this->app['website'],
                $this->app['events']
            );

            // Set cache when not in debug mode
            if ($app->config->get('twig.cache', false) === true && $options['debug'] === false) {
                $twig->setCache($this->app['twig.cache']);
            }

            // Add core extensions
            $twig->addExtension(new Extensions\Security);
            $twig->addExtension(new Extensions\Core($this->app['website']));

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