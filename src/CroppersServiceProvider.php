<?php

namespace Encore\Croppers;

use Encore\Admin\Admin;
use Encore\Admin\Form;
use Illuminate\Support\ServiceProvider;

class CroppersServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot(Croppers $extension)
    {
        if (! Croppers::boot()) {
            return ;
        }

        if ($views = $extension->views()) {
            $this->loadViewsFrom($views, 'laravel-admin-croppers');
        }

        if ($this->app->runningInConsole() && $assets = $extension->assets()) {
            $this->publishes(
                [$assets => public_path('vendor/laravel-admin-ext/croppers')],
                'laravel-admin-croppers'
            );
            $this->publishes([__DIR__.'/../resources/lang' => resource_path('lang')], 'laravel-admin-croppers-lang');
        }

        Admin::booting(function () {
            Form::extend('croppers', Crops::class);
        });
    }
}