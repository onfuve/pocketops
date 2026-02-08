<?php

namespace App\Http\Controllers;

use App\Models\PriceList;

class CustomerPriceListController extends Controller
{
    /**
     * Public price list view by code (no auth).
     */
    public function show(string $code)
    {
        $priceList = PriceList::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->with(['sections.items.product'])
            ->firstOrFail();

        return view('price-lists.public', compact('priceList'));
    }
}
