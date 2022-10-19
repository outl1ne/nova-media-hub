<?php

namespace Outl1ne\NovaMediaHub\Nova\Fields;

use Exception;
use Illuminate\Support\Arr;
use Laravel\Nova\Fields\Field;
use Outl1ne\NovaMediaHub\MediaHub;
use Laravel\Nova\Http\Requests\NovaRequest;

class MediaHubField extends Field
{
    public $component = 'media-hub-field';

    protected $defaultCollectionName = null;

    public function __construct($name, $attribute = null, $defaultCollectionName = null)
    {
        parent::__construct($name, $attribute, null);

        $this->withMeta([
            'multiple' => false,
            'user_can_create_collections' => MediaHub::userCanCreateCollections(),
            'defaultCollectionName' => $defaultCollectionName,
        ]);

        // TODO Add index field
        $this->hideFromIndex();
    }

    public function defaultCollection($defaultCollectionName = null)
    {
        return $this->withMeta(['defaultCollectionName' => $defaultCollectionName]);
    }

    public function multiple($multiple = true)
    {
        return $this->withMeta(['multiple' => $multiple]);
    }

    public function jsonSerialize(): array
    {
        $jsonSerialized = parent::jsonSerialize();
        $jsonSerialized['media'] = [];

        $value = $jsonSerialized['value'] ?? $jsonSerialized['displayedAs'] ?? null;

        // Maybe user hasn't set the cast to array, try to JSON parse it manually
        try {
            if (!is_array($value)) {
                $value = json_decode($value, true);
                if (is_array($value)) $jsonSerialized['value'] = $value;
            }
        } catch (Exception $e) {
            report($e);
        }

        // If is nova-translatable, resolve all medias manually without overriding $json['value']
        if ($jsonSerialized['translatable'] ?? false) {
            $value = Arr::flatten(array_values($jsonSerialized['translatable']['value'] ?? []), 1);
        }

        if (is_array($value)) {
            $jsonSerialized['media'] = MediaHub::getMediaModel()::findMany($value)->keyBy('id')->map->formatForNova()->toArray();
        } else if (!empty($value)) {
            $jsonSerialized['media'][$value] = MediaHub::getMediaModel()::find($value)?->formatForNova();
        }

        return $jsonSerialized;
    }

    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        if ($request->exists($requestAttribute)) {
            $value = $request[$requestAttribute];
            $model->{$attribute} = $this->isNullValue($value) ? null : $value;
        }
    }
}
