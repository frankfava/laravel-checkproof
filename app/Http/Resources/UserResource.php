<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class UserResource extends ModelResource
{
    public function toArray(Request $request)
    {
        $isIndex = $request->routeIs('users.index');

        return [
            ...$this->resource
                ->makeHidden([
                    'updated_at',
                    'password',
                    ...($isIndex ? ['active', 'role'] : []),
                ])
                ->toArray(),
        ];
    }
}
