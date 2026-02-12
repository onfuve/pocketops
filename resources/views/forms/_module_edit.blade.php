@php
    $config = $module->config ?? [];
    $label = $config['label'] ?? '';
    $content = $config['content'] ?? '';
    $help = $config['help'] ?? '';
    $accept = $config['accept'] ?? 'image/*,.pdf';
    $required = !empty($config['required']);
    $items = $config['items'] ?? [['text' => 'قوانین را می‌پذیرم', 'required' => true]];
    $questions = $config['questions'] ?? [['id' => 'q1', 'text' => 'نظر شما؟', 'type' => 'text']];
    $fieldType = $config['type'] ?? 'text';
    $placeholder = $config['placeholder'] ?? '';
    $fieldRequired = !empty($config['required']);
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
            <label class="ds-label" style="font-size: 0.8125rem;">نوع نمایش</label>
            <select name="config[style]" class="ds-select">
                <option value="normal" {{ ($config['style'] ?? '') === 'normal' ? 'selected' : '' }}>متن عادی</option>
                <option value="heading" {{ ($config['style'] ?? '') === 'heading' ? 'selected' : '' }}>عنوان / سرفصل</option>
            </select>
        </div>
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
            <label class="ds-label" style="font-size: 0.8125rem;">موارد تأیید (تا سه مورد)</label>
            @for($i = 0; $i < 3; $i++)
                @php $item = $items[$i] ?? ['text' => '', 'required' => false]; @endphp
                <div style="margin-bottom: 0.5rem;">
                    <input type="text"
                           name="config[items][{{ $i }}][text]"
                           value="{{ $item['text'] ?? '' }}"
                           class="ds-input"
                           placeholder="متن مورد {{ $i + 1 }}">
                    <label style="display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem; cursor: pointer; font-size: 0.8125rem;">
                        <input type="hidden" name="config[items][{{ $i }}][required]" value="0">
                        <input type="checkbox"
                               name="config[items][{{ $i }}][required]"
                               value="1"
                               {{ !empty($item['required']) ? 'checked' : '' }}>
                        اجباری
                    </label>
                </div>
            @endfor
            <p style="margin-top: 0.25rem; font-size: 0.75rem; color: var(--ds-text-subtle);">مواردی که متن خالی داشته باشند در فرم نمایش داده نمی‌شوند.</p>
        </div>
    @endif

    @if($module->type === 'survey')
        <div style="margin-bottom: 0.75rem;">
            <label class="ds-label" style="font-size: 0.8125rem;">سؤالات (تا سه سؤال)</label>
            @for($i = 0; $i < 3; $i++)
                @php
                    $q = $questions[$i] ?? [
                        'id' => 'q' . ($i + 1),
                        'text' => $i === 0 ? 'نظر شما؟' : '',
                        'type' => 'text',
                    ];
                @endphp
                <div style="margin-bottom: 0.75rem;">
                    <input type="text"
                           name="config[questions][{{ $i }}][text]"
                           value="{{ $q['text'] ?? '' }}"
                           class="ds-input"
                           placeholder="متن سؤال {{ $i + 1 }}">
                    <select name="config[questions][{{ $i }}][type]" class="ds-select" style="margin-top: 0.5rem;">
                        <option value="text" {{ ($q['type'] ?? '') === 'text' ? 'selected' : '' }}>متن آزاد</option>
                        <option value="nps" {{ ($q['type'] ?? '') === 'nps' ? 'selected' : '' }}>امتیاز ۰ تا ۱۰ (NPS)</option>
                    </select>
                    <input type="hidden"
                           name="config[questions][{{ $i }}][id]"
                           value="{{ $q['id'] ?? ('q' . ($i + 1)) }}">
                </div>
            @endfor
            <p style="margin-top: 0.25rem; font-size: 0.75rem; color: var(--ds-text-subtle);">سؤالاتی که متن خالی داشته باشند در فرم نمایش داده نمی‌شوند.</p>
        </div>
    @endif

    @if($module->type === 'postal_address')
        <div style="margin-bottom: 0.75rem;">
            <label class="ds-label" style="font-size: 0.8125rem;">متن راهنما (اختیاری)</label>
            <input type="text" name="config[help]" value="{{ $help }}" class="ds-input" placeholder="مثلاً: آدرس تحویل سفارش را بنویسید">
        </div>
    @endif

    @if($module->type === 'custom_fields')
        <div style="margin-bottom: 0.75rem;">
            <label class="ds-label" style="font-size: 0.8125rem;">نوع فیلد</label>
            <select name="config[type]" class="ds-select">
                <option value="text" {{ $fieldType === 'text' ? 'selected' : '' }}>متن کوتاه</option>
                <option value="textarea" {{ $fieldType === 'textarea' ? 'selected' : '' }}>متن چندخطی</option>
                <option value="email" {{ $fieldType === 'email' ? 'selected' : '' }}>ایمیل</option>
                <option value="number" {{ $fieldType === 'number' ? 'selected' : '' }}>عدد</option>
            </select>
        </div>
        <div style="margin-bottom: 0.75rem;">
            <label class="ds-label" style="font-size: 0.8125rem;">راهنما / placeholder (اختیاری)</label>
            <input type="text" name="config[placeholder]" value="{{ $placeholder }}" class="ds-input" placeholder="مثلاً: شماره سفارش شما">
        </div>
        <div style="margin-bottom: 0.75rem;">
            <label class="ds-label" style="font-size: 0.8125rem;">متن راهنما (اختیاری)</label>
            <input type="text" name="config[help]" value="{{ $help }}" class="ds-input" placeholder="توضیح کوتاه زیر فیلد">
        </div>
        <label style="display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.875rem;">
            <input type="hidden" name="config[required]" value="0">
            <input type="checkbox" name="config[required]" value="1" {{ $fieldRequired ? 'checked' : '' }}>
            اجباری
        </label>
    @endif

    <div style="margin-top: 1rem;">
        <button type="submit" class="ds-btn ds-btn-primary" style="padding: 0.5rem 1rem;">ذخیره ماژول</button>
    </div>
</form>
