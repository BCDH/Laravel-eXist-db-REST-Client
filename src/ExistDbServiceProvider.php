<?php

namespace BCDH\ExistDbRestClient;

use Illuminate\Support\ServiceProvider;

class ExistDbServiceProvider extends ServiceProvider {
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot() {
        $this->publishes([
            __DIR__ . '/../config/exist-db.php' => config_path('exist-db.php'),
        ], 'config');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register() {
        $this->mergeConfigFrom(__DIR__ . '/../config/exist-db.php', 'exist-db');
    }
}
