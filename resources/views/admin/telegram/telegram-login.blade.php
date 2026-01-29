{{-- resources/views/admin/telegram/telegram-login.blade.php --}}
@extends('layouts.app')
@section('show-back', true)
@section('title', __('messages.telegram.login') ?? 'Telegram Login')
@section('page-title', __('messages.telegram.login') ?? 'Telegram Login')

@section('content')
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

        <!-- Step 1: Full Input (keeps visible after send but locked) -->
        <div id="stepPhone">
          <label class="form-label">{{ __('messages.users.name') ?? 'Name' }}</label>
          <input id="name" type="text" name="name" class="form-control mb-2" placeholder="{{ __('messages.users.name') ?? 'Name' }}" required>
<div id="nameError" class="text-danger small d-none"></div>
          <label class="form-label">{{ __('messages.users.email') ?? 'Login (Email)' }}</label>
          <input id="login" type="text" name="login" class="form-control mb-2" placeholder="{{ __('messages.users.email') ?? 'Login (not required to be real email)' }}" required>
<div id="loginError" class="text-danger small d-none"></div>
          <label class="form-label">{{ __('messages.users.new_password') ?? 'Password' }}</label>
          <input id="password" type="password" name="password" class="form-control mb-2" placeholder="{{ __('messages.users.leave_empty') ?? 'Leave empty to keep' }}" required>
<div id="passwordError" class="text-danger small d-none"></div>
          @if(isset($roles) && $roles->count())
            <label class="form-label">{{ __('messages.users.role') ?? 'Role' }}</label>
            <select id="role" name="role" class="form-select mb-2">
              @foreach($roles as $role)
                <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
              @endforeach
            </select>
          @endif
<div id="roleError" class="text-danger small d-none"></div>
          <label class="form-label">{{ __('messages.users.avatar') ?? 'Avatar' }}</label>
          <input id="avatar" type="file" name="avatar" accept="image/*" class="form-control mb-2">
<div id="avatarError" class="text-danger small d-none"></div>
          <label class="form-label">{{ __('messages.telegram.phone_label') ?? 'Phone Number' }}</label>
          <input id="phone" type="text" class="form-control mb-2" autocomplete="tel" placeholder="{{ __('messages.telegram.phone_placeholder') ?? 'Enter users phone number' }}" value="" >
          <div id="phoneError" class="text-danger small d-none"></div>

          <button id="btnPhone" class="btn btn-primary w-100">{{ __('messages.telegram.send_sms') ?? 'Send SMS' }}</button>
        </div>

        <!-- Step 2: Code Input -->
        <div id="stepCode" class="d-none mt-3">
          <form method="POST" action="{{ route('admin.telegram.login') }}">
            @csrf
            <input type="hidden" name="phone" id="phoneHidden">
            <input type="hidden" name="login" id="loginHidden">
            <label class="form-label">{{ __('messages.telegram.code_label') ?? 'SMS Code' }}</label>
            <input name="code" id="code" type="text" class="form-control mb-3" placeholder="{{ __('messages.telegram.code_placeholder') ?? 'Enter SMS code' }}" autocomplete="one-time-code" required>

            <div id="codeError" class="text-danger small d-none"></div>

            <button type="submit" id="submitCodeBtn" class="btn btn-primary w-100">
              {{ __('messages.telegram.send_code') ?? 'Send Code' }}
            </button>
          </form>
        </div>

      </div>
    </div>
  </div>
</div>

<style>
/* spinner */
.spinner { display:inline-block; width:18px;height:18px;border:2px solid rgba(0, 0, 0, 0.1);border-left-color:transparent;border-radius:50%; animation: spin 0.8s linear infinite; vertical-align:middle; }
@keyframes spin{to{transform:rotate(360deg)}}

/* locked style: make fields visually distinct when locked */
.locked-field {
  opacity: 0.85;
  filter: saturate(0.85);
  background-clip: padding-box;
}

/* small badge shown after lock */
#lockedBadge {
  display:block;
  margin-top:10px;
  font-size:0.9rem;
  color: #6b7280;
}
</style>

