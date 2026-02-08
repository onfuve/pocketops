<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagController extends Controller
{
    private static function tagUserId(): ?int
    {
        return Auth::id();
    }

    public function index()
    {
        $tags = Tag::forCurrentUser()->orderBy('name')->get();

        return view('tags.index', compact('tags'));
    }

    private static function suggestedTagColor(): string
    {
        $palette = [
            '#059669', '#0284c7', '#7c3aed', '#c026d3', '#db2777', '#dc2626',
            '#ea580c', '#ca8a04', '#65a30d', '#0d9488', '#2563eb', '#4f46e5',
            '#0891b2', '#be185d', '#b45309', '#15803d', '#1e40af', '#6d28d9',
        ];
        $used = Tag::forCurrentUser()->pluck('color')->map(fn ($c) => strtolower($c))->unique()->values()->all();
        $available = array_values(array_filter($palette, fn ($c) => !in_array(strtolower($c), $used, true)));
        $candidates = $available ?: $palette;

        return $candidates[array_rand($candidates)];
    }

    public function create()
    {
        $tag = new Tag(['color' => self::suggestedTagColor()]);

        return view('tags.create', compact('tag'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $validated['user_id'] = self::tagUserId();

        if (Tag::forCurrentUser()->where('name', $validated['name'])->exists()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'برچسب با این نام قبلاً وجود دارد.');
        }

        Tag::create($validated);

        return redirect()->route('tags.index')->with('success', 'برچسب اضافه شد.');
    }

    public function show(Tag $tag)
    {
        if (!Tag::forCurrentUser()->where('id', $tag->id)->exists()) {
            abort(404);
        }
        $tag->load(['leads', 'contacts', 'invoices.contact']);

        return view('tags.show', compact('tag'));
    }

    public function edit(Tag $tag)
    {
        if ($tag->user_id !== self::tagUserId()) {
            abort(403);
        }

        return view('tags.edit', compact('tag'));
    }

    public function update(Request $request, Tag $tag)
    {
        if ($tag->user_id !== self::tagUserId()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        if (Tag::forCurrentUser()->where('name', $validated['name'])->where('id', '!=', $tag->id)->exists()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'برچسب با این نام قبلاً وجود دارد.');
        }

        $tag->update($validated);

        return redirect()->route('tags.index')->with('success', 'برچسب به‌روزرسانی شد.');
    }

    public function destroy(Tag $tag)
    {
        if ($tag->user_id !== self::tagUserId()) {
            abort(403);
        }

        $tag->delete();

        return redirect()->route('tags.index')->with('success', 'برچسب حذف شد.');
    }
}
