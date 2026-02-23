<?php

namespace App\Http\Controllers;

use App\Helpers\FormatHelper;
use App\Models\Contact;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->canModule('subscriptions', User::ABILITY_VIEW), 403, 'شما به این بخش دسترسی ندارید.');

        $q = $request->get('q');
        $category = $request->get('category');
        $status = $request->get('status'); // payment_status
        $perPage = (int) $request->get('per_page', 20);
        if (!in_array($perPage, [20, 50, 100], true)) {
            $perPage = 20;
        }

        $query = Subscription::visibleToUser($request->user())
            ->with(['contact', 'assignedTo']);

        if ($q) {
            $query->where(function ($qry) use ($q) {
                $qry->where('service_name', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%')
                    ->orWhereHas('contact', fn ($c) => $c->where('name', 'like', '%' . $q . '%'));
            });
        }
        if ($category) {
            $query->where('category', $category);
        }
        if ($status) {
            $query->where('payment_status', $status);
        }

        $subscriptions = $query->orderBy('expiry_date')->paginate($perPage)->withQueryString();

        return view('subscriptions.index', compact('subscriptions', 'q', 'category', 'status', 'perPage'));
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->canModule('subscriptions', User::ABILITY_CREATE), 403, 'شما مجوز ایجاد اشتراک را ندارید.');

        $contact = null;
        if ($request->filled('contact_id')) {
            $contact = Contact::visibleToUser($request->user())->find($request->contact_id);
        }
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('subscriptions.create', [
            'subscription' => new Subscription(['payment_status' => Subscription::PAYMENT_PENDING, 'auto_renewal' => false]),
            'contact' => $contact,
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->canModule('subscriptions', User::ABILITY_CREATE), 403, 'شما مجوز ایجاد اشتراک را ندارید.');

        $data = $this->validateSubscription($request);
        $data['user_id'] = $request->user()->id;

        $subscription = Subscription::create($data);
        $subscription->syncExpiryReminders();

        return redirect()->route('subscriptions.show', $subscription)->with('success', 'اشتراک با موفقیت ثبت شد و در تقویم نمایش داده می‌شود.');
    }

    public function show(Subscription $subscription)
    {
        abort_unless(request()->user()->canModule('subscriptions', User::ABILITY_VIEW), 403, 'شما به این بخش دسترسی ندارید.');
        abort_unless($subscription->isVisibleTo(request()->user()), 403, 'شما به این اشتراک دسترسی ندارید.');

        $subscription->load(['contact', 'assignedTo', 'reminders']);

        return view('subscriptions.show', compact('subscription'));
    }

    public function edit(Subscription $subscription)
    {
        abort_unless(request()->user()->canModule('subscriptions', User::ABILITY_EDIT), 403, 'شما مجوز ویرایش اشتراک را ندارید.');
        abort_unless($subscription->isVisibleTo(request()->user()), 403, 'شما به این اشتراک دسترسی ندارید.');

        $users = User::orderBy('name')->get(['id', 'name']);

        return view('subscriptions.edit', ['subscription' => $subscription, 'users' => $users]);
    }

    public function update(Request $request, Subscription $subscription)
    {
        abort_unless($request->user()->canModule('subscriptions', User::ABILITY_EDIT), 403, 'شما مجوز ویرایش اشتراک را ندارید.');
        abort_unless($subscription->isVisibleTo($request->user()), 403, 'شما به این اشتراک دسترسی ندارید.');

        $data = $this->validateSubscription($request);
        if (array_key_exists('account_credentials', $data) && $data['account_credentials'] === '') {
            unset($data['account_credentials']);
        }

        $subscription->update($data);
        $subscription->syncExpiryReminders();

        return redirect()->route('subscriptions.show', $subscription)->with('success', 'اشتراک به‌روزرسانی شد.');
    }

    public function destroy(Request $request, Subscription $subscription)
    {
        abort_unless($request->user()->canDeleteSubscription(), 403, 'شما مجوز حذف اشتراک را ندارید.');
        abort_unless($subscription->isVisibleTo($request->user()), 403, 'شما به این اشتراک دسترسی ندارید.');

        $subscription->reminders()->delete();
        $subscription->delete();

        return redirect()->route('subscriptions.index')->with('success', 'اشتراک حذف شد.');
    }

    private function validateSubscription(Request $request): array
    {
        $rules = [
            'contact_id' => 'required|exists:contacts,id',
            'service_name' => 'required|string|max:255',
            'category' => 'required|in:cloud,vpn,license,domain,other',
            'description' => 'nullable|string|max:1000',
            'start_date' => 'required|string|max:20',
            'expiry_date' => 'required|string|max:20',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly,custom',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:paid,pending,overdue',
            'auto_renewal' => 'boolean',
            'supplier' => 'nullable|string|max:255',
            'account_credentials' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:2000',
            'reminder_days_before' => 'nullable|in:3,7,14',
            'assigned_to_id' => 'nullable|exists:users,id',
        ];

        $request->merge(['reminder_days_before' => $request->input('reminder_days_before') ?: null]);
        $data = $request->validate($rules);
        $data['auto_renewal'] = $request->boolean('auto_renewal');

        $start = FormatHelper::shamsiToGregorian($data['start_date']);
        $expiry = FormatHelper::shamsiToGregorian($data['expiry_date']);
        if (!$start || !$expiry) {
            abort(422, 'تاریخ شروع یا انقضا معتبر نیست. فرمت: ۱۴۰۳/۱۱/۱۵');
        }
        $data['start_date'] = $start;
        $data['expiry_date'] = $expiry;
        $data['reminder_days_before'] = $data['reminder_days_before'] ?? null;
        $data['assigned_to_id'] = $data['assigned_to_id'] ?? null;
        $data['cost'] = isset($data['cost']) && $data['cost'] !== '' ? (float) $data['cost'] : null;

        return $data;
    }
}
