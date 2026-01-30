@extends('admin.layouts.app')

@section('title', ($department->name ?? __('messages.department')) . ' — ' . __('messages.admin.dashboard'))
@section('page-title', $department->name ?? __('messages.department'))
@section('show-sidebar','true')

@section('content')
<div class="card p-3 mb-3">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
            <h3 class="mb-0">{{ $department->name }} — {{ __('messages.admin.dashboard') }}</h3>
            <div class="small muted-small">ID: {{ $department->id }}</div>
        </div>

        @php
            // Server-provided ban and banMeta (controller should send banMeta array)
            $departmentBan = $ban ?? null;
            $banMeta = $banMeta ?? ['isBannedActive' => false, 'isScheduled' => false, 'startsAt' => null, 'untilAt' => null];

            $isBannedActive = $banMeta['isBannedActive'];
            $isScheduled = $banMeta['isScheduled'];
            $startsAt = $banMeta['startsAt'];
            $untilAt = $banMeta['untilAt'];

            // i18n strings for JS (will be emitted as JSON)
            $i18n = [
                'banned_since' => __('messages.ban.banned_since'),
                'until' => __('messages.ban.until'),
                'scheduled' => __('messages.ban.scheduled'),
                'date_optional' => __('messages.ban.date_optional'),
                'sure' => __('messages.ban.sure?'),
                'confirm' => __('messages.ban.confirm'),
                'unban' => __('messages.ban.unban'),
                'ban' => __('messages.ban.ban'),
                'ban_now' => __('messages.ban.banned_now') ?: __('messages.ban.ban'),
            ];
        @endphp

        <span id="department-status-{{ $department->id }}" class="muted-small" style="font-size:13px;">
            @if ($isBannedActive)
                🔒 {{ $i18n['banned_since'] ?: __('messages.ban.banned_since') }}: {{ $startsAt ?? '' }}
                @if($untilAt)
                    — {{ $i18n['until'] ?: __('messages.ban.until') }}: {{ $untilAt }}
                @endif
            @elseif ($isScheduled && $startsAt)
                ⏰ {{ $i18n['scheduled'] ?: __('messages.ban.scheduled') }}: {{ $startsAt }}
            @endif
        </span>

        <button id="department-btn-{{ $department->id }}"
                class="btn btn-sm department-ban-btn {{ $isBannedActive ? 'btn-success' : 'btn-danger' }}"
                data-type="department"
                data-id="{{ $department->id }}"
                data-banned="{{ $isBannedActive ? '1' : '0' }}"
                data-starts_at="{{ $startsAt ?? '' }}"
                data-until="{{ $untilAt ?? '' }}">
            {!! $isBannedActive ? ($i18n['unban']) : ($i18n['ban']) !!}
        </button>

        @if (auth()->check() && auth()->user()->role?->name === 'superadmin')
            <a href="{{ route('departments.edit', $department->id) }}" class="btn btn-sm btn-warning">
                ✏️ {{ __('messages.admin.edit') ?? 'Edit' }}
            </a>

            @if (is_null($department->deleted_at))
                <button class="btn btn-sm btn-danger js-confirm-action"
                        data-action="{{ route('departments.destroy', $department->id) }}"
                        data-method="DELETE"
                        data-verb="{{ __('messages.departments.delete') ?? 'Delete department' }}"
                        data-require-name="{{ $department->name }}">
                    🗑 {{ __('messages.admin.delete') ?? 'Delete' }}
                </button>
            @endif
        @endif
    </div>

    <div class="row mt-3">
        <div class="col-sm-6 col-md-3 mb-2">
            <div class="card p-3">
                <div class="muted-small">{{ __('messages.admin.users') }}</div>
                <div class="h4 mb-0">{{ $usersCount ?? 0 }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3 mb-2">
            <div class="card p-3">
                <div class="muted-small">{{ __('messages.admin.active') }}</div>
                <div class="h4 mb-0">{{ $activePhonesCount ?? 0 }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3 mb-2">
            <div class="card p-3">
                <div class="muted-small">{{ __('messages.admin.operations') }}</div>
                <div class="h4 mb-0">{{ $messageGroupsTotal ?? 0 }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3 mb-2">
            <div class="card p-3">
                <div class="muted-small">{{ __('messages.admin.messages_count') }}</div>
                <div class="h4 mb-0">{{ $telegramMessagesTotal ?? 0 }}</div>
            </div>
        </div>
    </div>

    @if(!empty($recentMessagesByGroup))
        <div class="mt-3">
            <h5>{{ __('messages.recent_groups') }}</h5>
            @foreach($recentMessagesByGroup as $gid => $rows)
                <div class="mt-2">
                    <strong>{{ __('messages.group') }} #{{ $gid }}</strong>
                    <div class="small muted-small">
                        {!! implode(', ', $rows->pluck('message_text')->take(5)->map(fn($t)=>e(\Illuminate\Support\Str::limit($t,60)))->toArray()) !!}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- I18N for JS -->
<script>
    const I18N = @json($i18n, JSON_UNESCAPED_UNICODE);
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    function getCsrf() {
        const m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.content : (window.Laravel && window.Laravel.csrfToken) || '';
    }
    const CSRF = getCsrf();

    function setBtnLoading(btn, on) {
        if (!btn) return;
        if (on) {
            btn.dataset._orig = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        } else {
            btn.disabled = false;
            if (btn.dataset._orig) btn.innerHTML = btn.dataset._orig;
        }
    }

    async function deleteDepartment(btn) {
        const action = btn.dataset.action;
        const method = (btn.dataset.method || 'POST').toUpperCase();
        const requireName = btn.dataset.requireName || btn.dataset.require_name || null;

        if (!action) return;

        // create modal overlay
        const overlay = document.createElement('div');
        Object.assign(overlay.style, {
            position: 'fixed', inset: '0', background: 'rgba(0,0,0,0.6)',
            display: 'flex', justifyContent: 'center', alignItems: 'center', zIndex: 2147483647
        });

        const card = document.createElement('div');
        Object.assign(card.style, {
            background: 'var(--card,#0f2233)', color: 'var(--text,#fff)',
            padding: '20px', borderRadius: '12px', maxWidth: '400px', width: '90%',
            boxShadow: '0 10px 40px rgba(0,0,0,0.6)', textAlign: 'center', fontFamily: 'Inter, system-ui'
        });

        card.innerHTML = `
            <h3 style="margin-bottom:12px">🗑 {{ __('messages.admin.delete') ?? 'Delete Department' }}</h3>
            <p style="font-size:14px; color:var(--muted,#9fb7dd); margin-bottom:20px">
                {{ __('messages.departments.delete_confirm') ?? 'Siz haqiqatan ham o‘chirmoqchimisiz? Bu amal qaytarilmaydi.' }}
            </p>
            <div style="display:flex; justify-content:center; gap:10px;">
                <button id="js-del-cancel" style="padding:8px 14px; border-radius:8px; border:1px solid #999; background:transparent; color:#fff; cursor:pointer">Bekor</button>
                <button id="js-del-confirm" style="padding:8px 14px; border-radius:8px; border:none; background:#ef4444; color:#fff; cursor:pointer">O‘chirish</button>
            </div>
        `;

        overlay.appendChild(card);
        document.body.appendChild(overlay);

        function removeModal() { overlay.remove(); }
        overlay.addEventListener('click', (e)=>{ if(e.target===overlay) removeModal(); });
        document.addEventListener('keydown', function escHandler(e){
            if(e.key==='Escape'){ removeModal(); document.removeEventListener('keydown', escHandler); }
        });

        const btnCancel = card.querySelector('#js-del-cancel');
        const btnConfirm = card.querySelector('#js-del-confirm');

        btnCancel.addEventListener('click', removeModal);

        btnConfirm.addEventListener('click', async () => {
            setBtnLoading(btnConfirm,true);
            try {
                const res = await fetch(action, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    credentials: 'same-origin'
                });

                if (!res.ok) {
                    const txt = await res.text();
                    let msg = txt;
                    try { msg = JSON.parse(txt).message || txt; } catch(e){}
                    alert('Server xatosi: ' + (msg || res.status));
                    setBtnLoading(btnConfirm,false);
                    return;
                }

                let json = {};
                try { json = await res.json(); } catch(e){}
                removeModal();

                if(json.redirect){
                    window.location.href = json.redirect;
                } else {
                    // default: refresh page
                    window.location.reload();
                }

            } catch (err) {
                console.error(err);
                alert('Tarmoq bilan muammo. Console-ni tekshiring.');
                setBtnLoading(btnConfirm,false);
            }
        });
    }

    document.body.addEventListener('click', function(e){
        const btn = e.target.closest('.js-confirm-action');
        if(!btn) return;
        e.preventDefault();
        deleteDepartment(btn);
    });

});
</script>

