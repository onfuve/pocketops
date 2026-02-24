<?php

namespace App\Http\Controllers;

use App\Models\ServqualQuestionBank;
use Illuminate\Http\Request;

class ServqualQuestionBankController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()?->isAdmin()) {
                abort(403, 'فقط مدیر به بانک سوالات SERVQUAL دسترسی دارد.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $dimensions = \App\Models\ServqualDimension::with('questions')->orderBy('sort')->get();
        return view('settings.servqual-question-bank.index', compact('dimensions'));
    }

    public function edit(ServqualQuestionBank $question)
    {
        $question->load('dimension');
        return view('settings.servqual-question-bank.edit', compact('question'));
    }

    public function update(Request $request, ServqualQuestionBank $question)
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'فقط مدیر می‌تواند سوالات را ویرایش کند.');

        $validated = $request->validate([
            'text' => 'required|string|max:200',
            'text_fa' => 'nullable|string|max:200',
            'weight' => 'nullable|integer|min:1|max:10',
            'is_reverse_scored' => 'boolean',
        ]);

        $question->update([
            'text' => $validated['text'],
            'text_fa' => $request->filled('text_fa') ? $validated['text_fa'] : null,
            'weight' => (int) ($validated['weight'] ?? 1),
            'is_reverse_scored' => $request->boolean('is_reverse_scored'),
        ]);

        return redirect()->route('settings.servqual-question-bank.index')
            ->with('success', 'سؤال به‌روزرسانی شد.');
    }
}
