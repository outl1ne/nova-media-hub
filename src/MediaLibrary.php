<?php

namespace Outl1ne\NovaMediaHub;

use Laravel\Nova\Nova;
use Laravel\Nova\Tool;
use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;

class MediaLibrary extends Tool
{
    public function boot()
    {
        Nova::script('nova-media-hub', __DIR__ . '/../dist/js/entry.js');
        Nova::style('nova-media-hub', __DIR__ . '/../dist/css/entry.css');
    }

    public function menu(Request $request)
    {
        return MenuSection::make(__('novaMediaHub.navigationItemTitle'))
            ->path(MediaLibrary::getBasePath())
            ->icon('photograph');
    }

    public static function getTableName()
    {
        return config('nova-media-hub.model');
    }

    public static function getMediaModel()
    {
        return config('nova-media-hub.model');
    }

    public static function getBasePath()
    {
        return config('nova-media-hub.base_path', 'media-hub');
    }
}