<script>
(function(){
  const userId = @json(auth()->id() ?? null);
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';

  // localized messages rendered server-side
  const MSG = {
    required_name: {!! json_encode(__('validation.required', ['attribute' => __('validation.attributes.name')])) !!},
    required_login: {!! json_encode(__('validation.required', ['attribute' => __('validation.attributes.login')])) !!},
    required_password: {!! json_encode(__('validation.required', ['attribute' => __('validation.attributes.password')])) !!},
    required_role: {!! json_encode(__('validation.required', ['attribute' => __('validation.attributes.role_id')])) !!},
    required_phone: {!! json_encode(__('validation.required', ['attribute' => __('validation.attributes.phone')])) !!},
    phone_regex: {!! json_encode(trans('validation.custom.phone.regex')) !!},
    invalid_file: {!! json_encode(__('validation.uploaded') ) !!},
  };

  function showErrorElem(id, msg) {
    const el = document.getElementById(id);
    if (!el) return;
    el.innerText = msg;
    el.classList.remove('d-none');
  }
  function clearErrorElem(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.innerText = '';
    el.classList.add('d-none');
  }
  function clearAllErrors() {
    ['nameError','loginError','passwordError','roleError','avatarError','phoneError','codeError'].forEach(clearErrorElem);
  }

  function validateBeforeSend() {
    clearAllErrors();
    const name = document.getElementById('name')?.value.trim() || '';
    const login = document.getElementById('login')?.value.trim() || '';
    const password = document.getElementById('password')?.value.trim() || '';
    const role = document.getElementById('role')?.value || '';
    const avatarEl = document.getElementById('avatar');
    const phone = document.getElementById('phone')?.value.trim() || '';

    // required checks
    if (!name) { showErrorElem('nameError', MSG.required_name); document.getElementById('name').focus(); return false; }
    if (!login) { showErrorElem('loginError', MSG.required_login); document.getElementById('login').focus(); return false; }
    if (!password) { showErrorElem('passwordError', MSG.required_password); document.getElementById('password').focus(); return false; }
    if (!role) { showErrorElem('roleError', MSG.required_role); document.getElementById('role').focus(); return false; }
    if (!phone) { showErrorElem('phoneError', MSG.required_phone); document.getElementById('phone').focus(); return false; }

    // phone format quick client-side (uz format) — keep simple: +998XXXXXXXXX or digits
    const normalized = phone.replace(/[^0-9+]/g,'');
    if (!/^\+?998\d{9}$/.test(normalized)) {
      showErrorElem('phoneError', MSG.phone_regex || 'Phone format invalid');
      document.getElementById('phone').focus(); return false;
    }

    // optional: avatar size/type check (example: max 5MB)
    if (avatarEl && avatarEl.files && avatarEl.files[0]) {
      const file = avatarEl.files[0];
      const maxBytes = 5 * 1024 * 1024;
      if (file.size > maxBytes) {
        showErrorElem('avatarError', MSG.invalid_file);
        avatarEl.focus(); return false;
      }
    }

    return true;
  }

  function show(el, msg, hideAfter=3500){
    el.innerText = msg;
    el.classList.remove('d-none');
    if (hideAfter) setTimeout(()=>el.classList.add('d-none'), hideAfter);
  }
  function showSuccess(msg){ show(document.getElementById('alertSuccess'), msg, 3500); }
  function showError(msg){ show(document.getElementById('alertError'), msg, 5000); }

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

  function lockInputs() {
    const ids = ['name','login','password','role','avatar','phone'];
    ids.forEach(id => {
      const el = document.getElementById(id);
      if (!el) return;
      try { el.disabled = true; } catch(e){}
      el.classList.add('locked-field');
      el.setAttribute('aria-readonly', 'true');
    });
  }

  document.getElementById('btnPhone').addEventListener('click', function(){
    clearAllErrors();

    if (!validateBeforeSend()) return;

    const phone = document.getElementById('phone').value.trim();
    const btn = this;
    loading(btn, true);

    const formData = new FormData();
    formData.append('phone', phone);
    formData.append('user_id', userId || '');
    formData.append('name', document.getElementById('name')?.value || '');
    formData.append('login', document.getElementById('login')?.value || '');
    formData.append('password', document.getElementById('password')?.value || '');
    formData.append('role_id', document.getElementById('role')?.value || '');

    const avatarInput = document.getElementById('avatar');
    if (avatarInput && avatarInput.files && avatarInput.files[0]) {
      formData.append('avatar', avatarInput.files[0]);
    }

    fetch("{{ route('admin.telegram.send') }}", {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'X-CSRF-TOKEN': csrf },
      body: formData
    })
    .then(async res => {
      loading(btn,false);
      const contentType = res.headers.get('content-type') || '';
      if (!res.ok) {
        if (contentType.includes('application/json')) {
          const json = await res.json().catch(()=>null);
          if (json && json.errors) {
            // display server-side validation errors per-field if present
            Object.keys(json.errors).forEach(key => {
              const fieldMap = {
                name: 'nameError',
                login: 'loginError',
                password: 'passwordError',
                role_id: 'roleError',
                role: 'roleError',
                phone: 'phoneError',
                avatar: 'avatarError'
              };
              const elId = fieldMap[key] || (key + 'Error');
              const msgs = Array.isArray(json.errors[key]) ? json.errors[key].join(' ') : json.errors[key];
              showErrorElem(elId, msgs);
            });
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
      if (data.status === 'sms_sent' || data.status === 'sent') {
        lockInputs();
        // hide send button completely
        btn.style.display = 'none';

        // add Change button if not exists
        if (!document.getElementById('btnChangePhone')) {
          const change = document.createElement('button');
          change.id = 'btnChangePhone';
          change.type = 'button';
          change.className = 'btn btn-ghost w-100 mt-2';
          change.textContent = '{{ __("messages.users.change_phone") ?? "Change phone" }}';
          change.addEventListener('click', function(){
            // allow editing again
            const ids = ['name','login','password','role','avatar','phone'];
            ids.forEach(id => {
              const el = document.getElementById(id);
              if (!el) return;
              try { el.disabled = false; } catch(e){}
              el.classList.remove('locked-field');
              el.removeAttribute('aria-readonly');
            });
            // show send button again
            btn.style.display = '';
            document.getElementById('stepCode').classList.add('d-none');
            document.getElementById('phoneHidden').value = '';
            document.getElementById('loginHidden').value = '';
            document.getElementById('phone').focus();
          });
          const stepPhone = document.getElementById('stepPhone');
          stepPhone.appendChild(change);
        }

        showSuccess(data.message || '{{ __("messages.telegram.sms_sent") ?? "SMS yuborildi" }}');

        document.getElementById('phoneHidden').value = phone;
        const loginValue = document.getElementById('login')?.value || '';
document.getElementById('loginHidden').value = loginValue;
        document.getElementById('stepCode').classList.remove('d-none');
        document.getElementById('code').focus();
      } else {
        showError(data.message || 'Xato');
      }
    })
    .catch(err => console.error(err));
  });

  // Enter key usability
  document.getElementById('phone')?.addEventListener('keydown', function(e){
    if (e.key === 'Enter') {
      e.preventDefault();
      document.getElementById('btnPhone').click();
    }
  });

})();
</script>


@endsection
