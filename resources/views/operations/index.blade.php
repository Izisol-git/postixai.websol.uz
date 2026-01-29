@extends('layouts.app')

@section('title', __('messages.operations.title') . ' — ' . ($department->name ?? ''))
@section('page-title', $department->name . ' — ' . __('messages.operations.title'))

@section('content')
<style>
:root{ --bg:#071427; --card:#0f2233; --text:#e7f4ff; --muted:#9fb7dd; --accent:#3b82f6; --accent2:#facc15; }

/* Compact filters styles */
.filter-bar .form-control-sm,
.filter-bar .form-select-sm,
.filter-bar .btn-sm {
  height: 34px;
  padding: .25rem .5rem;
  font-size: .85rem;
}

.filter-bar .input-group > .form-control,
.filter-bar .input-group > .form-select {
  height: 34px;
}

.filter-bar .form-select-sm {
  min-width: 120px;
  max-width: 220px;
}

.filter-bar .user-select {
  min-width: 160px;
  max-width: 240px;
}

.filter-bar .search-input {
  width: 220px;
  min-width: 120px;
}

.filter-bar .date-input {
  width: 135px;
  min-width: 110px;
}

/* Reduce gaps and wrap nicely on small screens */
.filter-bar {
  gap: 6px;
  align-items: center;
  display: flex;
  flex-wrap: wrap;
}

/* Message group: clearer separation and nicer corners */
.message-group {
  position: relative;
  margin-bottom: 18px; /* increased gap so groups don't blur together */
  padding: 14px;       /* a bit more inner spacing */
  border-radius: 10px;  /* rounder corners */
  background-color: rgba(255,255,255,0.02);
  border: 1px solid rgba(255,255,255,0.04);
  box-shadow: 0 6px 18px rgba(0,0,0,0.45);
  overflow: hidden;
}

/* Accent bar on the left to show boundaries */
.message-group::before{
  content: '';
  position: absolute;
  left: 0; top: 0; bottom: 0;
  width: 4px;
  background: var(--accent);
  border-top-left-radius: 10px;
  border-bottom-left-radius: 10px;
}

/* subtle hover lift to emphasize group boundaries */
.message-group:hover{
  transform: translateY(-3px);
  transition: transform .16s ease-out, box-shadow .16s ease-out;
  box-shadow: 0 12px 30px rgba(0,0,0,0.55);
}

/* Make action button slightly tighter */
.filter-bar .btn-search {
  padding: .25rem .6rem;
}

/* Tweak badges and chips a bit for compact look */
.status-badge { padding:6px 8px; font-size:.85rem; }
.status-chip { padding:3px 6px; font-size:.78rem; }

/* Make peer-row denser and separated */
.peer-row { padding:6px 0; }
.peer-row + .peer-row { border-top: 1px solid rgba(255,255,255,0.03); padding-top:10px; margin-top:8px; }

/* Replace the old hr look with a very faint divider (keeps visual flow) */
.group-divider { border-top: 1px solid rgba(255,255,255,0.03); margin:10px 0; }

/* Responsive: stack filters on very small screens */
@media (max-width: 640px) {
  .filter-bar { gap: 8px; }
  .filter-bar .search-input { width: 100%; }
  .filter-bar .user-select, .filter-bar .form-select-sm { width: 48%; }
  .filter-bar .date-input { width: 48%; }
}
</style>

<div class="container">
  @if(session('success'))
    <div class="alert alert-success mb-3">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger mb-3">{{ session('error') }}</div>
  @endif

  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h5 class="mb-0">{{ __('messages.operations.title') }}</h5>
      <div class="text-muted small">{{ __('messages.operations.subtitle', ['dept' => $department->name ]) }}</div>
    </div>

    {{-- Compact filters form --}}
    <form method="GET" class="filter-bar">
      {{-- User --}}
      <select name="user_id" class="form-select form-select-sm user-select" title="{{ __('messages.find.filter_all_users') }}">
        <option value="">{{ __('messages.find.filter_all_users') ?? '— Barcha foydalanuvchilar —' }}</option>
        @foreach($users as $u)
          <option value="{{ $u->id }}" {{ (string)($selectedUserId ?? '') === (string)$u->id ? 'selected' : '' }}>
            {{ $u->name ?? $u->username ?? ('#'.$u->id) }}
          </option>
        @endforeach
      </select>

      {{-- Search (compact) --}}
      <input name="q" value="{{ $q ?? '' }}" class="form-control form-control-sm search-input" type="search" placeholder="{{ __('messages.operations.search_placeholder') }}">

      {{-- Status --}}
      <select name="status" class="form-select form-select-sm" title="{{ __('messages.operations.filter_all_status') }}">
        <option value="">{{ __('messages.operations.filter_all_status') }}</option>
        <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>{{ __('messages.operations.status_pending') }}</option>
        <option value="scheduled" {{ ($status ?? '') === 'scheduled' ? 'selected' : '' }}>{{ __('messages.operations.status_scheduled') }}</option>
        <option value="sent" {{ ($status ?? '') === 'sent' ? 'selected' : '' }}>{{ __('messages.operations.status_sent') }}</option>
        <option value="canceled" {{ ($status ?? '') === 'canceled' ? 'selected' : '' }}>{{ __('messages.operations.status_canceled') }}</option>
        <option value="failed" {{ ($status ?? '') === 'failed' ? 'selected' : '' }}>{{ __('messages.operations.status_failed') }}</option>
      </select>

      {{-- Date range compact --}}
      <input type="date" name="from" value="{{ $from ?? '' }}" class="form-control form-control-sm date-input" title="{{ __('messages.operations.filter_from') }}" />
      <input type="date" name="to" value="{{ $to ?? '' }}" class="form-control form-control-sm date-input" title="{{ __('messages.operations.filter_to') }}" />

      {{-- Submit --}}
      <button class="btn btn-sm btn-primary btn-search" type="submit">{{ __('messages.operations.btn_search') }}</button>
    </form>
  </div>

  {{-- Totals --}}
  <div class="row g-3 mb-3">
    <div class="col-md-3">
      <div class="card p-3 text-center">
        <div class="text-muted small">{{ __('messages.operations.total_groups') }}</div>
        <h3 class="mb-0">{{ number_format($messageGroupsTotal ?? 0) }}</h3>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3 text-center">
        <div class="text-muted small">{{ __('messages.operations.total_messages') }}</div>
        <h3 class="mb-0">{{ number_format($telegramMessagesTotal ?? 0) }}</h3>
      </div>
    </div>
    <div class="col-md-6 text-end small-muted align-self-center">
      {{ __('messages.operations.showing') }} <strong>{{ $messageGroups->count() }}</strong> / {{ $messageGroups->total() }}
    </div>
  </div>

  {{-- Top pagination (added) --}}
  <div class="mt-3 d-flex justify-content-center">
    {{ $messageGroups->withQueryString()->links('pagination::bootstrap-5') }}
  </div>

  {{-- Groups list (keeps previous compact styles) --}}
  <div>
    @foreach ($messageGroups as $group)
      @php
        $gid = $group->id;
        $stat = $textStats->get($gid);
        $peers = $peerStatusByGroup[$gid] ?? [];
        $total = $groupTotals[$gid] ?? [];
        $sample = $group->message_text ?? null;
      @endphp

      <div class="message-group">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div style="font-weight:800;">{{ __('messages.operations.group') }} #{{ $gid }}</div>
            <div class="text-muted small">
              {{ __('messages.operations.by_user') }}:
              {{ optional($group->phone->user)->name ?? '—' }}
              ({{ optional($group->phone)->phone ?? '—' }})
            </div>
          </div>

          <div class="d-flex gap-2 align-items-center">
            @php
              $pendingCount = ($total['pending'] ?? 0) + ($total['scheduled'] ?? 0);
            @endphp
            <a href="{{ route('admin.operations.show', $gid) }}" class="btn btn-sm btn-outline-primary">{{__('messages.operations.show')}}</a>
            @if($group->status !=='canceled')
              <form method="POST" action="{{ route('message-groups.cancel', $gid) }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger js-confirm-action" data-text="{{ __('messages.operations.confirm_cancel_text', ['id' => $gid]) }}">
                  {{ __('messages.operations.btn_cancel') }}
                </button>
              </form>
            @endif
          </div>
        </div>

        <div class="group-divider"></div>

        <div class="message-text mb-2">
          <strong style="color:var(--accent);">{{ __('messages.operations.text_label') }}:</strong>
          <span style="font-weight:600;">{{ $sample ?? '—' }}</span>
        </div>

        <div>
          <div class="d-flex gap-2 flex-wrap mb-3">
            @foreach (['sent','failed','canceled','scheduled','pending'] as $k)
              @php $c = $total[$k] ?? 0; @endphp
              @if($c > 0)
                <span class="status-badge status-{{ $k }}">
                  {!! $k === 'sent' ? '✓' : ($k === 'failed' ? '✕' : ($k === 'canceled' ? '⦸' : '⏳')) !!}
                  <span style="opacity:.9; margin-left:6px;">{{ __('messages.operations.status_'.$k) }}</span>
                  <span class="ms-1" style="font-weight:900; margin-left:6px;">{{ $c }}</span>
                </span>
              @endif
            @endforeach
          </div>

          <div style="max-height:220px; overflow:auto; padding-right:6px;">
            @forelse($peers as $peer => $statuses)
              @php $peerTotal = array_sum($statuses); @endphp
              <div class="peer-row d-flex justify-content-between align-items-center">
                <div style="min-width:0; overflow:hidden;">
                  <strong style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:400px;">{{ $peer }}</strong>
                  <div class="text-muted small">{{ __('messages.operations.peer_total') }}: {{ $peerTotal }}</div>
                </div>

                <div class="d-flex gap-2 flex-wrap align-items-center">
                  @foreach(['sent','failed','canceled','scheduled','pending'] as $kk)
                    @php $cnt = $statuses[$kk] ?? 0; @endphp
                    @if($cnt > 0)
                      <div class="status-chip">
                        {!! $kk === 'sent' ? '✓' : ($kk === 'failed' ? '✕' : ($kk === 'canceled' ? '⦸' : '⏳')) !!}
                        <span style="opacity:.9; margin-left:6px;">{{ __('messages.operations.status_'.$kk) }}</span>
                        <strong style="color:var(--accent); margin-left:6px;">{{ $cnt }}</strong>
                      </div>
                    @endif
                  @endforeach
                </div>
              </div>
            @empty
              <div class="text-muted small">— {{ __('messages.operations.no_peers') ?? 'Peerlar yo‘q' }}</div>
            @endforelse
          </div>

          @php
            $all = array_sum($total);
            $sent = $total['sent'] ?? 0;
            $rate = $all > 0 ? round(($sent / $all) * 100) : 0;
          @endphp

          <div class="d-flex gap-3 flex-wrap mt-3" style="font-weight:700;">
            <div>{{ __('messages.operations.total') }}: <span style="color:var(--accent)">{{ $all }}</span></div>
            <div>{{ __('messages.operations.total_sent') }}: <span style="color:#22c55e">{{ $sent }}</span></div>
            <div>{{ __('messages.operations.rate') }}: <span style="color:var(--accent2)">{{ $rate }}%</span></div>
          </div>
        </div>
      </div>
    @endforeach

    {{-- Bottom pagination (kept) --}}
    <div class="mt-3 d-flex justify-content-center">
      {{ $messageGroups->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
  </div>
</div>

{{-- Confirm modal --}}
<div id="confirmOverlay" style="display:none; position:fixed; inset:0; align-items:center; justify-content:center; z-index:99998;">
  <div style="width:100%; max-width:520px; background:var(--card); color:var(--text); border-radius:12px; padding:18px; box-shadow:0 20px 60px rgba(0,0,0,.6);">
    <h5 id="confirmTitle">{{ __('messages.operations.confirm') }}</h5>
    <p id="confirmDesc" class="text-muted"></p>

    <div id="confirmStep1" class="d-flex justify-content-end gap-2">
      <button id="confirmCancel" class="btn btn-sm btn-secondary">{{ __('messages.operations.cancel') }}</button>
      <button id="confirmContinue" class="btn btn-sm btn-primary">{{ __('messages.operations.continue') }}</button>
    </div>
  </div>
</div>

<script>
(function(){
  const overlay = document.getElementById('confirmOverlay');
  const desc = document.getElementById('confirmDesc');
  const cancel = document.getElementById('confirmCancel');
  const cont = document.getElementById('confirmContinue');

  let activeForm = null;

  function openConfirm(text, form) {
    activeForm = form;
    desc.textContent = text || '{{ __('messages.operations.confirm_text_default') }}';
    overlay.style.display = 'flex';
  }

  function closeConfirm() {
    overlay.style.display = 'none';
    activeForm = null;
  }

  cancel.addEventListener('click', closeConfirm);
  cont.addEventListener('click', function(){
    if (!activeForm) return closeConfirm();
    activeForm.submit();
  });

  document.addEventListener('click', function(e){
    const btn = e.target.closest('.js-confirm-action');
    if (!btn) return;
    e.preventDefault();

    const form = btn.closest('form');
    const txt = btn.getAttribute('data-text') || btn.dataset.text || '';
    openConfirm(txt, form);
  });
})();
</script>
@endsection
