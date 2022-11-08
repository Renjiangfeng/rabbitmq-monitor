<?php

namespace Eric;

use Illuminate\Support\ServiceProvider;

class EricRabbitmqMonitorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
//        $this->mergeConfigFrom(
//            __DIR__.'/../config.demo.php', 'rabbitmq-monitor'
//        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config.demo.php' => config_path('rabbitmq-monitor.php'),
        ]);
    }
}