<!-- Dynamic Ban/Schedule Modal (created by JS) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    console.log('[ban] script init');

    // CSRF: multi-fallback
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && meta.content) return meta.content;
        const hidden = document.querySelector('input[name="_token"]') || document.getElementById('csrf_token');
        if (hidden && hidden.value) return hidden.value;
        if (window.Laravel && window.Laravel.csrfToken) return window.Laravel.csrfToken;
        console.warn('[ban] CSRF token not found');
        return '';
    }
    const CSRF = getCsrfToken();

    function showToast(message, type = 'success') {
        let root = document.getElementById('js-toast-root');
        if (!root) {
            root = document.createElement('div');
            root.id = 'js-toast-root';
            Object.assign(root.style, { position: 'fixed', top: '18px', right: '18px', zIndex: 2147483647 });
            document.body.appendChild(root);
        }
        const t = document.createElement('div');
        t.textContent = message;
        Object.assign(t.style, {
            background: type === 'success' ? '#16a34a' : '#ef4444',
            color: '#fff',
            padding: '8px 12px',
            borderRadius: '8px',
            marginTop: '8px',
            boxShadow: '0 8px 24px rgba(2,6,23,0.3)',
            fontFamily: 'Inter, system-ui, Arial',
            fontSize: '13px'
        });
        root.appendChild(t);
        setTimeout(() => { t.style.opacity = '0'; t.remove(); if (!root.children.length) root.remove(); }, 3500);
    }

    function setBtnLoading(btn, on) {
        if (!btn) return;
        if (on) {
            btn.dataset._orig = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        } else {
            btn.disabled = false;
            if (btn.dataset._orig) btn.innerHTML = btn.dataset._orig;
        }
    }

    async function doBanAction(type, id, action = null, starts_at = null, until = null) {
        const payload = { bannable_type: type, bannable_id: Number(id) };
        if (action) payload.action = action;
        if (starts_at) payload.starts_at = starts_at;
        if (until) payload.until = until;

        console.log('[ban] payload ->', payload);

        try {
            const res = await fetch('/admin/ban-unban', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            });

            const text = await res.text();
            let json = {};
            try {
                json = text ? JSON.parse(text) : {};
            } catch (err) {
                console.warn('[ban] response is not JSON:', text);
                json = { raw: text };
            }

            if (!res.ok) {
                const msg = (json && (json.message || json.error)) || `Server responded ${res.status}`;
                console.error('[ban] non-ok', res.status, json);
                return { ok: false, json, message: msg, status: res.status };
            }

            const okFlag = (json && (json.success === true || String(json.status).toLowerCase() === 'success')) || true;
            const message = json.message || (okFlag ? (I18N.ban_now || 'Success') : 'Error');
            return { ok: true, json, message };
        } catch (err) {
            console.error('[ban] fetch error', err);
            return { ok: false, json: null, message: 'Server bilan aloqa yoʻq. Console.log ni tekshiring.' };
        }
    }

    function formatForServerFromInputValue(inputVal) {
        if (!inputVal) return null;
        return inputVal.replace('T', ' ') + ':00';
    }

    function updateUIFromResponse(type, id, serverJson) {
        if (!serverJson) return;
        const payload = serverJson.data ?? serverJson;
        const is_banned = (typeof payload.is_banned !== 'undefined') ? payload.is_banned : (payload?.data?.is_banned ?? null);
        const starts_at = payload.starts_at ?? payload?.data?.starts_at ?? null;
        const until = payload.until ?? payload?.data?.until ?? null;

        const btn = document.querySelector(`[data-type="${type}"][data-id="${id}"]`);
        const status = document.getElementById(`${type}-status-${id}`);

        if (btn) {
            if (is_banned === true) {
                btn.dataset.banned = '1';
                btn.classList.remove('btn-danger'); btn.classList.add('btn-success');
                btn.innerHTML = I18N.unban || '🔓 Unban';
            } else {
                btn.dataset.banned = '0';
                btn.classList.remove('btn-success'); btn.classList.add('btn-danger');
                btn.innerHTML = I18N.ban || '🛑 Ban';
            }
        }

        if (status) {
            if (is_banned === true) {
                let text = '';
                if (starts_at) text = `🔒 ${I18N.banned_since || 'Ban since'}: ${starts_at}`;
                else text = `🔒 ${I18N.ban || 'Banned'}`;
                if (until) text += ` • ${I18N.until || 'until'}: ${until}`;
                status.textContent = text;
            } else {
                if (starts_at) status.textContent = `⏰ ${I18N.scheduled || 'Scheduled'}: ${starts_at}`;
                else status.textContent = '';
            }
        }
    }

    function createAndShowModal(type, id, initialStartsAt = null, initialUntil = null) {
        document.querySelectorAll('.js-ban-modal-overlay').forEach(el => el.remove());
        const overlay = document.createElement('div');
        overlay.className = 'js-ban-modal-overlay';
        Object.assign(overlay.style, {
            position: 'fixed', inset: '0', display: 'flex', alignItems: 'center', justifyContent: 'center',
            background: 'rgba(0,0,0,0.6)', zIndex: 2147483000, padding: '16px'
        });
        const card = document.createElement('div');
        Object.assign(card.style, {
            width: '100%', maxWidth: '520px', background: 'var(--card,#0f2233)', color: 'var(--text,#e7f4ff)',
            borderRadius: '12px', padding: '18px', boxShadow: '0 20px 60px rgba(0,0,0,0.6)'
        });

        card.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                <strong style="font-size:16px">${type.charAt(0).toUpperCase()+type.slice(1)} — ${I18N.ban || 'Ban / Schedule'}</strong>
                <button class="js-ban-close" aria-label="close" style="background:none;border:0;color:var(--muted,#9fb7dd);cursor:pointer;font-size:18px">✕</button>
            </div>
            <div style="color:var(--muted,#9fb7dd);font-size:13px;margin-bottom:10px">
                ${I18N.date_optional || 'Sana tanlash ixtiyoriy.'} <strong style="color:#ef4444">${I18N.ban_now || 'Ban Now'}</strong> — darhol ban qiladi. Agar <strong>Until</strong> tanlasangiz, ban avtomatik tugaydi.
            </div>
            <label style="font-size:13px; color:var(--muted); display:block; margin-bottom:6px">${I18N.date_optional || 'Start (optional)'}</label>
            <input id="js-ban-start" type="datetime-local" style="width:100%;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.02);color:inherit;margin-bottom:8px" />
            <label style="font-size:13px; color:var(--muted); display:block; margin-bottom:6px">${I18N.until || 'Until (optional)'}</label>
            <input id="js-ban-until" type="datetime-local" style="width:100%;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.02);color:inherit;margin-bottom:8px" />
            <div id="js-ban-hint" style="color:#facc15;min-height:18px;margin-bottom:12px;font-size:13px"></div>
            <div style="display:flex;justify-content:flex-end;gap:10px">
                <button id="js-ban-now" style="background:#ef4444;border:none;color:#fff;padding:8px 12px;border-radius:8px;cursor:pointer">${I18N.ban || '🚨 Ban Now'}</button>
                <button id="js-ban-schedule" style="background:#f59e0b;border:none;color:#000;padding:8px 12px;border-radius:8px;cursor:pointer">${I18N.scheduled || '⏰ Schedule'}</button>
            </div>
        `;
        overlay.appendChild(card);
        document.body.appendChild(overlay);

        const inputStart = card.querySelector('#js-ban-start');
        const inputUntil = card.querySelector('#js-ban-until');
        const hint = card.querySelector('#js-ban-hint');
        const close = card.querySelector('.js-ban-close');
        const btnNow = card.querySelector('#js-ban-now');
        const btnSchedule = card.querySelector('#js-ban-schedule');

        if (initialStartsAt) inputStart.value = initialStartsAt.replace(' ', 'T').slice(0,16);
        if (initialUntil) inputUntil.value = initialUntil.replace(' ', 'T').slice(0,16);
        if (!initialStartsAt) inputStart.value = '';

        function removeModal() { overlay.remove(); }
        close.addEventListener('click', removeModal);
        overlay.addEventListener('click', (e) => { if (e.target === overlay) removeModal(); });
        document.addEventListener('keydown', function onEsc(e){ if (e.key === 'Escape') { removeModal(); document.removeEventListener('keydown', onEsc); } });

        // Ban Now
        btnNow.addEventListener('click', async () => {
            setBtnLoading(btnNow, true);
            const res = await doBanAction(type, id, 'ban', null, null);
            setBtnLoading(btnNow, false);
            if (res.ok) {
                updateUIFromResponse(type, id, { is_banned: true, starts_at: null, until: null });
                showToast(res.message || (I18N.ban_now || 'Banned'), 'success');
                removeModal();
            } else {
                console.error('[ban] failed', res);
                hint.textContent = res.message || 'Xatolik yuz berdi';
                showToast(res.message || 'Xatolik', 'error');
            }
        });

        // Schedule
        btnSchedule.addEventListener('click', async () => {
            const startVal = formatForServerFromInputValue(inputStart.value);
            const untilVal = formatForServerFromInputValue(inputUntil.value);

            if (!startVal) {
                const res = await doBanAction(type, id, 'ban', null, untilVal);
                if (res.ok) {
                    updateUIFromResponse(type, id, { is_banned: true, starts_at: null, until: untilVal });
                    showToast(res.message || (I18N.ban_now || 'Banned until ' + untilVal), 'success');
                    removeModal();
                } else {
                    hint.textContent = res.message || 'Xatolik yuz berdi';
                    showToast(res.message || 'Xatolik', 'error');
                }
                return;
            }

            const chosen = new Date(startVal.replace(' ', 'T'));
            if (isNaN(chosen.getTime()) || chosen.getTime() <= Date.now()) {
                hint.textContent = (I18N.date_optional || 'Kelajakdagi boshlanish vaqt tanlang');
                return;
            }
            if (untilVal) {
                const untilDate = new Date(untilVal.replace(' ', 'T'));
                if (isNaN(untilDate.getTime()) || untilDate.getTime() <= chosen.getTime()) {
                    hint.textContent = (I18N.date_optional || 'Until vaqti boshlanishdan keyin boʻlishi kerak');
                    return;
                }
            }

            setBtnLoading(btnSchedule, true);
            const res = await doBanAction(type, id, 'schedule', startVal, untilVal);
            setBtnLoading(btnSchedule, false);
            if (res.ok) {
                updateUIFromResponse(type, id, res.json);
                showToast(res.message || (I18N.scheduled || 'Scheduled'), 'success');
                removeModal();
            } else {
                hint.textContent = res.message || 'Xatolik yuz berdi';
                showToast(res.message || 'Xatolik', 'error');
            }
        });
    }

    function createAndShowUnbanModal(type, id) {
        document.querySelectorAll('.js-unban-modal-overlay').forEach(el => el.remove());
        const overlay = document.createElement('div');
        overlay.className = 'js-unban-modal-overlay';
        Object.assign(overlay.style, {
            position: 'fixed', inset: '0', display: 'flex', alignItems: 'center', justifyContent: 'center',
            background: 'rgba(0,0,0,0.6)', zIndex: 2147483000, padding: '16px'
        });
        const card = document.createElement('div');
        Object.assign(card.style, {
            width: '100%', maxWidth: '420px', background: 'var(--card,#0f2233)', color: 'var(--text,#e7f4ff)',
            borderRadius: '12px', padding: '18px', boxShadow: '0 20px 60px rgba(0,0,0,0.6)'
        });

        card.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                <strong style="font-size:16px">${I18N.confirm || 'Confirm Unban'}</strong>
                <button class="js-unban-close" aria-label="close" style="background:none;border:0;color:var(--muted,#9fb7dd);cursor:pointer;font-size:18px">✕</button>
            </div>
            <div style="color:var(--muted,#9fb7dd);font-size:14px;margin-bottom:12px">
                ${I18N.sure || 'Siz haqiqatan ham bandan chiqarishni xohlaysizmi?'}
            </div>
            <div style="display:flex;justify-content:flex-end;gap:10px">
                <button id="js-unban-cancel" style="background:transparent;border:1px solid rgba(255,255,255,0.06);color:inherit;padding:8px 12px;border-radius:8px;cursor:pointer">${I18N.cancel ?? 'Bekor'}</button>
                <button id="js-unban-confirm" style="background:#16a34a;border:none;color:#fff;padding:8px 12px;border-radius:8px;cursor:pointer">${I18N.unban || '🔓 Unban'}</button>
            </div>
        `;
        overlay.appendChild(card);
        document.body.appendChild(overlay);

        const close = card.querySelector('.js-unban-close');
        const btnCancel = card.querySelector('#js-unban-cancel');
        const btnConfirm = card.querySelector('#js-unban-confirm');

        function removeModal() { overlay.remove(); }
        close.addEventListener('click', removeModal);
        btnCancel.addEventListener('click', removeModal);

        btnConfirm.addEventListener('click', async () => {
            setBtnLoading(btnConfirm, true);
            const res = await doBanAction(type, id, 'unban');
            setBtnLoading(btnConfirm, false);
            if (res.ok) {
                updateUIFromResponse(type, id, res.json);
                showToast(res.message || (I18N.unban || 'Unbanned'), 'success');
                removeModal();
            } else {
                showToast(res.message || 'Xatolik', 'error');
            }
        });
    }

    function attachListeners() {
        const btns = document.querySelectorAll('.department-ban-btn');
        btns.forEach(btn => {
            if (btn.dataset._bound === '1') return;
            btn.dataset._bound = '1';

            btn.addEventListener('click', async function () {
                const type = btn.dataset.type || 'department';
                const id = btn.dataset.id;
                const isBanned = String(btn.dataset.banned) === '1';

                if (isBanned) { createAndShowUnbanModal(type, id); return; }

                if (type === 'department') {
                    const statusEl = document.getElementById(`${type}-status-${id}`);
                    const existingStart = statusEl?.textContent?.match(/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/)?.[0] ?? null;
                    const existingUntil = statusEl?.textContent?.match(/until:\s*(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/)?.[1] ?? null;
                    createAndShowModal(type, id, existingStart, existingUntil);
                    return;
                }

                setBtnLoading(btn, true);
                const res = await doBanAction(type, id, isBanned ? 'unban' : 'ban');
                setBtnLoading(btn, false);
                if (res.ok) {
                    updateUIFromResponse(type, id, res.json);
                    showToast(res.message || (isBanned ? (I18N.unban || 'Unbanned') : (I18N.ban || 'Banned')), 'success');
                } else {
                    showToast(res.message || 'Xatolik', 'error');
                }
            });
        });
    }

    attachListeners();
    const observer = new MutationObserver((mutations) => { if (mutations.length) attachListeners(); });
    observer.observe(document.body, { childList: true, subtree: true });
});
</script>

@endsection
