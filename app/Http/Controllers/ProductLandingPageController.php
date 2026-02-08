<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductLandingPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductLandingPageController extends Controller
{
    public function index(Request $request)
    {
        $pages = ProductLandingPage::query()
            ->with('product')
            ->visibleToUser($request->user())
            ->orderBy('updated_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('product-landing-pages.index', compact('pages'));
    }

    public function create()
    {
        $products = Product::query()
            ->visibleToUser(request()->user())
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'default_unit_price']);

        return view('product-landing-pages.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->merge(['price' => $request->filled('price') ? $request->input('price') : null]);
        $validated = $request->validate($this->rules());
        $validated['user_id'] = $request->user()->id;
        $validated['show_price'] = $request->boolean('show_price');
        $validated['show_notes'] = $request->boolean('show_notes');
        $validated['show_social'] = $request->boolean('show_social');
        $validated['show_address'] = $request->boolean('show_address');
        $validated['show_contact'] = $request->boolean('show_contact');
        $validated['show_share_buttons'] = $request->boolean('show_share_buttons');
        $validated['is_active'] = $request->boolean('is_active');

        $page = ProductLandingPage::create($validated);
        $this->handlePhotoUploads($request, $page);

        return redirect()->route('product-landing-pages.edit', $page)
            ->with('success', 'صفحه فرود ایجاد شد. کد منحصربه‌فرد تولید کنید.');
    }

    public function edit(ProductLandingPage $productLandingPage)
    {
        abort_unless($productLandingPage->isVisibleTo(request()->user()), 403);
        $productLandingPage->load('product');
        $products = Product::query()
            ->visibleToUser(request()->user())
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'default_unit_price']);

        return view('product-landing-pages.edit', compact('productLandingPage', 'products'));
    }

    public function update(Request $request, ProductLandingPage $productLandingPage)
    {
        abort_unless($productLandingPage->isVisibleTo(request()->user()), 403);

        $request->merge(['price' => $request->filled('price') ? $request->input('price') : null]);
        $validated = $request->validate($this->rules());
        $validated['show_price'] = $request->boolean('show_price');
        $validated['show_notes'] = $request->boolean('show_notes');
        $validated['show_social'] = $request->boolean('show_social');
        $validated['show_address'] = $request->boolean('show_address');
        $validated['show_contact'] = $request->boolean('show_contact');
        $validated['show_share_buttons'] = $request->boolean('show_share_buttons');
        $validated['is_active'] = $request->boolean('is_active');

        $productLandingPage->update($validated);
        $this->handlePhotoUploads($request, $productLandingPage);

        return redirect()->route('product-landing-pages.edit', $productLandingPage)
            ->with('success', 'صفحه فرود به‌روزرسانی شد.');
    }

    private function handlePhotoUploads(Request $request, ProductLandingPage $page): void
    {
        $prefix = 'product-landing-pages/' . $page->id;

        if ($request->hasFile('photo')) {
            if ($page->photo_path) {
                Storage::disk('public')->delete($page->photo_path);
            }
            $path = $request->file('photo')->store($prefix, 'public');
            $page->update(['photo_path' => $path]);
        } elseif ($request->boolean('remove_photo')) {
            if ($page->photo_path) {
                Storage::disk('public')->delete($page->photo_path);
            }
            $page->update(['photo_path' => null]);
        }

        if ($request->hasFile('photos')) {
            $current = $page->photos ?? [];
            foreach ($request->file('photos') as $file) {
                $path = $file->store($prefix, 'public');
                $current[] = $path;
            }
            $page->update(['photos' => $current]);
        }

        $removePaths = $request->input('remove_photos', []);
        if (is_array($removePaths) && !empty($removePaths)) {
            $current = $page->photos ?? [];
            foreach ($removePaths as $path) {
                if (in_array($path, $current)) {
                    Storage::disk('public')->delete($path);
                    $current = array_values(array_filter($current, fn ($p) => $p !== $path));
                }
            }
            $page->update(['photos' => $current]);
        }
    }

    public function destroy(ProductLandingPage $productLandingPage)
    {
        abort_unless($productLandingPage->isVisibleTo(request()->user()), 403);

        if ($productLandingPage->photo_path) {
            Storage::disk('public')->delete($productLandingPage->photo_path);
        }
        foreach ($productLandingPage->photos ?? [] as $path) {
            Storage::disk('public')->delete($path);
        }
        Storage::disk('public')->deleteDirectory('product-landing-pages/' . $productLandingPage->id);

        $productLandingPage->delete();

        return redirect()->route('product-landing-pages.index')->with('success', 'صفحه فرود حذف شد.');
    }

    public function links(ProductLandingPage $productLandingPage)
    {
        abort_unless($productLandingPage->isVisibleTo(request()->user()), 403);

        if (!$productLandingPage->code) {
            return redirect()->route('product-landing-pages.edit', $productLandingPage)
                ->with('warning', 'ابتدا کد منحصربه‌فرد تولید کنید.');
        }

        return view('product-landing-pages.links', compact('productLandingPage'));
    }

    public function generateCode(ProductLandingPage $productLandingPage)
    {
        abort_unless($productLandingPage->isVisibleTo(request()->user()), 403);

        $code = Str::lower(Str::random(8));
        while (ProductLandingPage::where('code', $code)->exists()) {
            $code = Str::lower(Str::random(8));
        }
        $productLandingPage->update(['code' => $code]);

        return redirect()->back()->with('success', 'کد منحصربه‌فرد تولید شد: ' . $code);
    }

    private function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'headline' => 'nullable|string|max:255',
            'subheadline' => 'nullable|string|max:500',
            'cta_type' => 'required|in:purchase,call,whatsapp,link',
            'cta_url' => 'nullable|string|max:500',
            'cta_button_text' => 'nullable|string|max:100',
            'template' => 'nullable|string|in:hero,minimal,card,split',
            'primary_color' => 'nullable|string|max:20',
            'font_family' => 'nullable|string|in:vazirmatn,tahoma,inherit',
            'price' => 'nullable|numeric|min:0',
            'price_format' => 'nullable|string|in:rial,toman,none',
            'show_notes' => 'nullable|boolean',
            'notes_text' => 'nullable|string|max:2000',
            'show_social' => 'nullable|boolean',
            'social_instagram' => 'nullable|string|max:255',
            'social_telegram' => 'nullable|string|max:255',
            'social_whatsapp' => 'nullable|string|max:50',
            'show_address' => 'nullable|boolean',
            'address_text' => 'nullable|string|max:1000',
            'show_contact' => 'nullable|boolean',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'show_share_buttons' => 'nullable|boolean',
            'photo' => 'nullable|image|max:4096',
            'photos' => 'nullable|array',
            'photos.*' => 'image|max:4096',
            'remove_photo' => 'nullable|boolean',
            'remove_photos' => 'nullable|array',
            'remove_photos.*' => 'string|max:500',
        ];
    }
}
