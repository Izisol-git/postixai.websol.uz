{{-- resources/views/admin/departments/users.blade.php --}}
@extends('admin.layouts.app')

@section('title', ($department->name ?? (__('messages.department') ?: 'Department')) . ' — ' . (__('messages.admin.users') ?: 'Users'))
@section('page-title', (__('messages.admin.users') ?: 'Users'))
@section('show-sidebar','true')

@section('content')
@php
    // safe i18n: use Lang::has(...) to allow fallback strings without passing non-array to __()
    $langHas = function($key){
        return \Illuminate\Support\Facades\Lang::has($key);
    };

    $i18n = [
        'delete' => $langHas('messages.admin.delete') ? __('messages.admin.delete') : 'Delete',
        'delete_user' => $langHas('messages.admin.delete_user') ? __('messages.admin.delete_user') : 'Delete user',
        'delete_confirm' => $langHas('messages.departments.delete_confirm')
            ? __('messages.departments.delete_confirm')
            : 'Are you sure you want to delete this department? This action cannot be undone.',
        'cancel' => $langHas('messages.admin.cancel') ? __('messages.admin.cancel') : 'Cancel',
        'confirm' => $langHas('messages.admin.confirm') ? __('messages.admin.confirm') : 'Confirm',
        'success' => $langHas('messages.admin.success') ? __('messages.admin.success') : 'Success',
        'error' => $langHas('messages.admin.error') ? __('messages.admin.error') : 'Error',
        'server_error' => $langHas('messages.admin.server_error') ? __('messages.admin.server_error') : 'Server error',
        'add_user' => $langHas('messages.admin.add_user') ? __('messages.admin.add_user') : 'Add user',
        'toggle' => $langHas('messages.admin.toggle') ? __('messages.admin.toggle') : 'Toggle',
        'back' => $langHas('messages.admin.back') ? __('messages.admin.back') : 'Back',
        'add_phone' => $langHas('messages.admin.add_phone') ? __('messages.admin.add_phone') : 'Add phone',
        'details' => $langHas('messages.admin.details') ? __('messages.admin.details') : 'Details',
        'ban' => $langHas('messages.admin.ban') ? __('messages.admin.ban') : 'Ban',
        'unban' => $langHas('messages.admin.unban') ? __('messages.admin.unban') : 'Unban',
        'no_telegram' => $langHas('messages.admin.no_telegram') ? __('messages.admin.no_telegram') : 'No Telegram',
        'no_role' => $langHas('messages.admin.no_role') ? __('messages.admin.no_role') : 'No role',
        'confirm_type_name' => $langHas('messages.admin.confirm_type_name') ? __('messages.admin.confirm_type_name') : 'Type the name to confirm',
        'confirm_mismatch' => $langHas('messages.admin.confirm_mismatch') ? __('messages.admin.confirm_mismatch') : 'Name mismatch',
        'no_recent_activity' => $langHas('messages.admin.no_recent_activity') ? __('messages.admin.no_recent_activity') : 'No users found',
        'deleted' => $langHas('messages.admin.deleted') ? __('messages.admin.deleted') : 'Deleted',
        'restore' => $langHas('messages.admin.restore') ? __('messages.admin.restore') : 'Restore',
        'bulk_delete' => $langHas('messages.admin.bulk_delete') ? __('messages.admin.bulk_delete') : 'Delete selected',
        'bulk_restore' => $langHas('messages.admin.bulk_restore') ? __('messages.admin.bulk_restore') : 'Restore selected',
        'search' => $langHas('messages.admin.search_users') ? __('messages.admin.search_users') : 'Search users...',
    ];
