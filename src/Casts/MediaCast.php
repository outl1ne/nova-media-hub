<?php

namespace Outl1ne\NovaMediaHub\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Outl1ne\NovaMediaHub\Models\Media;

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

        if (is_null($value)) {
            return;
        }

        $ids = json_decode($value, true);

        if (is_array($ids)) {
            return Media::whereIn('id', $ids)->get();
        } else {
            return Media::where('id', $value)->get();
        }
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
