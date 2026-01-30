@extends('admin.layouts.app')
@section('show-sidebar','true')

@section('title', __('messages.operations.title') . ' — ' . ($department->name ?? ''))
@section('page-title', $department->name . ' — ' . __('messages.operations.title'))

@section('content')

<style>
:root {
    --bg-dark: #071427;
    --card-dark: #0f2233;
    --text-dark: #e7f4ff;
    --muted-dark: #9fb7dd;
    --accent-dark: #3b82f6;
    --accent2-dark: #facc15;
    --muted2-dark: rgba(255,255,255,0.06);

    --bg-light: #f6f8fb;
    --card-light: #ffffff;
    --text-light: #0b1220;
    --muted-light: #6b7280;
    --accent-light: #2563eb;
    --accent2-light: #d97706;
    --muted2-light: rgba(0,0,0,0.06);
}

/* Theme variables */
body {
    --bg: var(--bg-dark);
    --card: var(--card-dark);
    --text: var(--text-dark);
    --muted: var(--muted-dark);
    --accent: var(--accent-dark);
    --accent2: var(--accent2-dark);
    --muted-2: var(--muted2-dark);
}

body.light {
    --bg: var(--bg-light);
    --card: var(--card-light);
    --text: var(--text-light);
    --muted: var(--muted-light);
    --accent: var(--accent-light);
    --accent2: var(--accent2-light);
    --muted-2: var(--muted2-light);
}

body {
    background: var(--bg);
    color: var(--text);
    transition: all 0.25s ease;
    font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, Arial;
}

/* Global text */
body, body * {
    color: var(--text) !important;
}

/* Muted texts */
.text-muted, .small, .muted-small {
    color: var(--muted) !important;
}

/* Links */
a, a:hover, a:focus {
    color: var(--text) !important;
    text-decoration: none;
}

/* Buttons */
.btn, .btn-theme {
    color: var(--text) !important;
    border-color: var(--muted-2) !important;
}

/* Cards */
.card {
    background: var(--card);
    border: 1px solid var(--muted-2);
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.25);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 28px rgba(0,0,0,0.35);
}

/* Filter bar */
.filter-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    margin-bottom: 16px;
}
.filter-bar .form-control-sm,
.filter-bar .form-select-sm,
.filter-bar .btn-sm {
    font-size: 0.85rem;
    height: 34px;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    border: 1px solid var(--muted-2);
    background: var(--card);
    color: var(--text);
}
.filter-bar .btn-search {
    padding: 0.25rem 0.6rem;
}

/* Message group */
.message-group {
    background: var(--card);
    border: 1px solid var(--muted-2);
    border-radius: 12px;
    padding: 14px;
    margin-bottom: 18px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.25);
    position: relative;
    transition: all 0.2s ease;
}
.message-group::before {
    content:'';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 4px;
    background: var(--accent);
    border-radius: 8px 0 0 8px;
}
.message-group:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 28px rgba(0,0,0,0.35);
}

/* Peer row */
.peer-row {
    padding: 8px 0;
    border-top: 1px solid var(--muted-2);
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
}
.peer-row:first-child { border-top: none; }

