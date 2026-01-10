{{-- resources/views/admin/telegram/telegram-login.blade.php --}}
@extends('layouts.app')

@section('title', __('messages.telegram.login') ?? 'Telegram Login')
@section('page-title', __('messages.telegram.login') ?? 'Telegram Login')

@section('content')
<!-- ensure meta csrf-token is present so JS can read it even if layout misses it -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card p-4 auth-box">
                <h4 class="text-center mb-3">{{ __('messages.telegram.login') ?? "Telegram bilan bog'lash" }}</h4>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div id="alertSuccess" class="alert alert-success d-none" role="alert"></div>
                <div id="alertError" class="alert alert-danger d-none" role="alert"></div>
                <div id="alertNeutral" class="alert alert-secondary d-none" role="alert"></div>

                <!-- Step 1: Phone Input -->
                <div id="stepPhone">
                    <label class="form-label">{{ __('messages.telegram.phone_label') ?? 'Phone Number' }}</label>
                    <input id="phone" type="text" class="form-control mb-3" autocomplete="tel" placeholder="{{ __('messages.telegram.phone_placeholder') ?? 'Enter users phone number' }}" value="{{ old('phone', '') }}">
                    <div id="phoneError" class="text-danger small d-none"></div>
                    <button id="btnPhone" class="btn btn-primary w-100">{{ __('messages.telegram.send_sms') ?? 'Send SMS' }}</button>
                </div>

                <!-- Step 2: Code Input -->
                <!-- Step 2: Code Input (NORMAL POST) -->
<div id="stepCode" class="d-none mt-3">
    <form method="POST" action="{{ route('admin.telegram.login') }}">
        @csrf

        <!-- phone yashirin input -->
        <input type="hidden" name="phone" id="phoneHidden">

        <label class="form-label">
            {{ __('messages.telegram.code_label') ?? 'SMS Code' }}
        </label>

        <input
            name="code"
            id="code"
            type="text"
            class="form-control mb-3"
            placeholder="{{ __('messages.telegram.code_placeholder') ?? 'Enter SMS code' }}"
            autocomplete="one-time-code"
            required
        >

        <button type="submit" class="btn btn-primary w-100">
            {{ __('messages.telegram.send_code') ?? 'Send Code' }}
        </button>
    </form>
</div>

            </div>
        </div>
    </div>
</div>

<style>
/* small spinner for the button */
.spinner {
  display:inline-block;
  width:18px;height:18px;border:2px solid rgba(0,0,0,0.1);border-left-color:transparent;border-radius:50%;
  animation: spin 0.8s linear infinite;
  vertical-align:middle;
}
@keyframes spin{to{transform:rotate(360deg)}}
</style>

<script>
(function(){
    const userId = @json(auth()->id() ?? null);
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';

    function show(el, msg, hideAfter=3500){
        el.innerText = msg;
        el.classList.remove('d-none');
        if (hideAfter) setTimeout(()=>el.classList.add('d-none'), hideAfter);
    }
    function showSuccess(msg){ show(document.getElementById('alertSuccess'), msg, 3500); }
    function showError(msg){ show(document.getElementById('alertError'), msg, 5000); }
    function showNeutral(msg){ const el=document.getElementById('alertNeutral'); el.innerText=msg; el.classList.remove('d-none'); }
    function hideNeutral(){ document.getElementById('alertNeutral').classList.add('d-none'); }

    function loading(btn,state){
        if(state){
            btn.dataset.original = btn.innerHTML;
            btn.innerHTML = '<span class="spinner"></span>';
            btn.disabled = true;
        } else {
            btn.innerHTML = btn.dataset.original || btn.innerHTML;
            btn.disabled = false;
        }
    }

    // sendPhone
    document.getElementById('btnPhone').addEventListener('click', function(){
        const phone = document.getElementById('phone').value.trim();
        const phoneError = document.getElementById('phoneError');
        phoneError.classList.add('d-none'); phoneError.innerText = '';

        if(!phone){
            phoneError.innerText = '{{ __("messages.telegram.phone_required") ?? "Telefon kiritilishi shart" }}';
            phoneError.classList.remove('d-none');
            return;
        }

        const btn = this;
        loading(btn, true);

        fetch("{{ route('admin.telegram.send') }}", {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrf, 'Accept':'application/json' },
            body: JSON.stringify({ phone: phone, user_id: userId })
        })
        .then(async res => {
            loading(btn,false);
            const contentType = res.headers.get('content-type') || '';
            if (!res.ok) {
                if (contentType.includes('application/json')) {
                    const json = await res.json().catch(()=>null);
                    if (json && json.errors && json.errors.phone) {
                        phoneError.innerText = json.errors.phone.join(', ');
                        phoneError.classList.remove('d-none');
                    } else {
                        showError(json?.message || 'Xatolik yuz berdi');
                    }
                } else {
                    const txt = await res.text().catch(()=>null);
                    showError(txt || 'Server xatosi (non-json)');
                }
                throw new Error('sendPhone failed');
            }
            return res.json();
        })
        .then(data => {
            if (data.status === 'sms_sent') {
                showSuccess(data.message || '{{ __("messages.telegram.sms_sent") ?? "SMS yuborildi" }}');
                document.getElementById('phoneHidden').value = phone;
                document.getElementById('stepPhone').classList.add('d-none');
                document.getElementById('stepCode').classList.remove('d-none');
                document.getElementById('code').focus();
            } else {
                showError(data.message || 'Xato');
            }
        })
        .catch(err => console.error(err));
    });

    // sendCode
    document.getElementById('btnCode').addEventListener('click', function(){
        const phone = document.getElementById('phone').value.trim();
        const code = document.getElementById('code').value.trim();
        const codeError = document.getElementById('codeError');
        codeError.classList.add('d-none'); codeError.innerText = '';

        if(!phone || !code){
            codeError.innerText = '{{ __("messages.telegram.code_required") ?? "Telefon va kod kiritilishi kerak" }}';
            codeError.classList.remove('d-none');
            return;
        }

        const btn = this;
        loading(btn, true);
        showNeutral('{{ __("messages.telegram.verifying") ?? "Tasdiqlanmoqda, kuting..." }}');

        fetch("{{ route('admin.telegram.login') }}", {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrf, 'Accept':'application/json' },
            body: JSON.stringify({ phone: phone, code: code, user_id: userId })
        })
        .then(async res => {
            loading(btn,false);
            hideNeutral();
            const contentType = res.headers.get('content-type') || '';
            if (!res.ok) {
                if (contentType.includes('application/json')) {
                    const json = await res.json().catch(()=>null);
                    if (json && json.errors) {
                        let msg = '';
                        if (json.errors.code) msg = json.errors.code.join(', ');
                        if (json.errors.phone) msg += ' ' + json.errors.phone.join(', ');
                        if (!msg && json.message) msg = json.message;
                        codeError.innerText = msg || 'Xatolik';
                        codeError.classList.remove('d-none');
                    } else {
                        showError(json?.message || 'Server xatosi');
                    }
                } else {
                    const txt = await res.text().catch(()=>null);
                    showError(txt || 'Server xatosi (non-json)');
                }
                throw new Error('sendCode failed');
            }
            return res.json();
        })
        .then(data => {
            if (data.status === 'verified') {
                showSuccess(data.message || '{{ __("messages.telegram.verified") ?? "Verified" }}');
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    window.location.reload();
                }
            } else {
                showError(data.message || 'Tasdiqlash muvaffaqiyatsiz');
            }
        })
        .catch(err => console.error(err));
    });

})();
</script>
@endsection
