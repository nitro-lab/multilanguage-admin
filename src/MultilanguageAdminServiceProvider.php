<?php

namespace NitroLab\MultilanguageAdmin;

use Illuminate\Support\ServiceProvider;

class MultilanguageAdminServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/views', 'multilanguage-admin');
        $this->publishes([
            __DIR__.'/views' => resource_path('views/vendor/multilanguage-admin'),
        ]);
    }

    public function register()
    {

    }
}
