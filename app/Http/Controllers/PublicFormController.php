<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Form;
use App\Models\FormLink;
use App\Models\FormSubmission;
use App\Models\Invoice;
use App\Services\ServqualService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicFormController extends Controller
{
    public function __construct(
        private ServqualService $servqualService
    ) {}

    /**
     * Show form by link code (customer-facing). Create submission on first access for single mode.
     */
    public function show(string $code)
    {
        $link = FormLink::where('code', $code)->with('form.modules')->firstOrFail();
        $form = $link->form;

        if ($form->status !== Form::STATUS_ACTIVE) {
            return response()->view('forms.public-unavailable', ['message' => 'این فرم در دسترس نیست.']);
        }

        $submission = $link->submission;

        if (!$submission) {
            $invoiceData = array_filter([
                'invoice_id' => request()->input('invoice_id'),
                'invoice_number' => request()->input('invoice_number'),
            ], fn ($v) => $v !== null && $v !== '');
            $submission = FormSubmission::create([
                'form_id' => $form->id,
                'form_link_id' => $link->id,
                'first_accessed_at' => now(),
                'last_activity_at' => now(),
                'data' => $invoiceData,
                'contact_id' => $link->contact_id,
                'lead_id' => $link->lead_id,
                'task_id' => $link->task_id,
            ]);
        } else {
            if ($submission->isEditPeriodExpired()) {
                return response()->view('forms.public-expired', ['submission' => $submission]);
            }
            $submission->touchActivity();
        }

        if ($form->is_servqual_micro ?? false) {
            $data = $submission->data ?? [];
            $contactId = $submission->contact_id;
            if (!$contactId && !empty($data['invoice_id'])) {
                $inv = Invoice::find($data['invoice_id']);
                $contactId = $inv ? $inv->contact_id : null;
            }
            // First-time: collect baseline expectation using one question per dimension from the bank (no dimension names shown)
            if ($contactId && !$this->servqualService->hasBaselineExpectation($contactId)) {
                $questions = $this->servqualService->pickOneQuestionPerDimension(null);
                return view('forms.public-servqual-expectation', [
                    'form' => $form,
                    'link' => $link,
                    'submission' => $submission,
                    'questions' => $questions,
                ]);
            }
            $invoice = null;
            if (!empty($data['invoice_id'])) {
                $invoice = Invoice::find($data['invoice_id']);
            }
            // Use question bank: one random question per dimension (avoid recent repeats for this contact)
            $questionIds = $data['servqual_question_ids'] ?? null;
            if (!is_array($questionIds) || empty($questionIds)) {
                $picked = $this->servqualService->pickOneQuestionPerDimension($invoice);
                $questionIds = array_map(fn ($q) => $q->id, $picked);
                $submission->update(['data' => array_merge($data, ['servqual_question_ids' => $questionIds])]);
                $submission->refresh();
            }
            $questions = collect();
            if (!empty($questionIds)) {
                $byId = \App\Models\ServqualQuestionBank::with('dimension')->whereIn('id', $questionIds)->get()->keyBy('id');
                foreach ($questionIds as $id) {
                    if (isset($byId[$id])) {
                        $questions->push($byId[$id]);
                    }
                }
            }
            return view('forms.public-servqual-micro', [
                'form' => $form,
                'link' => $link,
                'submission' => $submission,
                'questions' => $questions,
            ]);
        }

        $data = array_merge($submission->data ?? [], old('data', []));
        return view('forms.public-fill', [
            'form' => $form,
            'link' => $link,
            'submission' => $submission,
            'data' => $data,
        ]);
    }

    /**
     * Save draft or final submit.
     */
    public function submit(Request $request, string $code)
    {
        $link = FormLink::where('code', $code)->with('form.modules')->firstOrFail();
        $form = $link->form;

        if ($form->status !== Form::STATUS_ACTIVE) {
            return back()->with('error', 'این فرم در دسترس نیست.');
        }

        $submission = $link->submission;
        if (!$submission) {
            return back()->with('error', 'جلسه منقضی شده. لینک را دوباره باز کنید.');
        }

        if ($submission->isEditPeriodExpired()) {
            return back()->with('error', 'مهلت ویرایش تمام شده است.');
        }

        if ($form->is_servqual_micro ?? false) {
            if ($request->boolean('servqual_expectation')) {
                return $this->submitServqualExpectation($request, $link, $submission);
            }
            return $this->submitServqualMicro($request, $link, $submission);
        }

        $data = array_merge($submission->data ?? [], $request->input('data', []));
        $isFinal = $request->boolean('final');

        if ($isFinal) {
            $errors = $this->validateSubmission($form, $data, $request, $submission);
            if (!empty($errors)) {
                return back()->withErrors($errors)->withInput();
            }
        }

        foreach ($form->modules as $module) {
            $key = 'm' . $module->id;
            if ($module->type === 'file_upload') {
                if ($request->hasFile("data.{$key}")) {
                    $file = $request->file("data.{$key}");
                    $dir = 'attachments/form-submissions/' . $submission->id;
                    $path = $file->store($dir, 'public');
                    $att = $submission->attachments()->create([
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]);
                    $data[$key] = ['attachment_id' => $att->id];
                } elseif (isset($submission->data[$key])) {
                    $data[$key] = $submission->data[$key];
                }
            }
        }

        $submission->update([
            'data' => $data,
            'last_activity_at' => now(),
            'submitted_at' => $isFinal ? now() : $submission->submitted_at,
        ]);

        if ($isFinal) {
            return redirect()->route('forms.public.show', $code)
                ->with('success', 'فرم با موفقیت ارسال شد.');
        }

        return back()->with('success', 'پیش‌نویس ذخیره شد.');
    }

    private function validateSubmission(Form $form, array $data, Request $request, FormSubmission $submission): array
    {
        $errors = [];
        foreach ($form->modules as $module) {
            $key = 'm' . $module->id;
            $value = $data[$key] ?? null;
            $fieldKey = "data.{$key}";

            if ($module->type === 'file_upload' && $module->getConfig('required')) {
                $hasNewFile = $request->hasFile($fieldKey);
                $existing = $submission->data[$key] ?? null;
                $hasExisting = is_array($existing) && !empty($existing['attachment_id']);
                if (!$hasNewFile && !$hasExisting) {
                    $errors[$fieldKey] = ($module->getConfig('label') ?: 'فایل') . ' الزامی است.';
                }
            }

            if ($module->type === 'consent') {
                $items = $module->getConfig('items', []);
                $items = array_values(array_filter($items, fn ($i) => trim($i['text'] ?? '') !== ''));
                foreach ($items as $i => $item) {
                    if (!empty($item['required'])) {
                        $checked = is_array($value) && !empty($value[$i]);
                        if (!$checked) {
                            $errors[$fieldKey] = '«' . ($item['text'] ?? 'تأیید') . '» الزامی است.';
                            break;
                        }
                    }
                }
            }

            if ($module->type === 'custom_fields' && $module->getConfig('required')) {
                $empty = !is_scalar($value) || trim((string) $value) === '';
                if ($empty) {
                    $errors[$fieldKey] = ($module->getConfig('label') ?: 'این فیلد') . ' الزامی است.';
                }
            }
        }
        return $errors;
    }

    private function submitServqualExpectation(Request $request, FormLink $link, FormSubmission $submission)
    {
        $data = $submission->data ?? [];
        $contactId = $submission->contact_id;
        if (!$contactId && !empty($data['invoice_id'])) {
            $inv = Invoice::find($data['invoice_id']);
            $contactId = $inv ? $inv->contact_id : null;
        }
        if (!$contactId) {
            return back()->with('error', 'مخاطب مشخص نیست.')->withInput();
        }
        $dimensions = \App\Models\ServqualDimension::orderBy('sort')->get();
        $rules = [];
        foreach ($dimensions as $d) {
            $rules['expect.' . $d->id] = 'required|integer|min:1|max:5';
        }
        $validated = $request->validate($rules, [], array_combine(
            array_map(fn ($d) => 'expect.' . $d->id, $dimensions->all()),
            array_fill(0, $dimensions->count(), 'انتظار')
        ));
        $expect = $validated['expect'] ?? [];
        $this->servqualService->saveBaselineExpectation($contactId, $expect);
        $url = route('forms.public.show', $link->code);
        if (!empty($data['invoice_id'])) {
            $url .= '?invoice_id=' . (int) $data['invoice_id'];
            if (!empty($data['invoice_number'])) {
                $url .= '&invoice_number=' . rawurlencode($data['invoice_number']);
            }
        }
        return redirect($url)->with('success', 'انتظارات شما ثبت شد. لطفاً اکنون نظر خود را دربارهٔ این بار به ما بگویید.');
    }

    private function submitServqualMicro(Request $request, FormLink $link, FormSubmission $submission)
    {
        $data = $submission->data ?? [];
        $questionIds = $data['servqual_question_ids'] ?? [];
        if (!is_array($questionIds) || empty($questionIds)) {
            return back()->with('error', 'لطفاً لینک را دوباره باز کنید.');
        }

        $invoiceId = (int) ($data['invoice_id'] ?? 0);
        $invoice = $invoiceId ? Invoice::find($invoiceId) : null;
        if (!$invoice) {
            return back()->with('error', 'فاکتور مرتبط یافت نشد.');
        }

        $rules = [];
        foreach ($questionIds as $qid) {
            $rules['servqual_' . $qid] = 'required|integer|min:1|max:5';
        }
        $validated = $request->validate($rules, [], array_combine(array_map(fn ($id) => 'servqual_' . $id, $questionIds), array_fill(0, count($questionIds), 'پاسخ')));

        $responses = [];
        foreach ($questionIds as $qid) {
            $key = 'servqual_' . $qid;
            if (isset($validated[$key])) {
                $responses[$qid] = (int) $validated[$key];
            }
        }
        if (empty($responses)) {
            return back()->with('error', 'لطفاً سؤال‌ها را پاسخ دهید.')->withInput();
        }

        $this->servqualService->storeMicroResponses($invoice, $responses, $submission, $link->code);

        $submission->update([
            'data' => array_merge($data, ['servqual_submitted_at' => now()->toIso8601String()]),
            'last_activity_at' => now(),
            'submitted_at' => now(),
        ]);

        return redirect()->route('forms.public.show', $link->code)
            ->with('success', 'نظرسنجی با موفقیت ثبت شد. از شما متشکریم.');
    }
}
