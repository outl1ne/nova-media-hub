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
    }

    public function menu(Request $request)
    {
        return MenuSection::make(__('novaMediaLibrary.navigationItemTitle'))
            ->icon('media')
            ->collapsable();
    }

    public static function getMediaModel()
    {
        return config();
    }
}
