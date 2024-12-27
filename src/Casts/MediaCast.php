<?php

namespace Outl1ne\NovaMediaHub\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class MediaCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
    {
        if (is_null($value)) return;

        $mediaModel = config('nova-media-hub.model');

        if (is_numeric($value)) return $mediaModel::find($value);

        $ids = json_decode($value, true);
        $order = implode(',', $ids);
        return $mediaModel::whereIn('id', $ids)
            ->orderByRaw("FIELD(id, $order)")
            ->get();
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (is_null($value)) return;
        if (is_array($value)) return json_encode($value);
        return $value;
    }
}
