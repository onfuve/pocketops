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
            $submission = FormSubmission::create([
                'form_id' => $form->id,
                'form_link_id' => $link->id,
                'first_accessed_at' => now(),
                'last_activity_at' => now(),
                'data' => [],
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

        return view('forms.public-fill', [
            'form' => $form,
            'link' => $link,
            'submission' => $submission,
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
}
