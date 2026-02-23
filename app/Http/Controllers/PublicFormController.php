<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Form;
use App\Models\FormLink;
use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicFormController extends Controller
{
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
}
