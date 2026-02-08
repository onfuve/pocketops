<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $query = Product::query()
            ->visibleToUser($request->user())
            ->with('tags')
            ->search($q)
            ->orderBy('sort_order')
            ->orderBy('name');
        $products = $query->paginate(20)->withQueryString();

        return view('products.index', compact('products', 'q'));
    }

    public function create()
    {
        $product = new Product;
        $tags = Tag::forCurrentUser()->orderBy('name')->get();

        return view('products.create', compact('product', 'tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $validated['user_id'] = $request->user()->id;
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('products', 'public');
        }

        $product = Product::create($validated);
        $this->syncTags($product, $request->input('tag_ids', []));

        return redirect()->route('products.index')->with('success', 'کالا/خدمت ذخیره شد.');
    }

    public function show(Product $product)
    {
        abort_unless($this->canAccess($product), 403, 'شما به این کالا/خدمت دسترسی ندارید.');

        $product->load('tags');

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        abort_unless($this->canAccess($product), 403, 'شما به این کالا/خدمت دسترسی ندارید.');

        $product->load('tags');
        $tags = Tag::forCurrentUser()->orderBy('name')->get();

        return view('products.edit', compact('product', 'tags'));
    }

    public function update(Request $request, Product $product)
    {
        abort_unless($this->canAccess($product), 403, 'شما به این کالا/خدمت دسترسی ندارید.');

        $validated = $request->validate($this->rules());
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('photo')) {
            if ($product->photo_path) {
                Storage::disk('public')->delete($product->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('products', 'public');
        } elseif ($request->boolean('remove_photo')) {
            if ($product->photo_path) {
                Storage::disk('public')->delete($product->photo_path);
            }
            $validated['photo_path'] = null;
        }

        $product->update($validated);
        $this->syncTags($product, $request->input('tag_ids', []));

        return redirect()->route('products.index')->with('success', 'کالا/خدمت به‌روزرسانی شد.');
    }

    public function destroy(Product $product)
    {
        abort_unless($this->canAccess($product), 403, 'شما به این کالا/خدمت دسترسی ندارید.');

        if ($product->photo_path) {
            Storage::disk('public')->delete($product->photo_path);
        }
        $product->delete();

        return redirect()->route('products.index')->with('success', 'کالا/خدمت حذف شد.');
    }

    /** API: search products for invoice autocomplete. */
    public function searchApi(Request $request)
    {
        $q = $request->get('q', '');
        $products = Product::query()
            ->visibleToUser($request->user())
            ->where('is_active', true)
            ->search($q)
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'description', 'default_unit_price', 'unit']);

        return response()->json($products->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'description' => $p->description,
                'default_unit_price' => (int) ($p->default_unit_price ?? 0),
                'unit' => $p->unit,
            ];
        }));
    }

    private function canAccess(Product $product): bool
    {
        $user = request()->user();
        if (!$user) {
            return false;
        }
        if ($user->isAdmin()) {
            return true;
        }
        return $product->user_id === $user->id || $product->user_id === null;
    }

    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'code_global' => 'nullable|string|max:100',
            'code_internal' => 'nullable|string|max:100',
            'photo' => 'nullable|image|max:2048',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ];
    }

    private function syncTags(Product $product, array $tagIds): void
    {
        $validTagIds = Tag::forCurrentUser()
            ->whereIn('id', $tagIds)
            ->pluck('id')
            ->toArray();
        $product->tags()->sync($validTagIds);
    }
}
