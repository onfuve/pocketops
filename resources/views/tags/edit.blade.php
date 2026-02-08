@extends('layouts.app')

@section('title', 'ویرایش برچسب — ' . config('app.name'))

@section('content')
<div style="max-width: 52rem; margin: 0 auto; padding: 0 1rem; box-sizing: border-box;">
    <div style="margin-bottom: 1.5rem;">
        <h1 style="display: flex; align-items: center; gap: 0.75rem; margin: 0 0 0.25rem 0; font-size: 1.5rem; font-weight: 700; color: #292524;">
            <span style="display: flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; border-radius: 0.75rem; background: #dbeafe; color: #1e40af; border: 2px solid #93c5fd;">
                @include('components._icons', ['name' => 'tag', 'class' => 'w-5 h-5'])
            </span>
            ویرایش برچسب
        </h1>
    </div>

    @if (session('error'))
        <div style="margin-bottom: 1rem; padding: 0.75rem 1rem; border-radius: 0.75rem; background: #fef2f2; border: 2px solid #fecaca; color: #b91c1c; font-size: 0.875rem;">
            {{ session('error') }}
        </div>
    @endif

    <div style="padding: 1.5rem; border-radius: 1rem; border: 1px solid #e7e5e4; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
        <form action="{{ route('tags.update', $tag) }}" method="post">
            @csrf
            @method('PUT')
            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                <div>
                    <label for="name" style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.5rem;">نام برچسب <span style="color: #b91c1c;">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $tag->name) }}" required placeholder="مثلاً VIP، مهم، فوری"
                           style="width: 100%; box-sizing: border-box; padding: 0.625rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 1rem; color: #292524; background: #fff;">
                    @error('name')
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #b91c1c;">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="color" style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.5rem;">رنگ <span style="color: #b91c1c;">*</span></label>
                    <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                        <input type="color" name="color" id="color" value="{{ old('color', $tag->color) }}" required
                               style="width: 4rem; height: 3rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; cursor: pointer;">
                        <input type="text" name="color_text" id="color_text" value="{{ old('color', $tag->color) }}" pattern="^#[0-9A-Fa-f]{6}$" placeholder="#059669"
                               style="width: 8rem; box-sizing: border-box; padding: 0.625rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 1rem; color: #292524; background: #fff; font-family: monospace;">
                    </div>
                    @error('color')
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #b91c1c;">{{ $message }}</p>
                    @enderror
                </div>

                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-top: 0.5rem;">
                    <button type="submit" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: 0.5rem; background: #059669; color: #fff; font-size: 0.875rem; font-weight: 600; border: none; cursor: pointer;">
                        @include('components._icons', ['name' => 'check', 'class' => 'w-4 h-4'])
                        <span>ذخیره</span>
                    </button>
                    <a href="{{ route('tags.index') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: 0.5rem; border: 2px solid #e7e5e4; background: #fff; color: #44403c; font-size: 0.875rem; font-weight: 500; text-decoration: none;">
                        <span>انصراف</span>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var colorInput = document.getElementById('color');
    var colorText = document.getElementById('color_text');
    if (!colorInput || !colorText) return;
    
    colorInput.addEventListener('input', function() {
        colorText.value = colorInput.value.toUpperCase();
    });
    
    colorText.addEventListener('input', function() {
        var val = colorText.value.trim();
        if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
            colorInput.value = val;
        }
    });
    
    colorText.addEventListener('blur', function() {
        var val = colorText.value.trim();
        if (!/^#[0-9A-Fa-f]{6}$/.test(val)) {
            colorText.value = colorInput.value.toUpperCase();
        }
    });
})();
</script>
@endpush
@endsection
