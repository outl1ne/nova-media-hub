<?php

namespace Outl1ne\NovaMediaHub;

use Laravel\Nova\Nova;
use Laravel\Nova\Tool;
use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;

class MediaHub extends Tool
{
    public function boot()
    {
        Nova::script('nova-media-hub', __DIR__ . '/../dist/js/entry.js');
        Nova::style('nova-media-hub', __DIR__ . '/../dist/css/entry.css');
    }

    public function menu(Request $request)
    {
        return MenuSection::make(__('novaMediaHub.navigationItemTitle'))
            ->path(self::getBasePath())
            ->icon('photograph');
    }



    // ------------------------------
    // Getters
    // ------------------------------

    public static function getTableName()
    {
        return config('nova-media-hub.table_name');
    }

    public static function getMediaModel()
    {
        return config('nova-media-hub.model');
    }

    public static function getBasePath()
    {
        return config('nova-media-hub.base_path');
    }
}
