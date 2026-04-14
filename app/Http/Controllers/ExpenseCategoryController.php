<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::ordered()->get();

        return view('settings.expense-categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
        ], [
            'name.required' => 'نام دسته را وارد کنید.',
        ]);

        $validated['name'] = trim($validated['name']);
        $validated['sort_order'] = (int) (ExpenseCategory::max('sort_order') ?? 0) + 1;

        ExpenseCategory::create($validated);

        return redirect()->route('settings.expense-categories')->with('success', 'دسته اضافه شد.');
    }

    public function destroy(Request $request, ExpenseCategory $expenseCategory)
    {
        if (ExpenseCategory::count() <= 1) {
            return redirect()->route('settings.expense-categories')->withErrors(['delete' => 'حداقل یک دسته باید باقی بماند.']);
        }

        if ($expenseCategory->businessExpenses()->exists()) {
            return redirect()->route('settings.expense-categories')->withErrors(['delete' => 'این دسته در هزینه‌ها استفاده شده؛ ابتدا هزینه‌ها را ویرایش یا حذف کنید.']);
        }

        $expenseCategory->delete();

        return redirect()->route('settings.expense-categories')->with('success', 'دسته حذف شد.');
    }
}
