<?php

namespace App\Http\Controllers;

use App\Helpers\FormatHelper;
use App\Models\Attachment;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadChannel;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $leads = Lead::query()
            ->visibleToUser($request->user())
            ->with('contact', 'leadChannel', 'referrerContact', 'user', 'assignedTo', 'tags')
            ->search($request->get('q'))
            ->ofStatus($request->get('status'))
            ->latest('lead_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('leads.index', compact('leads'));
    }

    public function create()
    {
        $lead = new Lead([
            'status' => Lead::STATUS_NEW,
            'lead_date' => now(),
        ]);
        $leadChannels = LeadChannel::orderBy('sort')->get();
        $tags = Tag::forCurrentUser()->orderBy('name')->get();

        return view('leads.create', ['lead' => $lead, 'leadChannels' => $leadChannels, 'tags' => $tags]);
    }

    public function store(Request $request)
    {
        $this->normalizeLeadDate($request);
        $validated = $request->validate($this->rules());
        $this->validateReferrer($request);
        $validated['value'] = $this->numericValue($request->get('value'));
        $validated['lead_date'] = $validated['lead_date'] ?? now();
        $validated['lead_channel_id'] = $request->filled('lead_channel_id') ? $request->lead_channel_id : null;
        $validated['referrer_contact_id'] = $request->filled('referrer_contact_id') ? $request->referrer_contact_id : null;
        $validated['user_id'] = $request->user()->id;

        $lead = Lead::create($validated);
        $this->syncTags($lead, $request->input('tag_ids', []));

        // Log call if call fields provided
        $this->logCallIfProvided($lead, $request);

        if ($request->boolean('add_another')) {
            if ($request->boolean('from_quick_add')) {
                return redirect()->route('leads.index')->with('success', 'سرنخ ذخیره شد. سرنخ بعدی را وارد کنید.');
            }
            return redirect()->route('leads.create')->with('success', 'سرنخ ذخیره شد. سرنخ بعدی را وارد کنید.');
        }

        return redirect()->route('leads.show', $lead)->with('success', 'سرنخ ذخیره شد.');
    }

    public function show(Lead $lead)
    {
        abort_unless($lead->isVisibleTo(request()->user()), 403, 'شما به این سرنخ دسترسی ندارید.');

        $lead->load('contact', 'leadChannel', 'referrerContact', 'tags', 'activities', 'comments.user', 'attachments', 'tasks.assignedUsers', 'user', 'assignedTo');

        $existingContacts = collect();
        if (!$lead->contact_id && trim((string) $lead->name) !== '') {
            $user = request()->user();
            $byName = Contact::visibleToUser($user)->where('name', trim($lead->name))->get();
            $existingContacts = $existingContacts->merge($byName);
            if ($lead->phone) {
                $byPhone = Contact::visibleToUser($user)->whereHas('contactPhones', fn ($q) => $q->where('phone', $lead->phone))->get();
                $existingContacts = $existingContacts->merge($byPhone);
            }
            $existingContacts = $existingContacts->unique('id')->values();
        }

        $tags = Tag::forCurrentUser()->orderBy('name')->get();
        $users = User::orderBy('name')->get(); // Include all users for assign dropdown
        $lead->load('assignedTo');
        return view('leads.show', compact('lead', 'existingContacts', 'tags', 'users'));
    }

    public function edit(Lead $lead)
    {
        abort_unless($lead->isVisibleTo(request()->user()), 403, 'شما به این سرنخ دسترسی ندارید.');

        $leadChannels = LeadChannel::orderBy('sort')->get();
        $tags = Tag::forCurrentUser()->orderBy('name')->get();
        $users = User::where('id', '!=', auth()->id())->orderBy('name')->get();
        $lead->load('tags');

        return view('leads.edit', compact('lead', 'leadChannels', 'tags', 'users'));
    }

    public function update(Request $request, Lead $lead)
    {
        abort_unless($lead->isVisibleTo($request->user()), 403, 'شما به این سرنخ دسترسی ندارید.');

        $this->normalizeLeadDate($request);
        $validated = $request->validate($this->rules());
        $this->validateReferrer($request);
        $validated['value'] = $this->numericValue($request->get('value'));
        $validated['lead_channel_id'] = $request->filled('lead_channel_id') ? $request->lead_channel_id : null;
        $validated['referrer_contact_id'] = $request->filled('referrer_contact_id') ? $request->referrer_contact_id : null;
        if ($lead->user_id === $request->user()->id || $request->user()->isAdmin()) {
            $validated['assigned_to_id'] = $request->filled('assigned_to_id') ? $request->assigned_to_id : null;
        }

        $lead->update($validated);
        $this->syncTags($lead, $request->input('tag_ids', []));

        // Log call if call fields provided
        $this->logCallIfProvided($lead, $request);

        return redirect()->route('leads.show', $lead)->with('success', 'سرنخ به‌روزرسانی شد.');
    }

    public function destroy(Lead $lead)
    {
        abort_unless($lead->isVisibleTo(request()->user()), 403, 'شما به این سرنخ دسترسی ندارید.');
        abort_unless(request()->user()->canDeleteLead(), 403, 'شما مجوز حذف سرنخ را ندارید.');

        $lead->delete();

        return redirect()->route('leads.index')->with('success', 'سرنخ حذف شد.');
    }

    public function convertToContact(Lead $lead)
    {
        abort_unless($lead->isVisibleTo(request()->user()), 403, 'شما به این سرنخ دسترسی ندارید.');

        if ($lead->contact_id) {
            return redirect()->route('leads.show', $lead)->with('error', 'این سرنخ قبلاً به مخاطب تبدیل شده است.');
        }

        $referrerName = $lead->referrerContact?->name ?? $lead->source;
        $contact = Contact::create([
            'name' => trim((string) ($lead->name ?? '')) ?: 'بدون نام',
            'user_id' => request()->user()->id,
            'address' => null,
            'city' => null,
            'website' => null,
            'instagram' => null,
            'telegram' => null,
            'whatsapp' => null,
            'referrer_name' => $referrerName,
            'is_hamkar' => false,
            'linked_contact_id' => null,
            'notes' => trim(($lead->company ? "شرکت: {$lead->company}\n" : '') . ($lead->details ?? '')),
        ]);

        if ($lead->phone) {
            $contact->contactPhones()->create([
                'phone' => $lead->phone,
                'label' => 'موبایل',
                'sort' => 0,
            ]);
        }

        $lead->update(['contact_id' => $contact->id]);

        return redirect()->route('contacts.show', $contact)->with('success', 'سرنخ به مخاطب تبدیل شد.');
    }

    /** Create a draft invoice from this lead. Converts to contact first if needed. */
    public function createInvoiceFromLead(Lead $lead)
    {
        abort_unless($lead->isVisibleTo(request()->user()), 403, 'شما به این سرنخ دسترسی ندارید.');

        if (!$lead->contact_id) {
            $referrerName = $lead->referrerContact?->name ?? $lead->source;
            $contact = Contact::create([
                'name' => trim((string) ($lead->name ?? '')) ?: 'بدون نام',
                'user_id' => request()->user()->id,
                'address' => null,
                'city' => null,
                'website' => null,
                'instagram' => null,
                'telegram' => null,
                'whatsapp' => null,
                'referrer_name' => $referrerName,
                'is_hamkar' => false,
                'linked_contact_id' => null,
                'notes' => trim(($lead->company ? "شرکت: {$lead->company}\n" : '') . ($lead->details ?? '')),
            ]);
            if ($lead->phone) {
                $contact->contactPhones()->create([
                    'phone' => $lead->phone,
                    'label' => 'موبایل',
                    'sort' => 0,
                ]);
            }
            $lead->update(['contact_id' => $contact->id]);
        }

        return redirect()->route('invoices.create', [
            'contact_id' => $lead->contact_id,
            'type' => Invoice::TYPE_SELL,
        ])->with('success', 'فاکتور پیش‌نویس ایجاد شد. سرنخ در صورت نیاز به مخاطب تبدیل شد.');
    }

    /** Assign/pass lead to a team member. */
    public function assignLead(Request $request, Lead $lead)
    {
        abort_unless($lead->isVisibleTo($request->user()), 403, 'شما به این سرنخ دسترسی ندارید.');
        abort_unless($lead->user_id === $request->user()->id || $request->user()->isAdmin(), 403, 'فقط مالک سرنخ یا مدیر می‌تواند واگذار کند.');

        $validated = $request->validate([
            'assigned_to_id' => 'nullable|exists:users,id',
        ]);
        $lead->update([
            'assigned_to_id' => $validated['assigned_to_id'] ?: null,
        ]);

        $msg = $lead->assigned_to_id
            ? 'سرنخ به ' . $lead->assignedTo->name . ' واگذار شد.'
            : 'واگذاری سرنخ لغو شد.';
        return redirect()->route('leads.show', $lead)->with('success', $msg);
    }

    public function showChangeStatus(Request $request, Lead $lead)
    {
        abort_unless($lead->isVisibleTo($request->user()), 403, 'شما به این سرنخ دسترسی ندارید.');

        $status = $request->get('status');
        $allowed = array_merge(Lead::pipelineStatuses(), ['negotiation']);
        if (!$status || !in_array($status, $allowed, true)) {
            return redirect()->route('leads.show', $lead)->with('error', 'مرحله نامعتبر است.');
        }
        $activityDate = $request->get('date', FormatHelper::shamsi(now()));

        return view('leads.change-status', [
            'lead' => $lead,
            'newStatus' => $status,
            'activityDate' => $activityDate,
        ]);
    }

    public function submitChangeStatus(Request $request, Lead $lead)
    {
        abort_unless($lead->isVisibleTo($request->user()), 403, 'شما به این سرنخ دسترسی ندارید.');

        $allowed = array_merge(Lead::pipelineStatuses(), ['negotiation']);
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', $allowed),
            'comment' => 'nullable|string|max:2000',
            'activity_date' => 'nullable|string',
        ]);
        $status = $validated['status'];
        $comment = $validated['comment'] ?? null;
        $dateStr = $validated['activity_date'] ?? FormatHelper::shamsi(now());
        $activityDate = now();
        if (trim((string) $dateStr) !== '') {
            $gregorian = FormatHelper::shamsiToGregorian($dateStr);
            if ($gregorian !== null) {
                $activityDate = \Carbon\Carbon::parse($gregorian);
            }
        }

        $fromStatus = $lead->status;
        $lead->update(['status' => $status]);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'from_status' => $fromStatus,
            'to_status' => $status,
            'comment' => $comment,
            'activity_date' => $activityDate,
        ]);

        return redirect()->route('leads.show', $lead)->with('success', 'مرحله سرنخ با ثبت نظر به‌روز شد.');
    }

    private function rules(): array
    {
        $rules = [
            'name' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'source' => 'nullable|string|max:255',
            'details' => 'nullable|string|max:5000',
            'status' => 'required|in:' . implode(',', array_merge(Lead::pipelineStatuses(), ['negotiation'])),
            'value' => 'nullable|numeric|min:0',
            'lead_date' => 'nullable|date',
            'lead_channel_id' => 'nullable|exists:lead_channels,id',
            'referrer_contact_id' => 'nullable|exists:contacts,id',
            'assigned_to_id' => 'nullable|exists:users,id',
        ];

        return $rules;
    }

    private function validateReferrer(Request $request): void
    {
        $channelId = $request->filled('lead_channel_id') ? $request->lead_channel_id : null;
        if (!$channelId) {
            return;
        }
        $channel = LeadChannel::find($channelId);
        if ($channel && $channel->is_referral && !$request->filled('referrer_contact_id')) {
            validator($request->all(), ['referrer_contact_id' => 'required|exists:contacts,id'])->validate();
        }
    }

    private function normalizeLeadDate(Request $request): void
    {
        $date = $request->get('lead_date');
        if (is_string($date) && $date !== '') {
            $gregorian = \App\Helpers\FormatHelper::shamsiToGregorian($date);
            if ($gregorian !== null) {
                $request->merge(['lead_date' => $gregorian]);
            }
        }
    }

    private function numericValue(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        $value = \App\Helpers\FormatHelper::persianToEnglish((string) $value);

        return (int) preg_replace('/[^0-9]/', '', $value) ?: null;
    }

    private function syncTags(Lead $lead, array $tagIds): void
    {
        $validTagIds = Tag::forCurrentUser()
            ->whereIn('id', $tagIds)
            ->pluck('id')
            ->toArray();
        $lead->tags()->sync($validTagIds);
    }

    public function storeAttachment(Request $request, Lead $lead)
    {
        abort_unless($lead->isVisibleTo($request->user()), 403, 'شما به این سرنخ دسترسی ندارید.');

        $request->validate(['file' => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf']);
        $file = $request->file('file');
        $dir = 'attachments/leads/' . $lead->id;
        $path = $file->store($dir, 'public');
        $lead->attachments()->create([
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
        return redirect()->route('leads.show', $lead)->with('success', 'فایل پیوست شد.');
    }

    public function destroyAttachment(Lead $lead, Attachment $attachment)
    {
        abort_unless($lead->isVisibleTo(request()->user()), 403, 'شما به این سرنخ دسترسی ندارید.');

        if ($attachment->attachable_type !== Lead::class || (int) $attachment->attachable_id !== (int) $lead->id) {
            abort(404);
        }
        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();
        return redirect()->route('leads.show', $lead)->with('success', 'پیوست حذف شد.');
    }

    /** Update lead tags from show page. */
    public function updateTags(Request $request, Lead $lead)
    {
        abort_unless($lead->isVisibleTo($request->user()), 403, 'شما به این سرنخ دسترسی ندارید.');

        $validated = $request->validate([
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);
        $this->syncTags($lead, $validated['tag_ids'] ?? []);

        return redirect()->route('leads.show', $lead)->with('success', 'برچسب‌ها به‌روزرسانی شدند.');
    }

    /** Add a comment/event to lead. */
    public function storeComment(Request $request, Lead $lead)
    {
        abort_unless($lead->isVisibleTo($request->user()), 403, 'شما به این سرنخ دسترسی ندارید.');

        $request->validate(['body' => 'required|string|max:2000']);

        $lead->comments()->create([
            'user_id' => $request->user()->id,
            'body' => trim($request->body),
        ]);

        return redirect()->route('leads.show', $lead)->with('success', 'نظر ثبت شد.');
    }

    /** Store call log from lead show page. */
    public function storeCallLog(Request $request, Lead $lead)
    {
        abort_unless($lead->isVisibleTo($request->user()), 403, 'شما به این سرنخ دسترسی ندارید.');

        $validated = $request->validate([
            'call_date' => 'required|string',
            'call_type' => 'required|in:incoming,outgoing',
            'call_notes' => 'required|string|max:2000',
        ]);

        $callDate = now();
        if ($request->filled('call_date')) {
            $gregorian = \App\Helpers\FormatHelper::shamsiToGregorian($request->input('call_date'));
            if ($gregorian !== null) {
                $callDate = \Carbon\Carbon::parse($gregorian);
            }
        }

        $callTypeLabel = $validated['call_type'] === 'incoming' ? 'ورودی' : 'خروجی';
        $callNotes = '📞 تماس ' . $callTypeLabel . ' انجام شد';
        
        if ($request->filled('call_notes')) {
            $callNotes .= "\n" . trim($validated['call_notes']);
        }

        LeadActivity::create([
            'lead_id' => $lead->id,
            'from_status' => $lead->status,
            'to_status' => $lead->status,
            'comment' => $callNotes,
            'activity_date' => $callDate,
        ]);

        return redirect()->route('leads.show', $lead)->with('success', 'تماس ثبت شد.');
    }

    /** Log call activity if call fields are provided in request. */
    private function logCallIfProvided(Lead $lead, Request $request): void
    {
        if (!$request->filled('call_notes') && !$request->filled('call_date')) {
            return;
        }

        $callDate = now();
        if ($request->filled('call_date')) {
            $gregorian = \App\Helpers\FormatHelper::shamsiToGregorian($request->input('call_date'));
            if ($gregorian !== null) {
                $callDate = \Carbon\Carbon::parse($gregorian);
            }
        }

        $callType = $request->input('call_type', 'outgoing');
        $callTypeLabel = $callType === 'incoming' ? 'ورودی' : 'خروجی';
        $callNotes = '📞 تماس ' . $callTypeLabel . ' انجام شد';
        
        if ($request->filled('call_notes')) {
            $callNotes .= "\n" . trim($request->input('call_notes'));
        }

        LeadActivity::create([
            'lead_id' => $lead->id,
            'from_status' => $lead->status,
            'to_status' => $lead->status,
            'comment' => $callNotes,
            'activity_date' => $callDate,
        ]);
    }
}
