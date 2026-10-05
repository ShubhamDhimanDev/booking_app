<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Country;
use Illuminate\Http\Request;

trait ResolvesStoreCountry
{
    /** The store is visible when the country is active and has e-commerce switched on. Admins may preview with ?preview=1. */
    protected function storeCountry(Request $request, Country $country): Country
    {
        $preview = $request->boolean('preview') && $request->user() && $request->user()->hasAnyRole(['admin', 'owner']);

        abort_unless($preview || ($country->is_active && $country->ecommerce_enabled), 404);

        return $country;
    }
}
