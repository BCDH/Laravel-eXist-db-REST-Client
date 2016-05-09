<?php

namespace BCDH\ExistDbRestClient;

use Illuminate\Support\ServiceProvider;

class ExistDbServiceProvider extends ServiceProvider {
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot() {
        $this->setupConfig();
    }

    private function setupConfig() {
        $configPath = __DIR__ . '/../config/exist-db.php';
        $this->mergeConfigFrom($configPath, 'exist-db');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register() {
        $this->registerResources();
    }

    public function registerResources() {
        $this->publishes(array(
            __DIR__ . '/../config/exist-db.php' => config_path('exist-db.php'),
        ));
    }
}