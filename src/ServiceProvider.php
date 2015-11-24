<?php

namespace Easychimp;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

    /**
     * Register paths to be published by the publish command.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/easychimp.php' => config_path('easychimp.php')
        ]);
    }
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Easychimp', function ($app) {
            $config = $app['config']['easychimp'];
            return new Easychimp($config['apikey']);
        });
    }
}