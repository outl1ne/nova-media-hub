<?php

namespace Outl1ne\NovaMediaLibrary\Nova\Fields;

use Laravel\Nova\Fields\Field;

class MediaField extends Field
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
