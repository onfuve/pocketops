@extends('layouts.app')

@section('title', 'کاربران — ' . config('app.name'))

@section('content')
<div style="max-width: 52rem; margin: 0 auto; padding: 0 1rem; box-sizing: border-box;">
    <div style="margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
        <div>
            <h1 style="display: flex; align-items: center; gap: 0.75rem; margin: 0 0 0.25rem 0; font-size: 1.5rem; font-weight: 700; color: #292524;">
                <span style="display: flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; border-radius: 0.75rem; background: #dbeafe; color: #1e40af; border: 2px solid #93c5fd;">
                    @include('components._icons', ['name' => 'users', 'class' => 'w-5 h-5'])
                </span>
                کاربران
            </h1>
            <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #78716c;">مدیریت کاربران، نقش‌ها و دسترسی‌ها.</p>
        </div>
        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem;">
            <a href="{{ route('settings.company') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 0.5rem; border: 2px solid #e7e5e4; background: #fff; color: #44403c; font-size: 0.875rem; font-weight: 500; text-decoration: none;">
                @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
                <span>تنظیمات شرکت</span>
            </a>
            <a href="{{ route('users.create') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 0.5rem; background: #059669; color: #fff; font-size: 0.875rem; font-weight: 600; text-decoration: none;">
            @include('components._icons', ['name' => 'plus', 'class' => 'w-4 h-4'])
            <span>کاربر جدید</span>
        </a>
        </div>
    </div>

    @if (session('success'))
        <div style="margin-bottom: 1rem; padding: 0.75rem 1rem; border-radius: 0.75rem; background: #ecfdf5; border: 2px solid #a7f3d0; color: #065f46; font-size: 0.875rem;">
            {{ session('success') }}
        </div>
    @endif

    <div style="display: grid; gap: 0.75rem;">
        @foreach ($users as $u)
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem; border-radius: 0.75rem; border: 1px solid #e7e5e4; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
                <div>
                    <span style="font-weight: 600; color: #292524; font-size: 1rem;">{{ $u->name }}</span>
                    <span style="color: #78716c; font-size: 0.875rem; margin-right: 0.5rem;">{{ $u->email }}</span>
                    <span style="display: inline-block; padding: 0.2rem 0.5rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 600; {{ $u->isAdmin() ? 'background: #dbeafe; color: #1e40af;' : 'background: #f5f5f4; color: #44403c;' }}">
                        {{ $u->isAdmin() ? 'مدیر' : 'عضو تیم' }}
                    </span>
                    @if (!$u->isAdmin())
                        <span style="font-size: 0.75rem; color: #78716c; margin-right: 0.5rem;">
                            حذف: {{ $u->can_delete_lead ? 'سرنخ ' : '' }}{{ $u->can_delete_contact ? 'مخاطب ' : '' }}{{ $u->can_delete_invoice ? 'فاکتور' : '' }}
                            @if (!$u->can_delete_lead && !$u->can_delete_contact && !$u->can_delete_invoice)
                                —
                            @endif
                        </span>
                    @endif
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <a href="{{ route('users.edit', $u) }}" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 0.5rem; border: 2px solid #e7e5e4; background: #fff; color: #44403c; font-size: 0.875rem; font-weight: 500; text-decoration: none;">
                        @include('components._icons', ['name' => 'pencil', 'class' => 'w-4 h-4'])
                        <span>ویرایش</span>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
