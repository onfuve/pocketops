@php
    $config = $module->config ?? [];
    $label = $config['label'] ?? '';
    $content = $config['content'] ?? '';
    $help = $config['help'] ?? '';
    $accept = $config['accept'] ?? 'image/*,.pdf';
    $required = !empty($config['required']);
    $items = $config['items'] ?? [['text' => 'قوانین را می‌پذیرم', 'required' => true]];
    $questions = $config['questions'] ?? [['id' => 'q1', 'text' => 'نظر شما؟', 'type' => 'text']];
@endphp
<form action="{{ route('forms.modules.update', [$form, $module]) }}" method="post" class="module-edit-form" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--ds-border);">
    @csrf
    @method('PUT')

    @if(in_array($module->type, ['custom_text', 'file_upload', 'postal_address', 'consent', 'survey', 'custom_fields']))
        <div style="margin-bottom: 0.75rem;">
            <label class="ds-label" style="font-size: 0.8125rem;">برچسب / عنوان</label>
            <input type="text" name="config[label]" value="{{ $label }}" class="ds-input" placeholder="برچسب ماژول">
        </div>
    @endif

    @if($module->type === 'custom_text')
        <div style="margin-bottom: 0.75rem;">
            <label class="ds-label" style="font-size: 0.8125rem;">متن</label>
            <textarea name="config[content]" rows="4" class="ds-textarea" placeholder="متن توضیحات یا قوانین">{{ $content }}</textarea>
        </div>
    @endif

    @if($module->type === 'file_upload')
        <div style="margin-bottom: 0.75rem;">
            <label class="ds-label" style="font-size: 0.8125rem;">راهنما (اختیاری)</label>
            <input type="text" name="config[help]" value="{{ $help }}" class="ds-input" placeholder="مثلاً: تصویر یا PDF">
        </div>
        <div style="margin-bottom: 0.75rem;">
            <label class="ds-label" style="font-size: 0.8125rem;">نوع فایل مجاز</label>
            <input type="text" name="config[accept]" value="{{ $accept }}" class="ds-input" placeholder="image/*,.pdf">
        </div>
        <label style="display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.875rem;">
            <input type="hidden" name="config[required]" value="0">
            <input type="checkbox" name="config[required]" value="1" {{ $required ? 'checked' : '' }}>
            اجباری
        </label>
    @endif

    @if($module->type === 'consent')
        <div style="margin-bottom: 0.75rem;">
            <label class="ds-label" style="font-size: 0.8125rem;">متن تأیید (یک مورد)</label>
            <input type="text" name="config[items][0][text]" value="{{ $items[0]['text'] ?? 'قوانین را می‌پذیرم' }}" class="ds-input">
            <label style="display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem; cursor: pointer; font-size: 0.8125rem;">
                <input type="hidden" name="config[items][0][required]" value="0">
                <input type="checkbox" name="config[items][0][required]" value="1" {{ !empty($items[0]['required']) ? 'checked' : '' }}>
                اجباری
            </label>
        </div>
    @endif

    @if($module->type === 'survey')
        <div style="margin-bottom: 0.75rem;">
            <label class="ds-label" style="font-size: 0.8125rem;">سؤال اول</label>
            <input type="text" name="config[questions][0][text]" value="{{ $questions[0]['text'] ?? 'نظر شما؟' }}" class="ds-input" placeholder="متن سؤال">
            <select name="config[questions][0][type]" class="ds-select" style="margin-top: 0.5rem;">
                <option value="text" {{ ($questions[0]['type'] ?? '') === 'text' ? 'selected' : '' }}>متن آزاد</option>
                <option value="nps" {{ ($questions[0]['type'] ?? '') === 'nps' ? 'selected' : '' }}>امتیاز ۰ تا ۱۰ (NPS)</option>
            </select>
            <input type="hidden" name="config[questions][0][id]" value="{{ $questions[0]['id'] ?? 'q1' }}">
        </div>
    @endif

    <div style="margin-top: 1rem;">
        <button type="submit" class="ds-btn ds-btn-primary" style="padding: 0.5rem 1rem;">ذخیره ماژول</button>
    </div>
</form>
