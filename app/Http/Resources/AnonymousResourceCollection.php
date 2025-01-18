<?php

namespace App\Http\Resources;

use Illuminate\Container\Container;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection as BaseResourceCollection;

class AnonymousResourceCollection extends BaseResourceCollection
{
    public function paginationInformation($request, $paginated, $default): array
    {
        return [
            'currentPage' => $paginated['current_page'],
            'from' => $paginated['from'],
            'lastPage' => $paginated['last_page'],
            'perPage' => $paginated['per_page'],
            'to' => $paginated['to'],
            'total' => $paginated['total'],
        ];
    }

    /**
     * Get the resources in this collection
     */
    public function data($request = null)
    {
        $request = $request ?: Container::getInstance()->make('request');

        return $this->collection->map(function ($item) use ($request) {
            return $this->collects::make($item)->resource->toArray($request);
        })->all();
    }

    public function toArray($request = null)
    {
        return $this->data($request);
    }
}
