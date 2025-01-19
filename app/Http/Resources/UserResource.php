<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class UserResource extends ModelResource
{
    public function toArray(Request $request)
    {
        return [
            ...$this->resource
                ->makeHidden(['updated_at', 'password', 'active'])
                ->toArray(),
        ];
    }
}
