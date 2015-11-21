<?php

namespace Easychimp;

use Illuminate\Support\Facades\Mail;
use Mailchimp\Mailchimp;

class ServiceProvider
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
            return new Easychimp(new Mailchimp($config['apikey']));
        });
    }
}