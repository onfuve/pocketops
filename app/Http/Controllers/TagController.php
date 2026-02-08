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

    public function create()
    {
        $tag = new Tag(['color' => '#059669']);

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
