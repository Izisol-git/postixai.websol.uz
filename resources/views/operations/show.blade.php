@extends('layouts.app')

@section('title', __('messages.group.show_title', ['id' => $operation->id ?? '']))
@section('show-back', true)
@section('page-title')
    {{ __('messages.group.show_title', ['id' => $operation->id ?? '']) }}
@endsection

@section('page-subtitle')
    {{ __('messages.group.show_subtitle',['text' => $operation->message_text ?? '']) }}
@endsection

@section('content')
<div class="container-fluid dark-mode-root" style="overflow-x:hidden;">
    <style>
        :root{
            --bg: #0f1720;
            --card: #0b1220;
            --muted: #9aa4b2;
            --text: #e6eef6;
            --accent-error-dark: #ff6b6b;
            --accent-error-light: #ff9999;
            --border: rgba(255,255,255,0.06);
        }
        .dark-mode-root{ background: var(--bg); color: var(--text); padding: 1.25rem; }
        .card{ background: var(--card); border: 1px solid var(--border); padding: 1rem; margin-bottom:1rem; border-radius:.5rem; }
        .peer-dropdown{ cursor:pointer; padding:.5rem; background: rgba(255,255,255,0.02); border-radius:.35rem; margin-bottom:.25rem; }
        .peer-content{ display:none; padding:.5rem 1rem; border-top:1px solid var(--border); margin-top:.25rem; max-height: 400px; overflow-y:auto; }
        .peer-row{ margin-bottom:.75rem; }
        .status-badge{ background: rgba(255,255,255,0.02); color: var(--muted); padding: .25rem .5rem; border-radius:.25rem; font-size:.85rem; }
        .failed-text-dark{ color: var(--accent-error-dark); font-weight:500; }
        .failed-text-light{ color: var(--accent-error-light); font-weight:500; }
        code{ background: rgba(255,255,255,0.02); padding:.05rem .35rem; border-radius:.25rem; color:var(--muted); }
        .small.text-muted{ color: var(--muted); }
        .compact { font-size:1rem; }
        select.status-filter {
    margin-bottom:1rem;
    background: #0b1220; /* fon dark card bilan uyg'unlashadi */
    color: #e6eef6;       /* matn yorqinroq, o'qilishi qulay */
    border:1px solid rgba(255,255,255,0.06);
    padding:.35rem .5rem;
    border-radius:.35rem;
}

select.status-filter option {
    background: #0b1220; /* har bir option fonini ham dark qilish */
    color: #e6eef6;      /* o'qilishi uchun yorqin matn */
}
 
        .btn-toggle { margin-bottom:1rem; cursor:pointer; background: rgba(255,255,255,0.02); border:1px solid var(--border); color: var(--text); padding:.35rem .75rem; border-radius:.35rem; margin-right:.5rem; }
        select.status-filter{ margin-bottom:1rem; background: rgba(255,255,255,0.02); color: var(--text); border:1px solid var(--border); padding:.35rem .5rem; border-radius:.35rem; }
    </style>

    @php
        $allPeers = $operation->messages->groupBy('peer');
        $currentStatus = request()->get('status', '');
    @endphp

    <div>
        <select class="status-filter" onchange="location = '?status=' + this.value">
            <option value="">All Statuses</option>
            @foreach($operation->messages->pluck('status')->unique() as $status)
                <option value="{{ $status }}" @if($status==$currentStatus) selected @endif>{{ $status }}</option>
            @endforeach
        </select>
    </div>

    @foreach($allPeers as $peer => $peerMsgs)
        @php
            $peerKey = $peer ?: __('messages.layout.unknown_peer');
            if($currentStatus){
                $peerMsgs = $peerMsgs->filter(fn($m)=>($m->status??'')==$currentStatus);
            }
        @endphp
        @if($peerMsgs->count() > 0)
        <div class="card peer-row compact">
            <div class="peer-dropdown" onclick="
                let content = this.nextElementSibling;
                document.querySelectorAll('.peer-content').forEach(p=>{if(p!==content)p.style.display='none'});
                content.style.display = content.style.display==='block'?'none':'block';
            ">
                <strong>{{ $peerKey }}</strong> <span class="small text-muted">({{ $peerMsgs->count() }} messages)</span>
            </div>
            <div class="peer-content">
                @foreach($peerMsgs->sortBy('send_at') as $msg)
                    @php $failedClass = (($msg->status??'')==='failed' || !empty($msg->error_key)) ? (app()->getLocale() === 'dark' ? 'failed-text-dark':'failed-text-light') : ''; @endphp
                    <div class="card p-2 mb-2" style="background: rgba(255,255,255,0.02); border-radius:.35rem;">
                        <div class="d-flex justify-content-between mb-1">
                            <div class="small text-muted">
                                <strong>ID:</strong> {{ $msg->id }} &middot;
                                <strong>Status:</strong> {{ $msg->status ?? 'n/a' }} &middot;
                                <strong>Sent at:</strong> {{ $msg->sent_at ? \Carbon\Carbon::parse($msg->sent_at)->format('Y-m-d H:i:s') : '-' }}
                            </div>
                            <div class="small text-muted">
                                <code class="{{ $failedClass }}">{{ $msg->error_key ? __('messages.errors.' .$msg->error_key) : '--' }}</code>
                            </div>
                        </div>
                        {{-- <div style="white-space:pre-wrap;">{!! nl2br(e($msg->message_text ?? '')) !!}</div> --}}
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    @endforeach

</div>
@endsection