<?php

namespace App\Http\Resources;

use Illuminate\Container\Container;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection as BaseResourceCollection;

class AnonymousResourceCollection extends BaseResourceCollection
{
    public function paginationInformation($request, $paginated, $original): array
    {
        $updated = [
            'currentPage' => $paginated['current_page'],
            'from' => $paginated['from'],
            'lastPage' => $paginated['last_page'],
            'perPage' => $paginated['per_page'],
            'to' => $paginated['to'],
            'total' => $paginated['total'],
        ];

        if (method_exists($this->collects, 'customPagination')) {
            return call_user_func([$this->collects, 'customPagination'], $request, $paginated, $original, $updated);
        }

        return $updated;
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
