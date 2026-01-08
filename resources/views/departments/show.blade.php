<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>{{ $department->name ?? 'Department' }} — Batafsil — Postix Ai</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --bg: #071427;
            --card: #0f2233;
            --muted: #9fb7dd;
            --text: #e7f4ff;
            --accent: #3b82f6;
            --yellow: #facc15;
            --danger: #ef4444;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, Arial;
            padding: 18px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Topbar */
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            gap: 12px;
        }

        .title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text);
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .breadcrumbs {
            color: var(--muted);
            font-size: 0.95rem;
        }

        /* Card */
        .card {
            background: var(--card);
            border-radius: 12px;
            padding: 16px;
            border: 1px solid rgba(255, 255, 255, 0.03);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.6);
            margin-bottom: 14px;
        }

        .card h3 {
            color: var(--yellow);
            margin-top: 0;
            margin-bottom: 10px;
        }

        /* Stats */
        .stats {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .stat {
            background: #0a1b3c;
            border-radius: 10px;
            padding: 12px;
            flex: 1 1 160px;
            text-align: center;
        }

        .stat h4 {
            margin: 0;
            font-size: 1.3rem;
            color: var(--accent);
        }

        .stat p {
            margin: 6px 0 0;
            font-size: 0.95rem;
            color: var(--muted);
        }

        /* Compact users list */
        .users-compact {
            margin-bottom: 14px;
        }

        .user-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 8px 10px;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 8px;
            margin-bottom: 8px;
        }

        .user-line .left {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .user-name {
            font-weight: 700;
            color: var(--text);
        }

        .user-telegram {
            color: var(--muted);
        }

        /* Phone dropdown */
        .form-select-sm {
            background: #071827;
            color: var(--text);
            border: 1px solid rgba(255, 255, 255, 0.04);
            border-radius: 7px;
            padding: 6px 10px;
        }

        /* MessageGroup card */
        .mg-card {
            background: rgba(255, 255, 255, 0.02);
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 12px;
            border: 1px solid rgba(255, 255, 255, 0.03);
        }

        .search-form {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .search-form input {
            flex: 1;
        }

        .mg-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
        }

        .mg-title {
            font-weight: 800;
            color: var(--text);
        }

        .mg-meta {
            color: var(--muted);
            font-size: 0.9rem;
        }

        /* Buttons */
        .btn-ghost {
            background: transparent;
            color: var(--muted);
            border: 1px solid rgba(255, 255, 255, 0.04);
            padding: 6px 10px;
            border-radius: 8px;
        }

        .btn-refresh {
            background: var(--accent);
            color: white;
            border-radius: 8px;
            padding: 6px 10px;
            border: none;
        }

        .btn-cancel {
            background: var(--danger);
            color: white;
            border-radius: 8px;
            padding: 6px 10px;
            border: none;
        }

        /* text importance */
        .normal {
            color: var(--text);
        }

        .important {
            color: var(--yellow);
            font-weight: 700;
        }

        /* badges & small */
        .badge {
            background: #0e2342;
            color: var(--text);
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .small-note {
            color: var(--muted);
            font-size: 0.9rem;
        }

        /* messages */
        .msg {
            background: rgba(255, 255, 255, 0.01);
            padding: 8px;
            border-radius: 8px;
            margin-bottom: 8px;
            color: #e6f2ff;
        }

        .meta-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        /* totals line */
        .totals-line {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: flex-end;
            color: var(--muted);
        }

        /* responsive */
        @media (max-width:900px) {
            .mg-header {
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
            }

            .totals-line {
                justify-content: flex-start;
                margin-top: 8px;
            }
        }

        /* Pagination Style */
        .pagination {
            display: flex;
            list-style: none;
            gap: 8px;
            padding: 0;
            justify-content: center;
            margin-top: 20px;
        }

        .page-item .page-link {
            background: var(--card);
            color: var(--muted);
            border: 1px solid rgba(255, 255, 255, 0.04);
            padding: 6px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.95rem;
        }

        .page-item.active .page-link {
            background: var(--accent);
            color: white;
            border-color: var(--accent);
        }

        .page-item.disabled .page-link {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .page-item .page-link:hover:not(.active, .disabled) {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text);
        }

        /* --- Confirm modal styles (minimal, in-project look) --- */
        #confirmOverlay {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1200;
            background: rgba(1, 6, 23, 0.6);
            padding: 20px;
        }

        #confirmBox {
            width: 100%;
            max-width: 560px;
            background: var(--card);
            border-radius: 12px;
            padding: 18px;
            border: 1px solid rgba(255, 255, 255, 0.04);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.65);
            color: var(--text);
        }

        .confirm-title {
            font-weight: 800;
            color: var(--yellow);
            margin-bottom: 6px;
            font-size: 1.05rem;
        }

        .confirm-desc {
            color: var(--muted);
            margin-bottom: 12px;
        }

        .confirm-input {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid rgba(0, 0, 0, 0.06);
            height: 44px;
            margin-top: 6px;
            margin-bottom: 6px;
            color: #071427;
        }

        .confirm-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 8px;
        }

        .modal-btn-cancel {
            background: transparent;
            color: var(--muted);
            border: 1px solid rgba(255, 255, 255, 0.04);
            padding: 8px 12px;
            border-radius: 8px;
        }

        .modal-btn-continue {
            background: var(--accent);
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 700;
        }

        .modal-btn-final {
            background: var(--danger);
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 800;
        }

        .modal-btn-final[disabled] {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>

<body>
    <div class="container">

        <!-- Top -->
        <div class="topbar">
            <div class="title">
                <span style="font-weight:800; color:var(--yellow)">POSTIX AI</span>
                <span class="breadcrumbs"> / <a href="{{ route('departments.index') }}"
                        style="color:var(--muted); text-decoration:none;">Departments</a> → <span
                        style="color:var(--text)">{{ $department->name }}</span></span>
            </div>

            <div>
                <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button class="btn-ghost" type="submit" style="background:#ef4444; color:white;">Logout</button>
                </form>
            </div>
        </div>

        <!-- Department header -->
        <div class="card p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 class="mb-0">{{ $department->name }} — Batafsil</h3>

                @php
                    $departmentBan = $department->ban ?? null;
                    $isBannedActive = $departmentBan?->active ?? false;
                    $startsAt = $departmentBan?->starts_at?->toDateTimeString();
                @endphp

                <div class="d-flex align-items-center gap-2">
                    <!-- Scheduled / Active info -->
                    <span id="department-status-{{ $department->id }}" style="font-size:13px; color:#6b7280;">
                        @if ($isBannedActive)
                            🔒 Ban since: {{ $startsAt }}
                        @elseif($startsAt)
                            ⏰ Scheduled: {{ $startsAt }}
                        @endif
                    </span>

                    <!-- Ban / Unban button -->
                    <button id="department-btn-{{ $department->id }}"
                        class="btn btn-sm {{ $isBannedActive ? 'btn-success' : 'btn-danger' }}"
                        onclick="{{ $isBannedActive ? "doBanAction('department', {$department->id}, 'unban')" : "showBanModal('department', {$department->id}" . ($startsAt ?? false ? ", '" . $startsAt . "'" : '') . ')' }}">
                        {{ $isBannedActive ? '🔓 Unban' : '🛑 Ban' }}
                    </button>

                    <!-- Edit/Delete for superadmin -->
                    @if (auth()->check() && auth()->user()->role?->name === 'superadmin')
                        <a href="{{ route('departments.edit', $department->id) }}" class="btn btn-sm btn-warning">✏️
                            Tahrirlash</a>
                        <button class="btn btn-sm btn-danger js-confirm-action"
                            data-action="{{ route('departments.destroy', $department->id) }}" data-method="DELETE"
                            data-verb="Bo‘limni o‘chirish" data-require-name="{{ $department->name }}">
                            🗑 O‘chirish
                        </button>
                    @endif
                </div>
            </div>
        </div>



        <div class="stats">
            <div class="stat">
                <h4>{{ $usersCount ?? 0 }}</h4>
                <p>Foydalanuvchilar</p>
            </div>
            <div class="stat">
                <h4>{{ $activePhonesCount ?? 0 }}</h4>
                <p>Telefonlar</p>
            </div>
            <div class="stat">
                <h4>{{ $messageGroupsTotal ?? 0 }}</h4>
                <p>Operatsiya</p>
            </div>
            <div class="stat">
                <h4>{{ $telegramMessagesTotal ?? 0 }}</h4>
                <p>Xabarlar soni</p>
            </div>
        </div>


        <!-- Users List with Delete, Show, Ban User, and Phone Ban Checkbox -->
        <div class="users-compact">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <h5 style="color:var(--yellow); margin-bottom:8px; cursor:pointer;" onclick="toggleUsersList()">
                    Foydalanuvchilar ▾
                </h5>
                <!-- Toast notifications -->
                <div id="toast-container" style="position:fixed; top:20px; right:20px; z-index:9999;"></div>

                <!-- CREATE USER BUTTON -->
                <a href="{{ route('users.create') }}" class="btn btn-sm"
                    style="background:#22c55e; color:#fff; padding:5px 14px; font-size:12px; border-radius:8px; text-decoration:none;">
                    + Add User
                </a>
            </div>

            <div id="usersList" style="display:none; margin-top:6px;">
                @foreach ($users as $user)
                    @php
                        $userBanned = $user->ban && $user->ban->active;
                        $activePhone = $user->phones->firstWhere('is_active', 1);
                        $phoneBanned = $activePhone && $activePhone->ban && $activePhone->ban->active;
                    @endphp

                    <div class="user-line" data-user-id="{{ $user->id }}">
                        <div class="left" style="display:flex; align-items:center; gap:12px;">
                            <div class="user-name">{{ $user->name ?? '—' }}</div>
                            <div class="user-telegram">({{ $user->telegram_id ?? '—' }})</div>
                            <div class="user-role">({{ $user->role->name ?? '—' }})</div>
                        </div>


                        <div style="min-width:360px; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                            <select class="form-select-sm phone-select" data-user-id="{{ $user->id }}">
                                @foreach ($user->phones as $phone)
                                    <option value="{{ $phone->id }}" {{ $phone->is_active ? 'selected' : '' }}
                                        data-phone-banned="{{ $phone->ban && $phone->ban->active ? '1' : '0' }}">
                                        {{ $phone->phone }}
                                    </option>
                                @endforeach
                            </select>

                            <a href="{{ route('telegram.login', ['user_id' => $user->id]) }}" class="btn btn-sm"
                                style="background:#4ade80; color:white; padding:5px 12px; font-size:11px; border-radius:6px; text-decoration:none;">
                                Add Phone
                            </a>


                            <a href="/users/{{ $user->id }}" class="btn btn-sm"
                                style="background:#3b82f6; color:#fff; padding:4px 8px; font-size:11px; border-radius:6px; text-decoration:none;">
                                Batafsil
                            </a>
                            @php
                                $userBanned = $user->ban?->active ?? false;
                            @endphp

                            <button type="button" class="btn btn-sm ban-toggle-btn"
                                id="user-ban-btn-{{ $user->id }}"
                                style="background: {{ $userBanned ? '#ef4444' : '#6b7280' }}; color:#fff; padding:5px 12px; font-size:11px; border-radius:6px; border:none;"
                                onclick="handleUserBanButton({{ $user->id }}, {{ $userBanned ? 'true' : 'false' }})">
                                {{ $userBanned ? 'Unban' : 'Ban' }}
                            </button>






                            <!-- User delete still requires typing exact name -->
                            <button type="button" class="btn btn-sm js-confirm-action"
                                style="background:#ef4444; color:#fff; padding:4px 8px; font-size:11px; border-radius:6px; border:none;"
                                data-action="{{ route('users.destroy', $user->id) }}" data-method="DELETE"
                                data-verb="Foydalanuvchini o‘chirish"
                                data-require-name="{{ $user->name ?? $user->id }}">
                                O'chirish
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>


        <!-- MessageGroups list (paginated) -->
        <div style="margin-top:18px;">
            <h5 style="color:var(--yellow); margin-bottom:6px;">Operatsiyalar</h5>
            <form method="GET" action="{{ route('departments.show', $department->id) }}" class="search-form mb-3"
                role="search">
                <input class="form-control" type="search" name="q" value="{{ $search ?? '' }}"
                    placeholder="Message text bo'yicha qidirish..."
                    style="background: #ffffff; border: 1px solid rgba(0,0,0,0.08); color: #071427; border-radius: 8px;">
                <button class="btn btn-primary" type="submit">Search</button>
            </form>


            @foreach ($messageGroups as $group)
                @php
                    $gid = $group->id;
                    $stat = $textStats->get($gid);
                    $peers = $peerStatusByGroup[$gid] ?? [];
                    $total = $groupTotals[$gid] ?? [];

                    $labels = [
                        'sent' => ['label' => 'sent', 'color' => '#4ade80'],
                        'canceled' => ['label' => 'canceled', 'color' => '#f87171'],
                        'scheduled' => ['label' => 'scheduled', 'color' => '#facc15'],
                        'failed' => ['label' => 'failed', 'color' => '#ef4444'],
                    ];

                    // note: no data-require-name for cancel (3rd step removed)

                @endphp

                <div class="mg-card">
                    <div class="mg-header">
                        <div>
                            <div class="mg-title">Operatsiya #{{ $gid }}</div>
                            <div class="mg-meta">
                                {{ optional($group->phone->user)->name ?? '—' }}
                                ({{ optional($group->phone)->phone ?? '—' }})

                            </div>
                        </div>

                        <div style="display:flex; gap:8px;">
                            <!-- Refresh now uses modal and will POST to refresh route -->
                            <button class="btn btn-outline-info js-confirm-action"
                                data-action="{{ route('message-groups.refresh', $group->id) }}" data-method="POST"
                                data-text="Siz “Operatsiya #{{ $group->id }}” holatini yangilamoqchisiz. Davom etishni xohlaysizmi?">
                                Refresh
                            </button>

                            <!-- Cancel now uses modal but NO exact-name requirement (3rd step removed) -->
                            <button type="button" class="btn-cancel js-confirm-action"
                                data-action="{{ route('message-groups.cancel', $gid) }}" data-method="POST"
                                data-verb="Operatsiyani bekor qilish"
                                data-text="Siz “Operatsiya #{{ $gid }}” ga oid amalni bajarishni boshlamoqchisiz. Davom etishni xohlaysizmi?">
                                Cancel
                            </button>
                        </div>
                    </div>

                    <hr style="border-color: rgba(255,255,255,0.04); margin:8px 0;">

                    <div
                        style="background:rgba(255,255,255,0.05); padding:10px; border-radius:8px; margin-top:6px; word-break:break-word;">
                        <strong style="color:var(--yellow);">Text:</strong>
                        <span style="font-weight:600; color:var(--text);">
                            {{ $stat->sample_text ?? '—' }}
                        </span>
                    </div>

                    <div style="margin-top:6px;">
                        <!-- Search + controls -->
                        {{-- <div style="display:flex; gap:8px; align-items:center; margin-bottom:8px;">
                        <input type="search" class="peer-search" placeholder="Search peers..."
                            style="flex:1; padding:6px 10px; border-radius:6px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.04); color:var(--text);">
                        <button type="button" class="peer-filter-failed btn-sm" title="Show only peers with failed"
                            style="padding:6px 8px; border-radius:6px; background:#ef4444; color:#fff; border:none;">
                            Failed only
                        </button>
                        <button type="button" class="peer-clear btn-sm" title="Clear"
                            style="padding:6px 8px; border-radius:6px; background:#64748b; color:#fff; border:none;">
                            Clear
                        </button>
                    </div> --}}

                        <!-- Compact scrollable peer list (fixed height) -->
                        <div class="peer-list" style="max-height:220px; overflow:auto; padding-right:6px;">
                            @foreach ($peers as $peer => $statuses)
                                @php $peerTotal = array_sum($statuses); @endphp

                                <div class="peer-row" data-peer="{{ $peer }}"
                                    data-sent="{{ $statuses['sent'] ?? 0 }}"
                                    data-failed="{{ $statuses['failed'] ?? 0 }}"
                                    data-canceled="{{ $statuses['canceled'] ?? 0 }}"
                                    data-scheduled="{{ $statuses['scheduled'] ?? 0 }}"
                                    style="display:flex; justify-content:space-between; align-items:center; padding:6px 8px; border-radius:6px; margin-bottom:6px; background:rgba(255,255,255,0.02);">

                                    <div style="display:flex; gap:8px; align-items:center; min-width:0;">
                                        <strong
                                            style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:220px;">{{ $peer }}</strong>
                                        <span class="small-note" style="opacity:0.7;">total:
                                            {{ $peerTotal }}</span>
                                    </div>

                                    <div style="display:flex; gap:6px; flex-wrap:wrap; align-items:center;">
                                        @foreach ($labels as $key => $info)
                                            @php $count = $statuses[$key] ?? 0; @endphp
                                            @if ($count > 0)
                                                <span title="{{ ucfirst($info['label']) }}"
                                                    style="
                    display:flex;
                    align-items:center;
                    gap:4px;
                    background:{{ $info['color'] }}22;
                    color:{{ $info['color'] }};
                    padding:3px 7px;
                    border-radius:6px;
                    font-size:11px;
                    font-weight:700;
                    white-space:nowrap;
                ">
                                                    {{-- Icon --}}
                                                    @if ($key === 'sent')
                                                        ✓
                                                    @elseif ($key === 'failed')
                                                        ✕
                                                    @elseif ($key === 'canceled')
                                                        ⦸
                                                    @elseif ($key === 'scheduled')
                                                        ⏳
                                                    @endif

                                                    {{-- Label + count --}}
                                                    <span style="opacity:.85;">{{ $info['label'] }}</span>
                                                    <span>{{ $count }}</span>
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    </div>

                    @php
                        $all = array_sum($total);
                        $sent = $total['sent'] ?? 0;
                        $rate = $all > 0 ? round(($sent / $all) * 100) : 0;
                    @endphp

                    <div style="margin-top:6px; font-weight:700; display:flex; gap:12px; flex-wrap:wrap;">
                        <span>
                            ALL:
                            <span style="color:#60a5fa">{{ $all }}</span>
                        </span>

                        <span>
                            TOTAL SENT:
                            <span style="color:#22c55e">{{ $sent }}</span>
                        </span>

                        <span>
                            RATE:
                            <span style="color:#facc15">{{ $rate }}%</span>
                        </span>
                    </div>

                </div>
            @endforeach

            <div class="mt-3">
                {{ $messageGroups->withQueryString()->links('pagination::bootstrap-5') }}
            </div>

        </div>

    </div>

    <!-- Toast container (single) -->

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast(@json(session('success')), 'success');
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast(@json(session('error')), 'error');
            });
        </script>
    @endif

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Confirm modal (single, reused for all delete/cancel/refresh actions) -->
    <div id="confirmOverlay" aria-hidden="true">
        <div id="confirmBox" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
            <div class="confirm-title" id="confirmTitle">Tasdiqlash</div>
            <div class="confirm-desc" id="confirmDesc">Siz amaliyotni bajarishni xohlaysizmi?</div>

            <!-- Step 1 -->
            <div id="confirmStep1">
                <div class="confirm-desc" id="confirmStep1Text"></div>
                <div class="confirm-actions" style="margin-top:8px;">
                    <button type="button" class="modal-btn-cancel" id="confirmStep1Cancel">Bekor</button>
                    <button type="button" class="modal-btn-continue" id="confirmStep1Continue">Davom etish</button>
                </div>
            </div>

            <!-- Step 2 (optional exact name for user/department deletes) -->
            <div id="confirmStep2" style="display:none; margin-top:10px;">
                <div style="font-weight:700; margin-bottom:6px;" id="confirmStep2Title">Iltimos, tasdiqlang</div>
                <div class="confirm-desc" id="confirmStep2Desc">Quyidagi maydonga aniq nomni kiriting:</div>

                <input id="confirmInput" class="confirm-input" type="text" placeholder="Nomni aniq kiriting"
                    aria-label="Tasdiqlash nomi" />
                <div class="confirm-example small-note" id="confirmExample">Masalan: <strong>Operatsiya #1</strong>
                </div>

                <div class="confirm-actions" style="margin-top:8px;">
                    <button type="button" class="modal-btn-cancel" id="confirmStep2Back">Ortga</button>
                    <button type="button" class="modal-btn-final" id="confirmStep2Final" disabled>Ha,
                        tasdiqlayman</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        /**
         * doBanAction - universal AJAX call
         */
        async function doBanAction(type, id, explicitAction = null, startsAt = null) {
            const payload = {
                bannable_type: type,
                bannable_id: id
            };
            if (explicitAction) payload.action = explicitAction;
            if (startsAt) payload.starts_at = startsAt;

            try {
                const res = await fetch('/admin/ban-unban', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok || !data.success) {
                    showToast(data.message || 'Xatolik yuz berdi', 'error');
                    return data;
                }

                // Update UI for department
                const depBtn = document.getElementById(`${type}-btn-${id}`);
                const depSched = document.getElementById(`${type}-schedule-${id}`);

                if (depBtn) {
                    if (data.is_banned) {
                        depBtn.textContent = '🔓 Unban';
                        depBtn.className = 'btn btn-sm btn-outline-success';
                        depBtn.setAttribute('onclick', `doBanAction('${type}', ${id}, 'unban')`);
                    } else {
                        depBtn.textContent = '🛑 Ban';
                        depBtn.className = 'btn btn-sm btn-outline-danger';
                        depBtn.setAttribute('onclick', `showBanModal('${type}', ${id})`);
                    }
                }

                if (depSched) {
                    if (data.is_banned) {
                        depSched.textContent = data.starts_at ? `Ban since: ${data.starts_at}` : 'Banned';
                    } else {
                        depSched.textContent = data.starts_at ? `Scheduled: ${data.starts_at}` : '';
                    }
                }

                // Update UI for user-specific button (backwards compatible id)
                const userBtn = document.getElementById(`user-ban-btn-${id}`);
                if (userBtn && type === 'user') {
                    if (data.is_banned) {
                        userBtn.textContent = 'Unban';
                        userBtn.style.background = '#ef4444';
                        // set onclick to call handleUserBanButton with new state = true
                        userBtn.setAttribute('onclick', `handleUserBanButton(${id}, true)`);
                    } else {
                        userBtn.textContent = 'Ban';
                        userBtn.style.background = '#6b7280';
                        userBtn.setAttribute('onclick', `handleUserBanButton(${id}, false)`);
                    }
                }

                showToast(data.message || 'Success', 'success');
                return data;
            } catch (err) {
                console.error(err);
                showToast('Server bilan aloqa yo‘q', 'error');
                return null;
            }
        }

        /**
         * showBanModal - creates a single-use modal (listeners not duplicated)
         */
        function showBanModal(type, id, startedAt = null) {
            // remove old modal if any
            document.querySelectorAll('.ban-modal-overlay').forEach(el => el.remove());

            const overlay = document.createElement('div');
            overlay.className = 'ban-modal-overlay';
            Object.assign(overlay.style, {
                position: 'fixed',
                inset: '0',
                background: 'rgba(0,0,0,0.6)',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                zIndex: 99999,
                padding: '16px',
                transition: 'opacity 0.3s ease-in-out'
            });

            const box = document.createElement('div');
            Object.assign(box.style, {
                width: '100%',
                maxWidth: '520px',
                background: 'linear-gradient(135deg, #1e3a8a, #0f172a)',
                color: '#f0f9ff',
                borderRadius: '16px',
                padding: '24px',
                boxShadow: '0 20px 50px rgba(0, 0, 0, 0.5)',
                position: 'relative',
                display: 'flex',
                flexDirection: 'column',
                gap: '16px',
                transform: 'scale(0.95)',
                transition: 'transform 0.3s ease-in-out, opacity 0.3s ease-in-out',
                opacity: 0
            });

            box.innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <div style="font-weight:800; font-size:18px; color: #ffffff;">Ban ${type.charAt(0).toUpperCase() + type.slice(1)}</div>
            <button id="ban-close" style="background:transparent; border:none; color:#cbd5e1; font-size:20px; cursor:pointer; transition: color 0.2s;">✕</button>
        </div>

        <div style="color:#94a3b8; font-size:14px;">Choose start date & time (optional). "Ban Now" ignores this.</div>
        <input id="ban-datetime-local" type="datetime-local" style="width:100%; padding:12px; border-radius:10px; border:1px solid rgba(255,255,255,0.15); background:#0f172a; color:#f0f9ff; font-size:14px; transition: border-color 0.2s;" />
        <div id="ban-hint" style="color:#fde047; font-size:14px; min-height:14px;"></div>

        <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:8px;">
            <button id="ban-now" style="padding:10px 16px; border-radius:10px; border:none; background:#ef4444; color:#fff; cursor:pointer; font-weight:600; transition: background 0.2s, transform 0.1s;">Ban Now</button>
            <button id="ban-schedule" style="padding:10px 16px; border-radius:10px; border:none; background:#facc15; color:#1e293b; cursor:pointer; font-weight:600; transition: background 0.2s, transform 0.1s;">Schedule</button>
        </div>
    `;

            overlay.appendChild(box);
            document.body.appendChild(overlay);

            // Animate in
            setTimeout(() => {
                overlay.style.opacity = 1;
                box.style.opacity = 1;
                box.style.transform = 'scale(1)';
            }, 10);

            // default datetime local
            const input = document.getElementById('ban-datetime-local');
            if (startedAt) {
                input.value = startedAt.replace(' ', 'T').slice(0, 16);
            } else {
                const now = new Date();
                now.setMinutes(now.getMinutes() + 5 - (now.getMinutes() % 5));
                const pad = n => String(n).padStart(2, '0');
                input.value =
                    `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
            }

            const hint = document.getElementById('ban-hint');
            const closeBtn = document.getElementById('ban-close');
            const btnNow = document.getElementById('ban-now');
            const btnSchedule = document.getElementById('ban-schedule');

            function closeModal() {
                overlay.style.opacity = 0;
                box.style.opacity = 0;
                box.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    document.removeEventListener('keydown', onKey);
                    overlay.remove();
                }, 300);
            }

            function onKey(e) {
                if (e.key === 'Escape') closeModal();
            }
            document.addEventListener('keydown', onKey);

            overlay.addEventListener('click', e => {
                if (e.target === overlay) closeModal();
            });
            closeBtn.addEventListener('click', closeModal);
            closeBtn.addEventListener('mouseover', () => closeBtn.style.color = '#ffffff');
            closeBtn.addEventListener('mouseout', () => closeBtn.style.color = '#cbd5e1');

            input.addEventListener('focus', () => input.style.borderColor = 'rgba(255,255,255,0.3)');
            input.addEventListener('blur', () => input.style.borderColor = 'rgba(255,255,255,0.15)');

            // Button hover effects
            btnNow.addEventListener('mouseover', () => btnNow.style.background = '#dc2626');
            btnNow.addEventListener('mouseout', () => btnNow.style.background = '#ef4444');
            btnNow.addEventListener('mousedown', () => btnNow.style.transform = 'scale(0.98)');
            btnNow.addEventListener('mouseup', () => btnNow.style.transform = 'scale(1)');

            btnSchedule.addEventListener('mouseover', () => btnSchedule.style.background = '#eab308');
            btnSchedule.addEventListener('mouseout', () => btnSchedule.style.background = '#facc15');
            btnSchedule.addEventListener('mousedown', () => btnSchedule.style.transform = 'scale(0.98)');
            btnSchedule.addEventListener('mouseup', () => btnSchedule.style.transform = 'scale(1)');

            // Ban Now
            btnNow.addEventListener('click', () => {
                btnNow.disabled = true;
                doBanAction(type, id).then(() => closeModal()).finally(() => btnNow.disabled = false);
            });

            // Schedule
            btnSchedule.addEventListener('click', () => {
                const v = input.value;
                if (!v) {
                    hint.textContent = 'Iltimos sana va vaqtni tanlang';
                    return;
                }
                const chosen = new Date(v);
                if (isNaN(chosen.getTime())) {
                    hint.textContent = 'Noto‘g‘ri format';
                    return;
                }
                if (chosen.getTime() <= Date.now()) {
                    hint.textContent = 'Iltimos kelajakdagi vaqtni tanlang';
                    return;
                }

                const formatted = v.replace('T', ' ') + ':00';
                btnSchedule.disabled = true;
                doBanAction(type, id, null, formatted).then(data => {
                    if (data?.success) {
                        const sched = document.getElementById(`${type}-status-${id}`);
                        if (sched) sched.textContent = `Scheduled: ${data.starts_at || formatted}`;
                        showToast(data.message || 'Scheduled', 'success');
                        closeModal();
                    } else {
                        hint.textContent = data?.message || 'Xatolik';
                    }
                }).finally(() => btnSchedule.disabled = false);
            });
        }

        /**
         * handleUserBanButton
         * userBanned param is only initial hint; after request UI will be updated by doBanAction
         */
        function handleUserBanButton(userId, userBanned) {
            const btn = document.getElementById(`user-ban-btn-${userId}`);
            if (!btn) return;

            btn.disabled = true;
            if (userBanned) {
                // request unban immediately
                doBanAction('user', userId, 'unban').finally(() => btn.disabled = false);
            } else {
                // immediate ban for users (no schedule)
                doBanAction('user', userId).finally(() => btn.disabled = false);
            }
        }

        /* Simple toast helper (reuse if you already have one) */
        function showToast(message, type = 'success') {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                Object.assign(container.style, {
                    position: 'fixed',
                    top: '20px',
                    right: '20px',
                    zIndex: 999999
                });
                document.body.appendChild(container);
            }
            const t = document.createElement('div');
            t.textContent = message;
            Object.assign(t.style, {
                background: type === 'success' ? '#16a34a' : '#ef4444',
                color: '#fff',
                padding: '8px 12px',
                borderRadius: '8px',
                marginTop: '8px',
                boxShadow: '0 6px 18px rgba(0,0,0,0.3)',
                fontWeight: 700
            });
            container.appendChild(t);
            setTimeout(() => {
                t.style.opacity = '0';
                setTimeout(() => t.remove(), 300);
            }, 3000);
        }
    </script>






    <script>
        // preserve existing helpers (toggleBan etc.)
        function toggleBan(button) {
            const bannableType = (button.dataset.bannableType || '').trim();
            const bannableId = (button.dataset.bannableId || '').trim();
            if (!bannableType || !bannableId) {
                showToast('No target specified', 'error');
                return;
            }

            button.disabled = true;

            fetch('/admin/ban-unban', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        bannable_type: bannableType,
                        bannable_id: bannableId
                    })
                })
                .then(async res => {
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || !data.success) {
                        showToast(data.message || 'Xatolik yuz berdi', 'error');
                        return;
                    }

                    // server javobiga qarab UIni yangilash
                    if (data.is_banned) {
                        button.textContent = 'User Banned';
                        button.style.background = '#ef4444';
                    } else {
                        button.textContent = 'Ban User';
                        button.style.background = '#6b7280';
                    }

                    showToast(data.message || (data.is_banned ? 'User banned' : 'User unbanned'), 'success');
                })
                .catch(() => {
                    showToast('Server bilan aloqa yo‘q', 'error');
                })
                .finally(() => {
                    button.disabled = false;
                });
        }



        (function ensureToastContainer() {
            if (!document.getElementById('toast-container')) {
                const c = document.createElement('div');
                c.id = 'toast-container';
                c.style.position = 'fixed';
                c.style.top = '20px';
                c.style.right = '20px';
                c.style.zIndex = '9999';
                document.body.appendChild(c);
            }
        })();

        function toggleBanCheckbox(checkbox) {
            const bannableType = checkbox.getAttribute('data-type') ||
                'phone'; // agar checkbox telefon uchun bo'lsa 'phone' qo'yilsin
            const bannableId = checkbox.getAttribute('data-id');
            const isChecked = checkbox.checked;

            checkbox.disabled = true;

            fetch('/admin/ban-unban', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        bannable_type: bannableType,
                        bannable_id: bannableId
                    })
                })
                .then(async res => {
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || !data.success) {
                        showToast(data.message || 'Xatolik yuz berdi', 'error');
                        checkbox.checked = !isChecked; // rollback
                        return;
                    }
                    // update select option attribute (phone select uses data-phone-banned)
                    const select = checkbox.closest('.user-line')?.querySelector('.phone-select');
                    if (select) {
                        const selectedOption = select.options[select.selectedIndex];
                        if (selectedOption) selectedOption.setAttribute('data-phone-banned', data.is_banned ? '1' :
                            '0');
                    }
                    showToast(data.message || 'Success', 'success');
                })
                .catch(() => {
                    showToast('Server bilan aloqa yo‘q', 'error');
                    checkbox.checked = !isChecked;
                })
                .finally(() => {
                    checkbox.disabled = false;
                });
        }


        document.querySelectorAll('.phone-select').forEach(select => {
            select.addEventListener('change', function() {
                const userLine = this.closest('.user-line');
                const phoneCheckbox = userLine.querySelector('.phone-ban-checkbox');
                const userBtn = userLine.querySelector('.ban-toggle-btn[data-type="user"]');
                const selectedOption = this.options[this.selectedIndex];

                const phoneId = selectedOption.value;
                const isPhoneBanned = selectedOption.getAttribute('data-phone-banned') === '1';
                const userBanned = userBtn && userBtn.textContent.includes('Banned');

                if (phoneCheckbox) {
                    phoneCheckbox.setAttribute('data-id', phoneId);
                    phoneCheckbox.checked = isPhoneBanned;
                    phoneCheckbox.disabled = !!userBanned;
                }
            });
        });

        // Centralized modal logic (supports .js-confirm-action or [data-confirm])
        (function() {
            const overlay = document.getElementById('confirmOverlay');
            const step1 = document.getElementById('confirmStep1');
            const step1Text = document.getElementById('confirmStep1Text');
            const step1Cancel = document.getElementById('confirmStep1Cancel');
            const step1Continue = document.getElementById('confirmStep1Continue');

            const step2 = document.getElementById('confirmStep2');
            const confirmInput = document.getElementById('confirmInput');
            const confirmExample = document.getElementById('confirmExample');
            const step2Back = document.getElementById('confirmStep2Back');
            const step2Final = document.getElementById('confirmStep2Final');

            let activeConfig = null;
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content') || '';

            function openConfirm(config) {
                activeConfig = Object.assign({
                    action: '#',
                    method: 'POST',
                    verb: 'Tasdiqlash',
                    requireName: '',
                    text: '' // optional full text
                }, config || {});

                // Priority: explicit text (data-text) -> if absent, use requireName or verb
                const targetText = activeConfig.text && activeConfig.text.trim() ?
                    activeConfig.text :
                    (activeConfig.requireName || activeConfig.verb || 'bu amal');

                step1Text.textContent =
                    `Siz “${ targetText }” ga oid amalni bajarishni boshlamoqchisiz. Davom etishni xohlaysizmi?`;

                if (activeConfig.requireName) {
                    confirmExample.innerHTML = `Masalan: <strong>${ activeConfig.requireName }</strong>`;
                } else {
                    confirmExample.innerHTML = '';
                }

                // show step1
                step2.style.display = 'none';
                step1.style.display = 'block';
                overlay.style.display = 'flex';
                overlay.setAttribute('aria-hidden', 'false');
                confirmInput.value = '';
                step2Final.disabled = true;
                setTimeout(() => step1Continue.focus(), 60);
            }

            function closeConfirm() {
                overlay.style.display = 'none';
                overlay.setAttribute('aria-hidden', 'true');
                activeConfig = null;
                confirmInput.value = '';
            }

            function doSubmit() {
                if (!activeConfig) return;
                const action = activeConfig.action;
                const method = (activeConfig.method || 'POST').toUpperCase();

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = action;
                form.style.display = 'none';

                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = '_token';
                tokenInput.value = csrf;
                form.appendChild(tokenInput);

                if (method !== 'POST') {
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = method;
                    form.appendChild(methodInput);
                }

                document.body.appendChild(form);
                form.submit();
            }

            // Step 1 handlers
            step1Cancel.addEventListener('click', () => closeConfirm());
            step1Continue.addEventListener('click', () => {
                if (activeConfig && activeConfig.requireName) {
                    // show step2 only if requireName set (user/department deletes)
                    step1.style.display = 'none';
                    step2.style.display = 'block';
                    confirmInput.value = '';
                    confirmInput.focus();
                    step2Final.disabled = true;
                } else {
                    // submit right away (message-group cancel & refresh)
                    doSubmit();
                    closeConfirm();
                }
            });

            // Step 2 handlers
            step2Back.addEventListener('click', () => {
                step2.style.display = 'none';
                step1.style.display = 'block';
                step1Continue.focus();
            });

            confirmInput.addEventListener('input', () => {
                if (!activeConfig) return;
                step2Final.disabled = (confirmInput.value !== (activeConfig.requireName || ''));
            });

            step2Final.addEventListener('click', () => {
                if (!activeConfig) return;
                if (activeConfig.requireName && confirmInput.value !== activeConfig.requireName) {
                    showToast('Kiritilgan nom mos kelmadi', 'error');
                    return;
                }
                doSubmit();
                closeConfirm();
            });

            // overlay click / ESC
            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) closeConfirm();
            });
            window.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && overlay.style.display === 'flex') closeConfirm();
            });

            // Attach handler for both `.js-confirm-action` and `[data-confirm]` (legacy)
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.js-confirm-action, [data-confirm]');
                if (!btn) return;
                e.preventDefault();

                const action = btn.getAttribute('data-action') || btn.getAttribute('href') || '#';
                const method = (btn.getAttribute('data-method') || 'POST').toUpperCase();
                const verb = btn.getAttribute('data-verb') || (method === 'DELETE' ? 'O\'chirish' :
                    'Tasdiqlash');
                const requireName = (btn.getAttribute('data-require-name') || '').trim();
                const text = (btn.getAttribute('data-text') || '').trim();

                openConfirm({
                    action: action,
                    method: method,
                    verb: verb,
                    requireName: requireName,
                    text: text
                });
            });

            // expose openConfirm if needed
            window.openConfirm = openConfirm;
        })();

        // Simple toast
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return console.warn('Toast container yo‘q');

            const toast = document.createElement('div');
            toast.innerHTML = message; // HTML allowed
            toast.style.padding = '10px 14px';
            toast.style.borderRadius = '8px';
            toast.style.marginTop = '8px';
            toast.style.color = '#fff';
            toast.style.fontWeight = '600';
            toast.style.boxShadow = '0 6px 20px rgba(0,0,0,0.25)';
            toast.style.maxWidth = '320px';
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 220ms ease, transform 220ms ease';
            toast.style.transform = 'translateY(-6px)';

            toast.style.background = type === 'success' ? '#22c55e' : '#ef4444';

            container.appendChild(toast);
            // show
            requestAnimationFrame(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateY(0)';
            });

            // auto remove
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-6px)';
                setTimeout(() => toast.remove(), 250);
            }, 3000);
        }

        // Peer filter init (unchanged)
        // (function() {
        //     function debounce(fn, wait) {
        //         let t;
        //         return function(...args) {
        //             clearTimeout(t);
        //             t = setTimeout(() => fn.apply(this, args), wait);
        //         };
        //     }

        //     function initPeerLists() {
        //         document.querySelectorAll('.mg-card').forEach(card => {
        //             const search = card.querySelector('.peer-search');
        //             const list = card.querySelector('.peer-list');
        //             const failedBtn = card.querySelector('.peer-filter-failed');
        //             const clearBtn = card.querySelector('.peer-clear');

        //             if (!list) return;

        //             const rows = Array.from(list.querySelectorAll('.peer-row'));

        //             const applyFilter = (query = '', failedOnly = false) => {
        //                 const q = query.trim().toLowerCase();
        //                 rows.forEach(row => {
        //                     const peer = (row.dataset.peer || '').toLowerCase();
        //                     const hasFailed = parseInt(row.dataset.failed || '0') > 0;
        //                     const matchesQuery = q === '' || peer.includes(q);
        //                     const matchesFailed = !failedOnly || hasFailed;
        //                     row.style.display = (matchesQuery && matchesFailed) ? 'flex' : 'none';
        //                 });
        //             };

        //             const debouncedFilter = debounce((e) => {
        //                 applyFilter(e.target.value, failedBtn && failedBtn.classList.contains(
        //                     'active'));
        //             }, 160);

        //             if (search) {
        //                 search.addEventListener('input', debouncedFilter);
        //             }

        //             if (failedBtn) {
        //                 failedBtn.addEventListener('click', () => {
        //                     failedBtn.classList.toggle('active');
        //                     failedBtn.textContent = failedBtn.classList.contains('active') ?
        //                         'Failed only ✓' : 'Failed only';
        //                     applyFilter(search ? search.value : '', failedBtn.classList.contains(
        //                         'active'));
        //                 });
        //             }

        //             if (clearBtn) {
        //                 clearBtn.addEventListener('click', () => {
        //                     if (search) search.value = '';
        //                     failedBtn.classList.remove('active');
        //                     applyFilter('', false);
        //                 });
        //             }

        //             applyFilter('', false);
        //         });
        //     }

        //     if (document.readyState === 'loading') {
        //         document.addEventListener('DOMContentLoaded', initPeerLists);
        //     } else {
        //         initPeerLists();
        //     }
        // })();

        // toggleUsersList kept as before
        function toggleUsersList() {
            const list = document.getElementById('usersList');
            if (!list) return;
            list.style.display = (list.style.display === 'none' || !list.style.display) ? 'block' : 'none';
        }

        // deleteUser wrapper kept for compatibility (uses modal)
        function deleteUser(userId) {
            const btn = document.querySelector(`.user-line[data-user-id="${userId}"] .js-confirm-action[data-action]`);
            if (btn) {
                btn.click();
                return;
            }
            openConfirm && openConfirm({
                action: `/users/${userId}`,
                method: 'DELETE',
                verb: 'Foydalanuvchini o‘chirish',
                requireName: ''
            });
        }
    </script>

</body>

</html>
