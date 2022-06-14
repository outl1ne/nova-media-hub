<?php

namespace Outl1ne\NovaMediaLibrary\Nova\Resources;

use Laravel\Nova\Resource;
use Illuminate\Http\Request;
use Outl1ne\NovaMediaLibrary\MediaLibrary;

class Media extends Resource
{
    public static $title = 'key';
    public static $model = null;
    public static $displayInNavigation = false;

    public function __construct($resource)
    {
        self::$model = MediaLibrary::getMediaModel();
        parent::__construct($resource);
    }

    public function fields(Request $request)
    {
        return [];
    }
}
