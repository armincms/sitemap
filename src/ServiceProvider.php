<?php

namespace Armincms\Sitemap;

use Illuminate\Console\Events\ArtisanStarting;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;  
use Laravel\Nova\Nova; 

class ServiceProvider extends LaravelServiceProvider 
{   
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'sitemap');

        $this->app->booted(function() {
            Event::listen(ArtisanStarting::class, [Observer::class, 'observe']);
            Nova::serving([Observer::class, 'observe']);  
        });

        \Route::get('sitemaps/{group}', Http\Controllers\SitemapController::class.'@handle')
            ->name('sitemap'); 
    } 
}