@endphp

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-start mb-3 gap-3 flex-wrap">
        <div>
            <h3 class="mb-1">{{ __('messages.admin.users') ?: 'Users' }}</h3>
            <div class="small muted-small">{{ $department->name ?? '' }}</div>
        </div>

        <div class="d-flex gap-2 align-items-center">
            <input id="js-search" type="search" placeholder="{{ $i18n['search'] }}" class="form-control form-control-sm" style="min-width:220px;">
            <select id="js-filter-role" class="form-select form-select-sm" style="min-width:160px;">
                <option value="">{{ __('messages.admin.all_role') ?? 'All roles' }}</option>
                @foreach($roles ?? \App\Models\Role::all() as $r)
                    <option value="{{ $r->id }}">{{ ucfirst($r->name) }}</option>
                @endforeach
            </select>

            <a href="{{ route('admin.telegram.new-users',['department'=>$department->id]) }}" class="btn btn-success px-4 py-2">
                {{ $i18n['add_user'] }}
            </a>
            <a href="{{ route('departments.show', $department->id) }}" class="btn btn-outline-secondary px-4 py-2">
                {{ $i18n['back'] }}
            </a>
        </div>
    </div>

    {{-- Bulk actions toolbar (appears when checkboxes are selected) --}}
    <div id="bulkToolbar" class="mb-3" style="display:none;">
        <span id="bulkCount" class="me-3"></span>
        <button id="bulkDeleteBtn" class="btn btn-danger btn-sm me-2">{{ $i18n['bulk_delete'] }}</button>
        <button id="bulkRestoreBtn" class="btn btn-outline-primary btn-sm">{{ $i18n['bulk_restore'] }}</button>
    </div>

    <div id="usersList" class="mt-3">
        @forelse ($users as $user)
            @php
                $isDeleted = method_exists($user, 'trashed') ? $user->trashed() : !is_null($user->deleted_at);
                $userBanned = $user->is_banned ?? false;
            @endphp

            <div class="user-row d-flex flex-wrap align-items-center justify-content-between p-3 mb-2 rounded"
                 data-user-id="{{ $user->id }}"
                 data-deleted="{{ $isDeleted ? '1' : '0' }}"
                 data-banned="{{ $userBanned ? '1' : '0' }}">
                <div class="d-flex align-items-center gap-3 flex-grow-1">
                    <div class="user-meta">
                        <div class="fw-bold user-name">{{ $user->name ?? '—' }}</div>
                        <div class="muted-small">
                            ({{ $user->telegram_id ?? $i18n['no_telegram'] }}) • {{ $user->role_name ?? $i18n['no_role'] }}
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 align-items-center actions">
                    <select class="form-select form-select-sm phone-select" data-user-id="{{ $user->id }}" aria-label="Select phone for {{ $user->name }}">
                        @foreach ($user->phones as $phone)
                            <option value="{{ $phone->id }}" {{ $phone->is_active ? 'selected' : '' }}
                                    >
                                {{ $phone->phone }}
                            </option>
                        @endforeach
                    </select>

                    

                    @if($isDeleted)
                        <span class="badge badge-deleted">{{ $i18n['deleted'] }}</span>
                        {{-- <button class="btn btn-outline-success btn-sm restore-btn" data-user-id="{{ $user->id }}">
                            {{ $i18n['restore'] }}
                        </button> --}}
                    @else
                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-primary px-3 py-2">
                        {{ $i18n['details'] }}
                    </a>
                        @php
    $userBanned = (isset($user->is_banned) && $user->is_banned) ? true : false;
@endphp

<button
    class="btn user-ban-btn px-3 py-2"
    style="background: {{ $userBanned ? '#ef4444' : '#6b7280' }}; color:#fff; font-weight:600;"
    data-type="user"
    data-id="{{ $user->id }}"
    data-banned="{{ $userBanned ? '1' : '0' }}"
    aria-pressed="{{ $userBanned ? 'true' : 'false' }}"
>
    {{ $userBanned ? $i18n['unban'] : $i18n['ban'] }}
</button>



                        <button class="btn btn-danger px-3 py-2 js-confirm-action"
                                data-action="{{ route('users.destroy', $user->id) }}"
                                data-method="DELETE"
                                data-verb="{{ $i18n['delete_user'] }}">
                            {{ $i18n['delete'] }}
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-3 muted-small">{{ $i18n['no_recent_activity'] }}</div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if(method_exists($users, 'links'))
        <div class="mt-3 d-flex justify-content-center">
            {{ $users->links() }}
        </div>
    @endif
</div>

