<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()?->isAdmin()) {
                abort(403, 'فقط مدیر به این بخش دسترسی دارد.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $users = User::orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $user = new User([
            'role' => User::ROLE_TEAM,
            'can_delete_invoice' => false,
            'can_delete_contact' => false,
            'can_delete_lead' => false,
        ]);
        return view('users.create', compact('user'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|in:' . User::ROLE_ADMIN . ',' . User::ROLE_TEAM,
            'can_delete_invoice' => 'boolean',
            'can_delete_contact' => 'boolean',
            'can_delete_lead' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['can_delete_invoice'] = $request->boolean('can_delete_invoice');
        $validated['can_delete_contact'] = $request->boolean('can_delete_contact');
        $validated['can_delete_lead'] = $request->boolean('can_delete_lead');

        User::create($validated);
        return redirect()->route('users.index')->with('success', 'کاربر ایجاد شد.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:' . User::ROLE_ADMIN . ',' . User::ROLE_TEAM,
            'can_delete_invoice' => 'boolean',
            'can_delete_contact' => 'boolean',
            'can_delete_lead' => 'boolean',
        ];
        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        $validated = $request->validate($rules);
        unset($validated['password']);
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }
        $validated['can_delete_invoice'] = $request->boolean('can_delete_invoice');
        $validated['can_delete_contact'] = $request->boolean('can_delete_contact');
        $validated['can_delete_lead'] = $request->boolean('can_delete_lead');

        $user->update($validated);
        return redirect()->route('users.index')->with('success', 'کاربر به‌روزرسانی شد.');
    }
}
