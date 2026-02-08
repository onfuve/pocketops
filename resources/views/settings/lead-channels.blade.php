@extends('layouts.app')

@section('title', 'کانال‌های ورود سرنخ — ' . config('app.name'))

@section('content')
<div style="max-width: 52rem; margin: 0 auto; padding: 0 1rem; box-sizing: border-box; font-family: 'Vazirmatn', sans-serif;">
    <div style="margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
        <div>
            <h1 style="display: flex; align-items: center; gap: 0.75rem; margin: 0 0 0.25rem 0; font-size: 1.5rem; font-weight: 700; color: #292524;">
                <span style="display: flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; border-radius: 0.75rem; background: #e0f2fe; color: #0369a1; border: 2px solid #bae6fd;">
                    @include('components._icons', ['name' => 'lightbulb', 'class' => 'w-5 h-5'])
                </span>
                کانال‌های ورود سرنخ
            </h1>
            <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #78716c;">تلفن، معرف، وب‌سایت، تبلیغات و غیره. در فرم سرنخ از این لیست انتخاب می‌شود.</p>
        </div>
        <a href="{{ route('leads.index') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 0.75rem; border: 2px solid #e7e5e4; background: #fff; color: #44403c; font-size: 0.875rem; font-weight: 500; text-decoration: none; min-height: 44px; align-items: center;" onmouseover="this.style.borderColor='#d6d3d1';this.style.backgroundColor='#fafaf9';" onmouseout="this.style.borderColor='#e7e5e4';this.style.backgroundColor='#fff';">
            @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
            <span>بازگشت به سرنخ‌ها</span>
        </a>
    </div>

    @if (session('success'))
        <div style="margin-bottom: 1rem; padding: 0.75rem 1rem; border-radius: 0.75rem; background: #ecfdf5; border: 2px solid #a7f3d0; color: #065f46; font-size: 0.875rem;">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div style="margin-bottom: 1rem; padding: 0.75rem 1rem; border-radius: 0.75rem; background: #fef2f2; border: 2px solid #fecaca; color: #b91c1c; font-size: 0.875rem;">
            <ul style="margin: 0; padding-right: 1.25rem; list-style: disc;">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="padding: 1.5rem; border-radius: 1rem; border: 2px solid #e7e5e4; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.06); margin-bottom: 1.5rem;">
        <h2 style="border-bottom: 2px solid #e7e5e4; padding-bottom: 0.75rem; margin-bottom: 1rem; font-size: 1rem; font-weight: 600; color: #292524;">افزودن کانال جدید</h2>
        <form action="{{ route('settings.lead-channels.store') }}" method="post">
            <div style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 1rem; margin-bottom: 1rem;">
                <div style="flex: 1; min-width: 12rem;">
                    <label for="name" style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.5rem;">نام کانال <span style="color: #b91c1c;">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="مثلاً تلفن، معرف، وب‌سایت"
                           style="width: 100%; box-sizing: border-box; padding: 0.625rem 0.75rem; border: 2px solid {{ $errors->has('name') ? '#f87171' : '#d6d3d1' }}; border-radius: 0.5rem; font-size: 1rem; color: #292524; background: #fff; font-family: 'Vazirmatn', sans-serif;">
                    @error('name')
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #b91c1c;">{{ $message }}</p>
                    @enderror
                </div>
                <label style="display: flex; align-items: center; gap: 0.5rem; min-height: 44px; cursor: pointer; padding: 0.5rem 0;">
                    <input type="hidden" name="is_referral" value="0">
                    <input type="checkbox" name="is_referral" value="1" {{ old('is_referral') ? 'checked' : '' }} style="width: 1.125rem; height: 1.125rem; accent-color: #059669;">
                    <span style="font-size: 0.875rem; font-weight: 500; color: #44403c;">این کانال «معرف» است — لینک به مخاطب معرف</span>
                </label>
                <button type="submit" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: 0.5rem; background: #059669; color: #fff; font-size: 0.875rem; font-weight: 600; border: none; cursor: pointer; font-family: 'Vazirmatn', sans-serif;" onmouseover="this.style.background='#047857';" onmouseout="this.style.background='#059669';">
                    @include('components._icons', ['name' => 'plus', 'class' => 'w-4 h-4'])
                    <span>افزودن کانال</span>
                </button>
            </div>
        </form>
    </div>

    @if ($channels->isEmpty())
        <div style="padding: 2rem; text-align: center; background: #fff; border: 2px solid #e7e5e4; border-radius: 1rem;">
            <p style="margin: 0 0 0.5rem 0; color: #78716c;">هنوز کانالی ثبت نشده است.</p>
            <p style="margin: 0; font-size: 0.875rem; color: #57534e;">از فرم بالا یک کانال اضافه کنید. کانال‌های پیش‌فرض با اولین مهاجرت ایجاد شده‌اند.</p>
        </div>
    @else
        <div style="display: grid; gap: 0.75rem;">
            @foreach ($channels as $ch)
                <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem; border-radius: 0.75rem; border: 1px solid #e7e5e4; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
                    <div style="display: flex; align-items: center; gap: 0.75rem; min-width: 0; flex: 1;">
                        <span style="font-weight: 600; color: #292524; font-size: 1rem;">{{ $ch->name }}</span>
                        @if ($ch->is_referral)
                            <span style="display: inline-block; padding: 0.2rem 0.5rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 600; background: #f5f3ff; color: #6b21a8; border: 1px solid #e9d5ff;">معرف</span>
                        @endif
                    </div>
                    <form action="{{ route('settings.lead-channels.destroy', $ch) }}" method="post" style="display: inline;" onsubmit="return confirm('این کانال حذف شود؟');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 0.5rem; border: 2px solid #fecaca; background: #fff; color: #b91c1c; font-size: 0.875rem; font-weight: 500; cursor: pointer; font-family: 'Vazirmatn', sans-serif;" onmouseover="this.style.backgroundColor='#fef2f2';this.style.borderColor='#fca5a5';" onmouseout="this.style.backgroundColor='#fff';this.style.borderColor='#fecaca';">
                            @include('components._icons', ['name' => 'trash', 'class' => 'w-4 h-4'])
                            <span>حذف</span>
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
