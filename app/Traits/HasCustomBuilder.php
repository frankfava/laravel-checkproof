<?php

namespace App\Traits;

use App\Builders\EloquentBuilder;

/**
 * @method static EloquentBuilder newModelQuery()
 * @method static EloquentBuilder newQuery()
 * @method static EloquentBuilder query()
 */
trait HasCustomBuilder
{
    /** Custom Builder */
    public function newEloquentBuilder($query): EloquentBuilder
    {
        return new EloquentBuilder($query);
    }
}
