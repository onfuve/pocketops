@extends('layouts.app')

@section('title', 'کاربر جدید — ' . config('app.name'))

@section('content')
<div style="max-width: 52rem; margin: 0 auto; padding: 0 1rem; box-sizing: border-box;">
    <div style="margin-bottom: 1.5rem;">
        <h1 style="display: flex; align-items: center; gap: 0.75rem; margin: 0 0 0.25rem 0; font-size: 1.5rem; font-weight: 700; color: #292524;">
            <span style="display: flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; border-radius: 0.75rem; background: #dbeafe; color: #1e40af; border: 2px solid #93c5fd;">
                @include('components._icons', ['name' => 'user-plus', 'class' => 'w-5 h-5'])
            </span>
            کاربر جدید
        </h1>
    </div>

    <div style="padding: 1.5rem; border-radius: 1rem; border: 1px solid #e7e5e4; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
        <form action="{{ route('users.store') }}" method="post">
            @csrf
            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                <div>
                    <label for="name" style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.5rem;">نام <span style="color: #b91c1c;">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           style="width: 100%; box-sizing: border-box; padding: 0.625rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 1rem; color: #292524; background: #fff;">
                    @error('name')<p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #b91c1c;">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.5rem;">ایمیل <span style="color: #b91c1c;">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                           style="width: 100%; box-sizing: border-box; padding: 0.625rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 1rem; color: #292524; background: #fff;">
                    @error('email')<p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #b91c1c;">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.5rem;">رمز عبور <span style="color: #b91c1c;">*</span></label>
                    <input type="password" name="password" id="password" required
                           style="width: 100%; box-sizing: border-box; padding: 0.625rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 1rem; color: #292524; background: #fff;">
                    @error('password')<p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #b91c1c;">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.5rem;">تکرار رمز عبور <span style="color: #b91c1c;">*</span></label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                           style="width: 100%; box-sizing: border-box; padding: 0.625rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 1rem; color: #292524; background: #fff;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.5rem;">نقش</label>
                    <select name="role" style="width: 100%; box-sizing: border-box; padding: 0.625rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 1rem; color: #292524; background: #fff;">
                        <option value="team" {{ old('role', $user->role) === 'team' ? 'selected' : '' }}>عضو تیم</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>مدیر</option>
                    </select>
                    @error('role')<p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #b91c1c;">{{ $message }}</p>@enderror
                </div>
                <div style="border-top: 1px solid #e7e5e4; padding-top: 1rem;">
                    <p style="font-size: 0.875rem; font-weight: 600; color: #44403c; margin-bottom: 0.75rem;">مجوز حذف (فقط برای عضو تیم)</p>
                    <label style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <input type="hidden" name="can_delete_lead" value="0">
                        <input type="checkbox" name="can_delete_lead" value="1" {{ old('can_delete_lead', $user->can_delete_lead) ? 'checked' : '' }}>
                        <span>حذف سرنخ</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <input type="hidden" name="can_delete_contact" value="0">
                        <input type="checkbox" name="can_delete_contact" value="1" {{ old('can_delete_contact', $user->can_delete_contact) ? 'checked' : '' }}>
                        <span>حذف مخاطب</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="hidden" name="can_delete_invoice" value="0">
                        <input type="checkbox" name="can_delete_invoice" value="1" {{ old('can_delete_invoice', $user->can_delete_invoice) ? 'checked' : '' }}>
                        <span>حذف فاکتور</span>
                    </label>
                </div>
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-top: 0.5rem;">
                    <button type="submit" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: 0.5rem; background: #059669; color: #fff; font-size: 0.875rem; font-weight: 600; border: none; cursor: pointer;">
                        @include('components._icons', ['name' => 'check', 'class' => 'w-4 h-4'])
                        <span>ذخیره</span>
                    </button>
                    <a href="{{ route('users.index') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: 0.5rem; border: 2px solid #e7e5e4; background: #fff; color: #44403c; font-size: 0.875rem; font-weight: 500; text-decoration: none;">
                        <span>انصراف</span>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
