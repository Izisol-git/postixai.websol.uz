{{-- resources/views/pages/users/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Foydalanuvchilar — Postix AI')
@section('breadcrumbs')<a href="{{ route('dashboard') }}" style="color:var(--muted); text-decoration:none;">Asosiy</a> → <span style="color:var(--text)">Foydalanuvchilar</span>@endsection

@section('content')
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; color:var(--yellow);">Foydalanuvchilar</h3>
            <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary-custom">+ Add User</a>
        </div>

        <div style="margin-top:12px;">
            {{-- Paste your users list block here (the users-compact div) --}}

            {{-- Example placeholder: --}}
            @foreach($users ?? collect() as $user)
                <div class="user-line">
                    <div class="left"><div class="user-name">{{ $user->name }}</div><div class="user-telegram">({{ $user->telegram_id }})</div></div>
                    <div style="display:flex; gap:8px; align-items:center;"><a href="/users/{{ $user->id }}" class="btn btn-sm btn-primary-custom">Batafsil</a></div>
                </div>
            @endforeach

        </div>

        <div style="margin-top:12px;">{{ $users->links ?? '' }}</div>
    </div>
@endsection
