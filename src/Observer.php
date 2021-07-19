<?php

namespace Armincms\Sitemap;

use Laravel\Nova\Nova; 

class Observer
{
    public static function observe()
    {
        collect(Nova::$resources)->each(function($resource) {
            if (! static::isUrlRoutable($resource::$model)) {
                return;
            }

            forward_static_call([$resource::$model, 'saved'], function($model) use ($resource) { 
                $map = $resource::uriKey().'-'.intval($model->getKey()/50000).'.xml'; 

                Sitemap::push(config('app.url')."/sitemaps/{$map}");
            });
        });
    }

    public static function isUrlRoutable($model)
    {
        if (method_exists($model, 'component')) {
            return true;
        }

        return false;
    }
}