/* Status badges */
.status-badge, .status-chip {
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    background: rgba(255,255,255,0.05);
    color: var(--text);
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.status-chip strong { color: var(--accent); }

/* Divider */
.group-divider {
    border-top: 1px solid var(--muted-2);
    margin: 10px 0;
}

/* Responsive */
@media(max-width: 768px) {
    .filter-bar { gap:6px; }
    .filter-bar .search-input { flex:1 1 100%; min-width:100%; }
    .filter-bar .user-select, .filter-bar .form-select-sm { flex:1 1 48%; }
    .filter-bar .date-input { flex:1 1 48%; }
    .peer-row { flex-direction: column; align-items:flex-start; }
}

@media(max-width: 576px){
    .card { padding:12px; }
    .message-group { padding:12px; }
    .status-badge, .status-chip { font-size:0.75rem; padding:3px 6px; }
}
</style>

<div class="container">
    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-3">{{ session('error') }}</div>
    @endif

    {{-- Header + filters --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <div>
            <h5>{{ __('messages.operations.title') }}</h5>
            <div class="text-muted small">{{ __('messages.operations.subtitle', ['dept'=>$department->name]) }}</div>
        </div>

        <form method="GET" class="filter-bar">
            <select name="user_id" class="form-select form-select-sm user-select">
                <option value="">{{ __('messages.find.filter_all_users') }}</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ ($selectedUserId ?? '') == $u->id ? 'selected' : '' }}>
                        {{ $u->name ?? $u->username ?? '#'.$u->id }}
                    </option>
                @endforeach
            </select>

            <input name="q" value="{{ $q ?? '' }}" type="search" class="form-control form-control-sm search-input" placeholder="{{ __('messages.operations.search_placeholder') }}">

            <select name="status" class="form-select form-select-sm">
                <option value="">{{ __('messages.operations.filter_all_status') }}</option>
                @foreach(['pending','scheduled','sent','canceled','failed'] as $s)
                    <option value="{{ $s }}" {{ ($status ?? '')==$s ? 'selected':'' }}>{{ __('messages.operations.status_'.$s) }}</option>
                @endforeach
            </select>

            <input type="date" name="from" value="{{ $from ?? '' }}" class="form-control form-control-sm date-input">
            <input type="date" name="to" value="{{ $to ?? '' }}" class="form-control form-control-sm date-input">

            <button class="btn btn-sm btn-primary btn-search" type="submit">{{ __('messages.operations.btn_search') }}</button>
        </form>
    </div>

    {{-- Totals --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="text-muted small">{{ __('messages.operations.total_groups') }}</div>
                <h3>{{ number_format($messageGroupsTotal ?? 0) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="text-muted small">{{ __('messages.operations.total_messages') }}</div>
                <h3>{{ number_format($telegramMessagesTotal ?? 0) }}</h3>
            </div>
        </div>
        <div class="col-md-6 text-end small-muted align-self-center">
            {{ __('messages.operations.showing') }} <strong>{{ $messageGroups->count() }}</strong> / {{ $messageGroups->total() }}
        </div>
    </div>

    {{-- Message groups --}}
    <div>
        @foreach($messageGroups as $group)
            @php
                $gid = $group->id;
                $stat = $textStats->get($gid);
                $peers = $peerStatusByGroup[$gid] ?? [];
                $total = $groupTotals[$gid] ?? [];
                $sample = $group->message_text ?? null;
            @endphp
            <div class="message-group">
                <div class="d-flex justify-content-between align-items-start flex-wrap">
                    <div>
                        <strong>{{ __('messages.operations.group') }} #{{ $gid }}</strong>
                        <div class="text-muted small">{{ __('messages.operations.by_user') }}: {{ optional($group->phone->user)->name ?? '—' }} ({{ optional($group->phone)->phone ?? '—' }})</div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.operations.show',$gid) }}" class="btn btn-sm btn-outline-primary">{{__('messages.operations.show')}}</a>
                        @if($group->status !=='canceled')
                            <form method="POST" action="{{ route('message-groups.cancel', $gid) }}" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger js-confirm-action" data-text="{{ __('messages.operations.confirm_cancel_text', ['id'=>$gid]) }}">
                                    {{ __('messages.operations.btn_cancel') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="group-divider"></div>

                <div class="message-text mb-2">
                    <strong style="color:var(--accent)">{{ __('messages.operations.text_label') }}:</strong>
                    <span style="font-weight:600;">{{ $sample ?? '—' }}</span>
                </div>

                <div class="d-flex gap-2 flex-wrap mb-3">
                    @foreach(['sent','failed','canceled','scheduled','pending'] as $k)
                        @php $c = $total[$k] ?? 0; @endphp
                        @if($c>0)
                            <span class="status-badge">{!! $k==='sent'?'✓':($k==='failed'?'✕':($k==='canceled'?'⦸':'⏳')) !!} <span>{{ __('messages.operations.status_'.$k) }}</span> <strong>{{ $c }}</strong></span>
                        @endif
                    @endforeach
                </div>

                <div style="max-height:220px; overflow:auto; padding-right:6px;">
                    @forelse($peers as $peer=>$statuses)
                        @php $peerTotal=array_sum($statuses); @endphp
                        <div class="peer-row">
                            <div style="min-width:0; overflow:hidden;">
                                <strong style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:400px;">{{ $peer }}</strong>
                                <div class="text-muted small">{{ __('messages.operations.peer_total') }}: {{ $peerTotal }}</div>
                            </div>
                            <div class="d-flex gap-2 flex-wrap align-items-center">
                                @foreach(['sent','failed','canceled','scheduled','pending'] as $kk)
                                    @php $cnt = $statuses[$kk] ?? 0; @endphp
                                    @if($cnt>0)
                                        <div class="status-chip">{!! $kk==='sent'?'✓':($kk==='failed'?'✕':($kk==='canceled'?'⦸':'⏳')) !!} <span>{{ __('messages.operations.status_'.$kk) }}</span> <strong>{{ $cnt }}</strong></div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="text-muted small">— {{ __('messages.operations.no_peers') }}</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="mt-3 d-flex justify-content-center">
        {{ $messageGroups->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
</div>

<script>
// (function(){
//     /* Confirm modal */
//     const overlay = document.getElementById('confirmOverlay');
//     const desc = document.getElementById('confirmDesc');
//     const cancel = document.getElementById('confirmCancel');
//     const cont = document.getElementById('confirmContinue');
//     let activeForm=null;

//     function openConfirm(text, form){activeForm=form;desc.textContent=text||'Confirm?';overlay.style.display='flex';}
//     function closeConfirm(){overlay.style.display='none';activeForm=null;}
//     cancel?.addEventListener('click', closeConfirm);
//     cont?.addEventListener('click',()=>{if(activeForm) activeForm.submit(); closeConfirm();});
//     document.addEventListener('click', e=>{
//         const btn=e.target.closest('.js-confirm-action'); if(!btn) return;
//         e.preventDefault();
//         openConfirm(btn.dataset.text, btn.closest('form'));
//     });

//     /* Theme toggle */
//     const KEY='app_theme';
//     const body=document.body;
//     const btn=document.getElementById('themeToggleBtn');
//     function apply(theme){body.classList.toggle('light',theme==='light'); if(btn) btn.textContent=theme==='light'?'🌙':'☀️';}
//     let theme=localStorage.getItem(KEY) || (window.matchMedia('(prefers-color-scheme: light)').matches?'light':'dark');
//     apply(theme);
//     btn?.addEventListener('click', ()=>{theme=body.classList.contains('light')?'dark':'light';localStorage.setItem(KEY,theme);apply(theme);});
// })();
</script>

@endsection
