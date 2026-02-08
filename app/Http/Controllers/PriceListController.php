<?php

namespace App\Http\Controllers;

use App\Helpers\FormatHelper;
use App\Models\PriceList;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PriceListController extends Controller
{
    public function index(Request $request)
    {
        $query = PriceList::query()
            ->visibleToUser($request->user())
            ->with(['sections.items'])
            ->orderBy('updated_at', 'desc');
        $priceLists = $query->paginate(20)->withQueryString();

        return view('price-lists.index', compact('priceLists'));
    }

    public function create()
    {
        $priceList = new PriceList([
            'show_prices' => true,
            'show_photos' => false,
            'template' => 'simple',
            'is_active' => true,
        ]);
        $products = Product::query()
            ->visibleToUser(request()->user())
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('price-lists.create', compact('priceList', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $validated['user_id'] = $request->user()->id;
        $validated['show_prices'] = $request->boolean('show_prices');
        $validated['show_photos'] = $request->boolean('show_photos');
        $validated['show_cta'] = $request->boolean('show_cta');
        $validated['show_notes'] = $request->boolean('show_notes');
        $validated['show_social'] = $request->boolean('show_social');
        $validated['show_address'] = $request->boolean('show_address');
        $validated['show_contact'] = $request->boolean('show_contact');
        $validated['show_share_buttons'] = $request->boolean('show_share_buttons');
        $validated['is_active'] = $request->boolean('is_active');

        $priceList = PriceList::create($validated);

        return redirect()->route('price-lists.edit', $priceList)->with('success', 'لیست قیمت ایجاد شد. بخش‌ها و آیتم‌ها را اضافه کنید.');
    }

    public function show(PriceList $priceList)
    {
        abort_unless($priceList->isVisibleTo(request()->user()), 403, 'شما به این لیست قیمت دسترسی ندارید.');

        $priceList->load(['sections.items.product']);

        return view('price-lists.show', compact('priceList'));
    }

    public function edit(PriceList $priceList)
    {
        abort_unless($priceList->isVisibleTo(request()->user()), 403, 'شما به این لیست قیمت دسترسی ندارید.');

        $priceList->load(['sections.items.product']);
        $products = Product::query()
            ->visibleToUser(request()->user())
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('price-lists.edit', compact('priceList', 'products'));
    }

    public function update(Request $request, PriceList $priceList)
    {
        abort_unless($priceList->isVisibleTo(request()->user()), 403, 'شما به این لیست قیمت دسترسی ندارید.');

        $validated = $request->validate($this->rules());
        $validated['show_prices'] = $request->boolean('show_prices');
        $validated['show_photos'] = $request->boolean('show_photos');
        $validated['show_cta'] = $request->boolean('show_cta');
        $validated['show_notes'] = $request->boolean('show_notes');
        $validated['show_social'] = $request->boolean('show_social');
        $validated['show_address'] = $request->boolean('show_address');
        $validated['show_contact'] = $request->boolean('show_contact');
        $validated['show_share_buttons'] = $request->boolean('show_share_buttons');
        $validated['is_active'] = $request->boolean('is_active');

        $priceList->update(collect($validated)->except('sections')->all());

        $sections = $request->input('sections', []);
        $sections = is_array($sections) ? array_values($sections) : [];
        Log::info('Price list update sections received', ['price_list_id' => $priceList->id, 'sections_count' => count($sections), 'sections' => $sections]);
        $this->syncSections($priceList, $sections);

        return redirect()->route('price-lists.show', $priceList)->with('success', 'لیست قیمت به‌روزرسانی شد.');
    }

    public function destroy(PriceList $priceList)
    {
        abort_unless($priceList->isVisibleTo(request()->user()), 403, 'شما به این لیست قیمت دسترسی ندارید.');

        $priceList->delete();

        return redirect()->route('price-lists.index')->with('success', 'لیست قیمت حذف شد.');
    }

    public function duplicate(PriceList $priceList)
    {
        abort_unless($priceList->isVisibleTo(request()->user()), 403, 'شما به این لیست قیمت دسترسی ندارید.');

        $new = $priceList->replicate(['code']);
        $new->name = $priceList->name . ' (کپی)';
        $new->user_id = request()->user()->id;
        $new->save();

        foreach ($priceList->sections as $section) {
            $newSection = $section->replicate();
            $newSection->price_list_id = $new->id;
            $newSection->save();
            foreach ($section->items as $item) {
                $newItem = $item->replicate();
                $newItem->price_list_section_id = $newSection->id;
                $newItem->save();
            }
        }

        return redirect()->route('price-lists.edit', $new)->with('success', 'لیست قیمت کپی شد.');
    }

    public function generateCode(PriceList $priceList)
    {
        abort_unless($priceList->isVisibleTo(request()->user()), 403, 'شما به این لیست قیمت دسترسی ندارید.');

        $code = Str::lower(Str::random(8));
        while (PriceList::where('code', $code)->exists()) {
            $code = Str::lower(Str::random(8));
        }
        $priceList->update(['code' => $code]);

        return redirect()->back()->with('success', 'کد منحصربه‌فرد تولید شد: ' . $code);
    }

    public function links(PriceList $priceList)
    {
        abort_unless($priceList->isVisibleTo(request()->user()), 403, 'شما به این لیست قیمت دسترسی ندارید.');

        if (!$priceList->code) {
            return redirect()->route('price-lists.edit', $priceList)
                ->with('warning', 'ابتدا کد منحصربه‌فرد تولید کنید.');
        }

        return view('price-lists.links', compact('priceList'));
    }

    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'title_text' => 'nullable|string|max:255',
            'template' => 'nullable|string|in:simple,with_photos,grid',
            'font_family' => 'nullable|string|in:vazirmatn,tahoma,inherit',
            'price_format' => 'nullable|string|in:rial,toman,none',
            'show_cta' => 'nullable|boolean',
            'cta_url' => 'nullable|string|max:500',
            'cta_text' => 'nullable|string|max:100',
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
            'sections' => 'nullable|array',
            'sections.*.name' => 'nullable|string|max:255',
            'sections.*.items' => 'nullable|array',
            'sections.*.items.*.product_id' => 'nullable|exists:products,id',
            'sections.*.items.*.custom_name' => 'nullable|string|max:255',
            'sections.*.items.*.custom_description' => 'nullable|string|max:2000',
            'sections.*.items.*.unit_price' => 'nullable|string|max:50',
            'sections.*.items.*.unit' => 'nullable|string|max:30',
            'sections.*.items.*.badge' => 'nullable|string|max:30|in:new,hot,special_offer,sale',
        ];
    }

    private function syncSections(PriceList $priceList, array $sections): void
    {
        $priceList->sections()->delete();

        $sortSection = 0;
        foreach ($sections as $sec) {
            if (!is_array($sec)) {
                continue;
            }
            $name = trim($sec['name'] ?? '');
            if ($name === '') {
                continue;
            }
            $section = $priceList->sections()->create([
                'name' => $name,
                'sort_order' => $sortSection++,
            ]);

            $items = isset($sec['items']) && is_array($sec['items']) ? array_values($sec['items']) : [];
            $sortItem = 0;
            foreach ($items as $it) {
                if (!is_array($it)) {
                    continue;
                }
                $productId = !empty($it['product_id']) ? (int) $it['product_id'] : null;
                $customName = trim($it['custom_name'] ?? '');
                if (!$productId && $customName === '') {
                    continue;
                }
                $section->items()->create([
                    'product_id' => $productId,
                    'custom_name' => $customName ?: null,
                    'custom_description' => trim($it['custom_description'] ?? '') ?: null,
                    'unit_price' => $this->parsePrice($it['unit_price'] ?? null),
                    'unit' => trim($it['unit'] ?? '') ?: null,
                    'badge' => $this->normalizeBadge($it['badge'] ?? null),
                    'sort_order' => $sortItem++,
                ]);
            }
        }
    }

    private function normalizeBadge(mixed $value): ?string
    {
        $v = trim((string) ($value ?? ''));
        if ($v === '') {
            return null;
        }
        return in_array($v, ['new', 'hot', 'special_offer', 'sale'], true) ? $v : null;
    }

    private function parsePrice(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (int) round((float) $value);
        }
        $cleaned = FormatHelper::persianToEnglish((string) $value);
        $cleaned = preg_replace('/[^\d.]/', '', $cleaned);

        return $cleaned !== '' ? (int) round((float) $cleaned) : null;
    }
}
