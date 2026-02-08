<?php

namespace App\Http\Controllers;

use App\Models\ProductLandingPage;

class CustomerProductController extends Controller
{
    public function show(string $code)
    {
        $page = ProductLandingPage::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->with('product')
            ->firstOrFail();

        return view('product-landing-pages.public', compact('page'));
    }
}
