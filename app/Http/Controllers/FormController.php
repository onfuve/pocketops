<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormLink;
use App\Models\FormModule;
use App\Models\FormSubmission;
use Illuminate\Http\Request;

class FormController extends Controller
{
    public function index(Request $request)
    {
        $forms = Form::query()
            ->visibleToUser($request->user())
            ->withCount(['links', 'submissions'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('forms.index', compact('forms'));
    }

    public function create()
    {
        $form = new Form([
            'title' => '',
            'status' => Form::STATUS_DRAFT,
            'edit_period_minutes' => 15,
            'submission_mode' => Form::MODE_SINGLE,
        ]);

        return view('forms.create', compact('form'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'edit_period_minutes' => 'nullable|integer|min:0|max:10080', // max 1 week
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['status'] = Form::STATUS_DRAFT;
        $validated['submission_mode'] = Form::MODE_SINGLE;
        $validated['edit_period_minutes'] = (int) ($validated['edit_period_minutes'] ?? 15);

        $form = Form::create($validated);

        return redirect()->route('forms.edit', $form)->with('success', 'فرم ساخته شد. ماژول‌ها را اضافه کنید.');
    }

    public function show(Request $request, Form $form)
    {
        $this->authorizeForm($form);

        $form->load('modules', 'links.submission');
        $submissions = $form->submissions()->with('contact', 'lead', 'formLink')->latest('updated_at')->paginate(20);

        $contacts = \App\Models\Contact::query()->orderBy('name')->limit(200)->get(['id', 'name']);
        $leads = \App\Models\Lead::query()->visibleToUser($request->user())->orderBy('name')->limit(100)->get(['id', 'name']);
        $tasks = \App\Models\Task::query()->visibleToUser($request->user())->orderBy('id', 'desc')->limit(100)->get(['id', 'title']);

        return view('forms.show', compact('form', 'submissions', 'contacts', 'leads', 'tasks'));
    }

    public function edit(Form $form)
    {
        $this->authorizeForm($form);

        $form->load('modules');

        return view('forms.edit', compact('form'));
    }

    public function update(Request $request, Form $form)
    {
        $this->authorizeForm($form);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:draft,active,closed',
            'edit_period_minutes' => 'nullable|integer|min:0|max:10080',
        ]);

        $form->update([
            'title' => $validated['title'],
            'status' => $validated['status'],
            'edit_period_minutes' => (int) ($validated['edit_period_minutes'] ?? 15),
        ]);

        return redirect()->route('forms.edit', $form)->with('success', 'فرم به‌روز شد.');
    }

    public function destroy(Form $form)
    {
        $this->authorizeForm($form);

        $form->delete();

        return redirect()->route('forms.index')->with('success', 'فرم حذف شد.');
    }

    public function storeModule(Request $request, Form $form)
    {
        $this->authorizeForm($form);

        $validated = $request->validate([
            'type' => 'required|in:custom_text,file_upload,postal_address,consent,survey,custom_fields',
            'config' => 'nullable|array',
        ]);

        $defaults = [
            'custom_text' => ['content' => 'متن توضیحات را اینجا بنویسید.'],
            'file_upload' => ['label' => 'آپلود فایل', 'help' => 'تصویر یا PDF', 'accept' => 'image/*,.pdf', 'required' => false],
            'postal_address' => ['label' => 'آدرس پستی'],
            'consent' => ['label' => 'رضایت', 'items' => [['text' => 'قوانین را می‌پذیرم', 'required' => true]]],
            'survey' => ['label' => 'نظرسنجی', 'questions' => [['id' => 'q1', 'text' => 'نظر شما؟', 'type' => 'text']]],
            'custom_fields' => ['label' => 'فیلد'],
        ];
        $config = array_merge($defaults[$validated['type']] ?? [], $validated['config'] ?? []);

        $maxOrder = $form->modules()->max('sort_order') ?? 0;
        $form->modules()->create([
            'type' => $validated['type'],
            'config' => $config,
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->route('forms.edit', $form)->with('success', 'ماژول اضافه شد.');
    }

    public function updateModule(Request $request, Form $form, FormModule $module)
    {
        $this->authorizeForm($form);
        if ($module->form_id !== $form->id) {
            abort(404);
        }

        $validated = $request->validate([
            'config' => 'nullable|array',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if (isset($validated['sort_order'])) {
            $module->update(['sort_order' => $validated['sort_order']]);
        }
        if (array_key_exists('config', $validated) && is_array($validated['config'])) {
            $merged = array_merge($module->config ?? [], $validated['config']);
            $module->update(['config' => $merged]);
        }

        return back()->with('success', 'ماژول به‌روز شد.');
    }

    public function destroyModule(Form $form, FormModule $module)
    {
        $this->authorizeForm($form);
        if ($module->form_id !== $form->id) {
            abort(404);
        }

        $module->delete();

        return back()->with('success', 'ماژول حذف شد.');
    }

    public function createLink(Request $request, Form $form)
    {
        $this->authorizeForm($form);

        if ($form->status !== Form::STATUS_ACTIVE) {
            return back()->with('error', 'برای ایجاد لینک، فرم باید فعال باشد.');
        }

        $validated = $request->validate([
            'contact_id' => 'nullable|exists:contacts,id',
            'lead_id' => 'nullable|exists:leads,id',
            'task_id' => 'nullable|exists:tasks,id',
        ]);

        $link = $form->links()->create([
            'code' => FormLink::generateCode(),
            'contact_id' => $validated['contact_id'] ?? null,
            'lead_id' => $validated['lead_id'] ?? null,
            'task_id' => $validated['task_id'] ?? null,
        ]);

        return back()->with('success', 'لینک ساخته شد.')->with('new_link', $link);
    }

    /** Inbox: all submissions for current user's forms. */
    public function inbox(Request $request)
    {
        $submissions = FormSubmission::query()
            ->whereHas('form', fn ($q) => $q->visibleToUser($request->user()))
            ->with(['form', 'formLink', 'contact', 'lead'])
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('forms.inbox', compact('submissions'));
    }

    public function showSubmission(FormSubmission $submission)
    {
        $form = $submission->form;
        if (!$form || !$form->isVisibleTo(request()->user())) {
            abort(403);
        }

        $submission->load('form.modules', 'contact', 'lead', 'task', 'attachments');

        return view('forms.submission-show', compact('submission', 'form'));
    }

    private function authorizeForm(Form $form): void
    {
        if (!$form->isVisibleTo(request()->user())) {
            abort(403, 'شما به این فرم دسترسی ندارید.');
        }
    }

}
