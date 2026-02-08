# Design System — pocket-business

Unified styles for buttons, filters, inputs, cards, and page layout. Use these classes for consistent UI across all views.

**Font:** Vazirmatn | **Layout:** RTL | **Language:** Persian

---

## CSS Variables (Custom Properties)

All tokens live in `:root`. Use these in custom CSS:

| Token | Value | Usage |
|-------|-------|-------|
| `--ds-font` | 'Vazirmatn', sans-serif | Font family |
| `--ds-primary` | #059669 | Primary green |
| `--ds-primary-hover` | #047857 | Primary hover |
| `--ds-primary-bg` | #ecfdf5 | Primary background (light) |
| `--ds-text` | #292524 | Main text |
| `--ds-text-muted` | #57534e | Secondary text |
| `--ds-text-subtle` | #78716c | Subtle text |
| `--ds-border` | #e7e5e4 | Borders |
| `--ds-border-hover` | #d6d3d1 | Border hover |
| `--ds-radius-sm` | 0.5rem | Small radius |
| `--ds-radius` | 0.75rem | Default radius |
| `--ds-radius-lg` | 1rem | Large radius |

---

## Page Layout

```blade
<div class="ds-page">
    <div class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon">@include('components._icons', ['name' => 'users', 'class' => 'w-5 h-5'])</span>
                عنوان صفحه
            </h1>
            <p class="ds-page-subtitle">توضیح کوتاه</p>
        </div>
        <a href="..." class="ds-btn ds-btn-primary">اقدام اصلی</a>
    </div>
    {{-- content --}}
</div>
```

- `.ds-page` — Max-width container (52rem), centered
- `.ds-page-header` — Title + actions row
- `.ds-page-title` — H1 with icon
- `.ds-page-title-icon` — Icon container (green accent)
- `.ds-page-subtitle` — Subtitle text

---

## Buttons

| Class | Usage |
|-------|-------|
| `.ds-btn` | Base button (use with variant) |
| `.ds-btn-primary` | Primary action (green) |
| `.ds-btn-secondary` | Secondary / cancel (neutral) |
| `.ds-btn-outline` | Outline (white + border) |
| `.ds-btn-ghost` | Ghost (light green fill) |
| `.ds-btn-danger` | Delete / destructive |
| `.ds-btn-dashed` | Dashed border (e.g. "فرم کامل") |

**Example:**
```blade
<a href="..." class="ds-btn ds-btn-primary">ذخیره</a>
<a href="..." class="ds-btn ds-btn-secondary">انصراف</a>
<button type="submit" class="ds-btn ds-btn-primary">ارسال</button>
```

Use `@include('components._icons', ['name' => 'plus', 'class' => 'w-4 h-4'])` inside buttons for icons.

---

## Filter Tabs (Selectable Options)

For pipeline filters, status filters, type tabs (همه | جدید | تماس …):

```blade
<div class="ds-filter-tabs">
    <a href="{{ route('...', ['status' => null]) }}" class="{{ !request('status') ? 'ds-filter-active' : '' }}" style="{{ !request('status') ? 'background: #f5f5f4; color: #44403c;' : '' }}">همه</a>
    @foreach ($options as $st)
        <a href="{{ route('...', ['status' => $st]) }}" class="{{ request('status') === $st ? 'ds-filter-active' : '' }}"
           style="{{ request('status') === $st ? 'background: ' . Model::statusBgColor($st) . '; color: ' . Model::statusTextColor($st) . ';' : '' }}">{{ $label }}</a>
    @endforeach
</div>
```

- `.ds-filter-tabs` — Container (white, bordered, rounded)
- `.ds-filter-active` — Active tab (add inline `background` and `color` for status-specific colors)
- Inactive: gray text, hover = light gray bg

**Status colors (use `Lead::statusBgColor()`, `Lead::statusTextColor()` etc.):**
- همه: neutral `#f5f5f4` / `#44403c`
- جدید: sky `#e0f2fe` / `#075985`
- تماس: amber `#fffbeb` / `#92400e`
- جدی: violet `#f5f3ff` / `#6b21a8`
- پیشنهاد: blue `#dbeafe` / `#1e40af`
- بسته شد: emerald `#d1fae5` / `#065f46`
- رد شد: red `#fef2f2` / `#b91c1c`

---

## Inputs & Selects

```blade
<label for="id" class="ds-label">برچسب</label>
<input type="text" name="name" id="id" class="ds-input" placeholder="...">
<select name="x" id="x" class="ds-select">...</select>
<textarea name="y" class="ds-textarea">...</textarea>
```

- `.ds-input`, `.ds-select`, `.ds-textarea` — Unified input styles
- `.ds-label` — Label above input
- Focus: green border + subtle shadow

---

## Cards

```blade
<a href="..." class="ds-card">...</a>
<div class="ds-card ds-card-static">...</div>
```

- `.ds-card` — Clickable card (hover lift, shadow)
- `.ds-card-static` — Non-clickable (no hover transform)

---

## Form Sections

```blade
<div class="ds-form-card">
    <h2 class="ds-form-card-title">عنوان بخش</h2>
    {{-- form fields --}}
</div>
```

- `.ds-form-card` — Section with border, padding, shadow
- `.ds-form-card-title` — Section heading with bottom border

---

## Badges

```blade
<span class="ds-badge ds-badge-primary">وضعیت</span>
<span class="ds-badge ds-badge-amber">برچسب</span>
```

For dynamic status colors, use inline styles:
```blade
<span class="ds-badge" style="background: {{ Model::statusBgColor($status) }}; color: {{ Model::statusTextColor($status) }};">{{ $label }}</span>
```

---

## Empty State

```blade
<div class="ds-empty">
    <p>پیام خالی بودن لیست</p>
    <a href="..." class="ds-btn ds-btn-primary">اقدام پیشنهادی</a>
</div>
```

---

## Search Row

```blade
<div class="ds-search-row">
    <form class="ds-search-form" action="..." method="get">
        <input type="search" name="q" class="ds-input" placeholder="جستجو...">
        <button type="submit" class="ds-btn ds-btn-secondary">جستجو</button>
    </form>
    <a href="..." class="ds-btn ds-btn-dashed">فرم کامل</a>
</div>
```

---

## Alerts

```blade
<div class="ds-alert-success">{{ session('success') }}</div>
<div class="ds-alert-error">{{ session('error') }}</div>
```

---

## Chips / Selectable Tags

For tag pickers, checkbox rows:

```blade
<label class="ds-chip">
    <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}">
    <span style="width: 0.875rem; height: 0.875rem; border-radius: 0.25rem; background: {{ $tag->color }};"></span>
    <span>{{ $tag->name }}</span>
</label>
```

---

## Migration Checklist

When adding or refactoring a view:

1. Wrap content in `.ds-page`
2. Use `.ds-page-header`, `.ds-page-title`, `.ds-page-subtitle` for header
3. Use `.ds-btn ds-btn-primary` / `.ds-btn-secondary` for buttons
4. Use `.ds-filter-tabs` for filter tabs; add status colors via inline style when active
5. Use `.ds-input`, `.ds-select`, `.ds-label` for form fields
6. Use `.ds-card` for list items
7. Use `.ds-form-card` for form sections
8. Use `.ds-empty` for empty states
9. Use `.ds-badge` for status labels

---

## File Location

- **CSS:** `resources/views/components/_design-system.blade.php`
- **Layout:** Included in `layouts/app.blade.php` before `@stack('styles')`
- **Icons:** `@include('components._icons', ['name' => '…', 'class' => 'w-4 h-4'])`
