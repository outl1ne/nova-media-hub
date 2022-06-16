<?php

namespace Outl1ne\NovaMediaHub\Nova\Fields;

use Laravel\Nova\Fields\Field;

class MediaHubField extends Field
{
    public $component = 'media-field';

    protected $collectionName = null;

    public function __construct($name, $attribute, $collectionName = null)
    {
        $this->name = $name;
        $this->attribute = $attribute;
        $this->collectionName = $collectionName;
    }
}
