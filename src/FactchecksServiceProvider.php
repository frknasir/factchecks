<?php

namespace StarfolkSoftware\Factchecks;

use Illuminate\Support\ServiceProvider;

class FactchecksServiceProvider extends ServiceProvider
{
  /**
   * Bootstrap the application services.
   */
  public function boot()
  {
    if ($this->app->runningInConsole()) {
      $this->publishes([
        __DIR__.'/../config/config.php' => config_path('factchecks.php'),
      ], 'config');


      if (! class_exists('CreateFactchecksTable')) {
        $this->publishes([
          __DIR__.'/../database/migrations/create_factchecks_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_factchecks_table.php'),
        ], 'migrations');
      }
    }
  }

  /**
   * Register the application services.
   */
  public function register()
  {
    $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'factchecks');
  }
}
