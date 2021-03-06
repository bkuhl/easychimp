<?php

namespace Easychimp;

use Mailchimp\Mailchimp;

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
        $this->mergeConfigFrom(
            __DIR__.'/config/easychimp.php', 'easychimp'
        );

        $this->app->bind(Easychimp::class, function ($app) {
            $config = $app['config']['easychimp'];
            return new Easychimp(new Mailchimp($config['apikey']));
        });
    }
}