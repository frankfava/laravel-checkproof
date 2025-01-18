<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Smart Resource
 * Use create method to automatically create resource or collection
 */
class ModelResource extends JsonResource
{
    public static function create($resource)
    {
        if ($resource instanceof LengthAwarePaginator || $resource instanceof Collection) {
            $collection = self::collection($resource);

            return $collection;
        }

        static::withoutWrapping();

        return static::make($resource);
    }

    public static function collection($resource)
    {
        return tap(new AnonymousResourceCollection($resource, get_called_class()), function ($collection) {
            if (property_exists(static::class, 'preserveKeys')) {
                $collection->preserveKeys = (new static([]))->preserveKeys === true;
            }
        });
    }

    public function toArray(Request $request)
    {
        return parent::toArray($request);
    }
}
