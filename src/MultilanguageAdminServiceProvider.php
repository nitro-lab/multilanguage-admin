<?php

namespace NitroLab\MultilanguageAdmin;

use NitroLab\MultilanguageAdmin\Extensions\LangTabAll;
use NitroLab\MultilanguageAdmin\Form\HasMany;
use Encore\Admin\Admin;
use Illuminate\Support\ServiceProvider;

class MultilanguageAdminServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/views', 'multilanguage-admin');
        $this->publishes([
            __DIR__.'/views' => resource_path('views/vendor/multilanguage-admin'),
        ]);

        Admin::booting(function () {
            Form::forget(['hasMany']);
            Form::extend('langtaball', LangTabAll::class);
            Form::extend('hasMany', HasMany::class);
        });
    }

    public function register()
    {

    }
}