{{-- Styles --}}
<style>
    /* layout */
    .user-row { background: rgba(255,255,255,0.03); align-items: center; transition: background .15s, transform .08s; }
    .user-row:hover { transform: translateY(-2px); }

    /* deleted */
    .user-row[data-deleted="1"] { background: rgba(239,68,68,0.06); border-left: 4px solid rgba(239,68,68,0.9); }
    .user-row[data-deleted="1"] .user-name { color: #991b1b; }
    .badge-deleted { background: rgba(239,68,68,0.12); color: #b91c1c; padding: 6px 10px; border-radius: 999px; font-weight:600; }

    /* buttons */
    .btn { white-space: nowrap; }
    .restore-btn { border-radius: 8px; padding: 6px 10px; }

    /* bulk toolbar */
    #bulkToolbar { background: rgba(0,0,0,0.03); padding: 8px 12px; border-radius: 8px; }

    /* modal */
    .js-modal-overlay { position: fixed; inset: 0; display:flex; align-items:center; justify-content:center; background: rgba(0,0,0,0.5); z-index: 2147483647; padding: 16px; }
    .js-modal-card { width: 100%; max-width: 520px; border-radius: 12px; padding: 18px; box-shadow: 0 12px 40px rgba(2,6,23,0.6); background: #fff; color: #111; }
    @media (prefers-color-scheme: dark) { .js-modal-card { background:#081024; color:#e6eef8; } }

    /* responsive */
    @media (max-width:768px) {
        .user-row { flex-direction: column; align-items: stretch; gap: 8px; }
        .actions .btn, .actions .form-select { width: 100%; }
    }
</style>

{{-- I18N for JS --}}
<script>const I18N = @json($i18n, JSON_UNESCAPED_UNICODE);</script>

{{-- JS: modal + AJAX actions + bulk + search (fully contained) --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    /* ---------- basic helpers ---------- */
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
    function toast(msg, type = 'success', timeout = 3000) {
        const rootId = 'js-toast-root';
        let root = document.getElementById(rootId);
        if (!root) {
            root = document.createElement('div');
            root.id = rootId;
            Object.assign(root.style, { position: 'fixed', top: '18px', right: '18px', zIndex: 2147483650 });
            document.body.appendChild(root);
        }
        const t = document.createElement('div');
        t.textContent = msg;
        Object.assign(t.style, {
            background: type === 'success' ? '#16a34a' : (type === 'error' ? '#ef4444' : '#2563eb'),
            color: '#fff', padding: '8px 12px', borderRadius: '8px', marginTop: '8px', boxShadow: '0 8px 24px rgba(2,6,23,0.3)', fontSize: '13px'
        });
        root.appendChild(t);
        setTimeout(()=>{
            t.style.opacity = '0'; t.remove();
            if (!root.children.length) root.remove();
        }, timeout);
    }

    async function postJson(url, method='POST', body=null, headers={}) {
        const opts = {
            method: method.toUpperCase(),
            credentials: 'same-origin',
            headers: Object.assign({'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json'}, headers)
        };
        if (body) {
            if (body instanceof FormData) {
                opts.body = body;
                // let browser set Content-Type for FormData
            } else {
                opts.body = JSON.stringify(body);
                opts.headers['Content-Type'] = 'application/json';
            }
        }
        const res = await fetch(url, opts);
        const text = await res.text();
        let json = {};
        try { json = text ? JSON.parse(text) : {}; } catch(e) { json = { raw: text }; }
        if (!res.ok) throw { status: res.status, json, text };
        return json;
    }

    /* ---------- modal (single reusable) ---------- */
    let modalOverlay = null;
    function ensureModal() {
        if (modalOverlay) return modalOverlay;
        modalOverlay = document.createElement('div');
        modalOverlay.className = 'js-modal-overlay';
        modalOverlay.style.display = 'none';
        modalOverlay.innerHTML = `<div class="js-modal-card" role="dialog" aria-modal="true">
            <div class="js-modal-inner">
                <h3 class="js-modal-title" style="margin-bottom:8px"></h3>
                <p class="js-modal-body" style="margin-bottom:12px"></p>
                <div style="display:flex;justify-content:flex-end;gap:10px">
                    <button class="js-modal-cancel" style="padding:8px 12px;border-radius:8px;border:1px solid rgba(0,0,0,0.08);background:transparent">` + (I18N.cancel || 'Cancel') + `</button>
                    <button class="js-modal-confirm" style="padding:8px 12px;border-radius:8px;background:#ef4444;color:#fff;border:none">` + (I18N.confirm || 'Confirm') + `</button>
                </div>
            </div>
        </div>`;
        document.body.appendChild(modalOverlay);
        return modalOverlay;
    }

    function showModal({ title = '', message = '', confirmText = null, cancelText = null }) {
        return new Promise((resolve, reject) => {
            const overlay = ensureModal();
            overlay.style.display = 'flex';
            overlay.querySelector('.js-modal-title').textContent = title || (I18N.confirm || 'Confirm');
            overlay.querySelector('.js-modal-body').textContent = message || '';
            if (confirmText) overlay.querySelector('.js-modal-confirm').textContent = confirmText;
            if (cancelText) overlay.querySelector('.js-modal-cancel').textContent = cancelText;

            const onCancel = () => { cleanup(); resolve(false); };
            const onConfirm = () => { cleanup(); resolve(true); };
            const onEsc = (e) => { if (e.key === 'Escape') onCancel(); };

            function cleanup() {
                overlay.style.display = 'none';
                overlay.querySelector('.js-modal-cancel').removeEventListener('click', onCancel);
                overlay.querySelector('.js-modal-confirm').removeEventListener('click', onConfirm);
                document.removeEventListener('keydown', onEsc);
            }

            overlay.querySelector('.js-modal-cancel').addEventListener('click', onCancel);
            overlay.querySelector('.js-modal-confirm').addEventListener('click', onConfirm);
            document.addEventListener('keydown', onEsc);
            // focus confirm for accessibility
            setTimeout(()=> overlay.querySelector('.js-modal-confirm').focus(), 10);
        });
    }

    /* ---------- delegated handlers ---------- */

    // DELETE handler (modal confirm only)
    document.body.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-confirm-action');
        if (!btn) return;
        e.preventDefault();

        const action = btn.dataset.action;
        const method = (btn.dataset.method || 'POST').toUpperCase();

        const ok = await showModal({ title: (btn.dataset.verb || I18N.delete), message: I18N.delete_confirm || 'Are you sure?' });
        if (!ok) return;

        try {
            const res = await postJson(action, method, null);
            toast(res.message || (I18N.success || 'Success'), 'success');
            // remove DOM node or reload
            const row = btn.closest('.user-row');
            if (row) row.remove();
            else window.location.reload();
        } catch (err) {
            console.error(err);
            toast((err.json && err.json.message) || (I18N.server_error || 'Server error'), 'error');
        }
    });
    
    // BAN/UNBAN handler
    document.body.addEventListener('click', async (e) => {
        const btn = e.target.closest('.user-ban-btn');
        if (!btn) return;
        e.preventDefault();

        const type = btn.dataset.type || 'user';
        const id = btn.dataset.id;
        const isBanned = String(btn.dataset.banned) === '1';
        const action = '/admin/ban-unban';

        // optimistic UI
        btn.disabled = true;
        const originalText = btn.textContent;
        try {
            const payload = { bannable_type: type, bannable_id: Number(id), action: isBanned ? 'unban' : 'ban' };
            const res = await postJson(action, 'POST', payload);
            btn.dataset.banned = isBanned ? '0' : '1';
            btn.style.background = (!isBanned) ? '#ef4444' : '#6b7280';
            btn.textContent = (!isBanned) ? (I18N.unban || 'Unban') : (I18N.ban || 'Ban');
            toast(res.message || (I18N.success || 'Success'), 'success');
        } catch (err) {
            console.error(err);
            toast((err.json && err.json.message) || (I18N.error || 'Error'), 'error');
            btn.textContent = originalText;
        } finally {
            btn.disabled = false;
        }
    });

    // RESTORE handler (soft-deleted restore)
    document.body.addEventListener('click', async (e) => {
        const btn = e.target.closest('.restore-btn');
        if (!btn) return;
        e.preventDefault();
        const userId = btn.dataset.userId;
        const action = `/admin/users/${userId}/restore`; // adjust route if needed

        const ok = await showModal({ title: (I18N.restore || 'Restore'), message: (I18N.confirm || 'Confirm') + ' — ' + (I18N.restore || 'Restore user?') });
        if (!ok) return;

        try {
            const res = await postJson(action, 'POST', null);
            toast(res.message || (I18N.success || 'Restored'), 'success');
            // reload or update row
            window.location.reload();
        } catch (err) {
            console.error(err);
            toast((err.json && err.json.message) || (I18N.error || 'Error'), 'error');
        }
    });

    // Phone select change (AJAX activate phone)
    document.body.addEventListener('change', async (e) => {
        const sel = e.target.closest('.phone-select');
        if (!sel) return;
        const userId = sel.dataset.userId;
        const phoneId = sel.value;
        const action = '/admin/phones/activate'; // adjust route to your endpoint

        try {
            const res = await postJson(action, 'POST', { user_id: Number(userId), phone_id: Number(phoneId) });
            toast(res.message || (I18N.success || 'Phone activated'), 'success');
        } catch (err) {
            console.error(err);
            toast((err.json && err.json.message) || (I18N.error || 'Error'), 'error');
        }
    });

    /* ---------- bulk actions ---------- */
    const bulkToolbar = document.getElementById('bulkToolbar');
    const bulkCountEl = document.getElementById('bulkCount');
    const getSelectedUserIds = () => Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.dataset.userId);

    document.body.addEventListener('change', (e) => {
        if (e.target.matches('.user-checkbox')) {
            const selected = getSelectedUserIds();
            if (selected.length) {
                bulkToolbar.style.display = 'block';
                bulkCountEl.textContent = selected.length + ' selected';
            } else {
                bulkToolbar.style.display = 'none';
            }
        }
        // master checkbox handling (optional): implement if needed
    });

    document.getElementById('bulkDeleteBtn')?.addEventListener('click', async () => {
        const ids = getSelectedUserIds();
        if (!ids.length) return toast('No users selected', 'error');
        const ok = await showModal({ title: I18N.bulk_delete, message: 'This will delete selected users. Continue?' });
        if (!ok) return;
        try {
            const res = await postJson('/admin/users/bulk-delete', 'POST', { ids });
            toast(res.message || 'Deleted', 'success');
            // remove rows
            ids.forEach(id => {
                const row = document.querySelector(`.user-row[data-user-id="${id}"]`);
                if (row) row.remove();
            });
            bulkToolbar.style.display = 'none';
        } catch (err) {
            console.error(err);
            toast((err.json && err.json.message) || 'Error', 'error');
        }
    });

    document.getElementById('bulkRestoreBtn')?.addEventListener('click', async () => {
        const ids = getSelectedUserIds();
        if (!ids.length) return toast('No users selected', 'error');
        const ok = await showModal({ title: I18N.bulk_restore, message: 'This will restore selected users. Continue?' });
        if (!ok) return;
        try {
            const res = await postJson('/admin/users/bulk-restore', 'POST', { ids });
            toast(res.message || 'Restored', 'success');
            window.location.reload();
        } catch (err) {
            console.error(err);
            toast((err.json && err.json.message) || 'Error', 'error');
        }
    });

    /* ---------- search (debounced) ---------- */
    function debounce(fn, wait=300) {
        let t;
        return (...args) => { clearTimeout(t); t = setTimeout(()=> fn.apply(this, args), wait); };
    }

    async function performSearch(q, roleId) {
        // if you have server-side filtering endpoint, call it here.
        // For now we'll do client-side simple filter to keep it snappy on small lists.
        const rows = Array.from(document.querySelectorAll('.user-row'));
        const ql = (q || '').toLowerCase().trim();
        rows.forEach(row => {
            const name = row.querySelector('.user-name')?.textContent?.toLowerCase() || '';
            const role = row.querySelector('.muted-small')?.textContent?.toLowerCase() || '';
            const matchesQ = !ql || name.includes(ql) || role.includes(ql);
            const matchesRole = !roleId || row.querySelector('.muted-small')?.textContent?.toLowerCase().includes(roleId.toString()) ? true : true;
            row.style.display = (matchesQ && matchesRole) ? '' : 'none';
        });
    }

    document.getElementById('js-search')?.addEventListener('input', debounce(function(e){
        performSearch(e.target.value, document.getElementById('js-filter-role')?.value);
    }, 220));

    document.getElementById('js-filter-role')?.addEventListener('change', function(e){
        performSearch(document.getElementById('js-search')?.value || '', e.target.value);
    });

    /* ---------- keyboard shortcuts (optional) ---------- */
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            document.getElementById('js-search')?.focus();
        }
    });

    /* ---------- final notes ---------- */
    // If you expect hundreds or thousands of users, prefer server-side pagination & search.
});
function updateBanButton(type, id, isBanned) {
    const btn = document.querySelector(`[data-type="${type}"][data-id="${id}"]`);
    if (!btn) return;
    btn.dataset.banned = isBanned ? '1' : '0';
    btn.style.background = isBanned ? '#ef4444' : '#6b7280';
    btn.textContent = isBanned ? (I18N.unban || 'Unban') : (I18N.ban || 'Ban');
    btn.setAttribute('aria-pressed', isBanned ? 'true' : 'false');
}

</script>

@endsection
