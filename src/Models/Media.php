<?php

namespace Outl1ne\NovaMediaHub\Models;

use Outl1ne\NovaMediaHub\MediaHub;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $casts = [
        'data' => 'array',
        'conversions' => 'array',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(MediaHub::getTableName());
    }
}
