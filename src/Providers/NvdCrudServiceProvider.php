<?php

namespace App\Generator\src\Providers;

use Illuminate\Support\ServiceProvider;
use App\Generator\src\Commands\Crud;

class NvdCrudServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->commands([Crud::class]);
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('crud.php'),
            __DIR__.'/../classic-templates' => base_path('resources/views/vendor/crud/classic-templates'),
            __DIR__.'/../single-page-templates' => base_path('resources/views/vendor/crud/single-page-templates'),
        ],'nvd');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
