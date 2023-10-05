<?php

namespace App\Traits;

use Arr;
use Illuminate\Http\Request;

trait HasApiPagination
{
    /**
     * @param  array<array-key, mixed>  $paginated
     * @param  array{links: array<string, ?string>, meta: array<array-key, mixed>}  $default
     * @return array{links: array<string, ?string>, meta: array<string, int>}
     */
    public function paginationInformation(Request $request, array $paginated, array $default): array
    {
        Arr::forget($default, [
            'meta.to',
            'meta.from',
            'meta.links',
            'meta.path',
        ]);

        return $default;
    }
}
