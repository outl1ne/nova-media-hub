<?php

namespace Outl1ne\NovaMediaLibrary;

use Laravel\Nova\Nova;
use Laravel\Nova\Tool;
use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;

class MediaLibrary extends Tool
{
    public function boot()
    {
        Nova::script('nova-medialibrary', __DIR__ . '/../dist/js/entry.js');
        Nova::style('nova-medialibrary', __DIR__ . '/../dist/css/entry.css');
    }

    public function menu(Request $request)
    {
        return MenuSection::make(__('novaMediaLibrary.navigationItemTitle'))
            ->path(MediaLibrary::getBasePath())
            ->icon('photograph');
    }

    public static function getMediaModel()
    {
        return config('laravel-medialibrary.model');
    }

    public static function getBasePath()
    {
        return config('nova-medialibrary.base_path', 'media-library');
    }
}
