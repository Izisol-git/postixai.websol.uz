@extends('admin.layouts.app')

@section('title', __('messages.admin.dashboard'))
@section('page-title', __('messages.admin.dashboard'))
@section('show-back', false)

@section('content')

@php
// Normalize chart data to arrays so JS receives stable structures
$chartUsersArr = (is_object($chartUsers) && method_exists($chartUsers, 'toArray')) ? $chartUsers->toArray() : (array)($chartUsers ?? []);
$chartPhonesArr = (is_object($chartPhones) && method_exists($chartPhones, 'toArray')) ? $chartPhones->toArray() : (array)($chartPhones ?? []);
$chartGroupsArr = (is_object($chartGroups) && method_exists($chartGroups, 'toArray')) ? $chartGroups->toArray() : (array)($chartGroups ?? []);
$chartMessagesArr = (is_object($chartMessages) && method_exists($chartMessages, 'toArray')) ? $chartMessages->toArray() : (array)($chartMessages ?? []);
$colorsArr = $colors ?? [];
@endphp

<style>
/* card accents + improved UI */
.dept-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 16px;
  margin-bottom: 1rem;
}

/* reuse theme vars from layout where possible */
.sa-card{
    background:var(--card);
    border-radius:16px;
    padding:16px;
    color:var(--text);
    box-shadow:0 6px 18px rgba(2,6,23,0.55);
    transition: transform .18s, box-shadow .18s;
    position: relative;
    overflow: hidden;
    border: 1px solid var(--muted-2);

    /* NEW: keep content and footer consistent -> footer always bottom */
    display: flex;
    flex-direction: column;
}

/* left accent to highlight contour */
.sa-card::before{
  content: "";
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 6px;
  background: var(--accent);
  border-radius: 0 8px 8px 0;
  opacity: 0.98;
}

.sa-deleted{
    border-color: #7f1d1d;
    box-shadow: 0 8px 28px rgba(139, 16, 20, 0.18);
}
.sa-deleted::before{
  background: linear-gradient(180deg,#dc2626,#9f1239);
}

.sa-card:hover{
    transform:translateY(-4px);
    box-shadow:0 12px 36px rgba(2,6,23,0.65);
}

.sa-muted{
    color:var(--muted);
}

/* ensure stats area can grow and push footer down */
.sa-card .card-stats {
    flex: 1 1 auto; /* allow grow */
}

/* make footer (link) always at bottom of card */
.sa-card .card-footer {
    margin-top: 12px;
}

/* style for footer button placement */
.sa-card .card-footer a.btn {
    width: 100%;
}

/* Legend style */
.legend{
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    justify-content:center;
    margin-top:8px;
}
.legend-item{
    display:flex;
    gap:6px;
    align-items:center;
    font-size:0.82rem;
    color:var(--muted);
    opacity:0.95;
    cursor:pointer;
}
.legend-dot{
    width:12px;
    height:12px;
    border-radius:3px;
    display:inline-block;
}

/* Pies layout */
.pies-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 14px;
  align-items: start;
  margin-top: 12px;
}
.pie-card {
  text-align: center;
  min-height: 180px;
  padding: 12px;
  border-radius: 12px;
  border: 1px solid var(--muted-2);
  background: var(--card);
  box-shadow: 0 8px 24px rgba(2,6,23,0.48);
  display: flex;
  flex-direction: column;
}

/* make pie body flexible so totals/footer stay aligned */
.pie-card .pie-body { flex: 1 1 auto; display:flex; flex-direction:column; justify-content:center; }

/* canvas sizing */
.pie-canvas {
  width: 100% !important;
  height: 140px !important;
  max-width: 220px;
  margin: 0 auto;
  display: block;
}

/* responsive */
@media (max-width:1000px) { .pie-canvas { height:160px !important; } }
@media (max-width:600px) { .pie-canvas { height:200px !important; } }
</style>

{{-- Filters --}}
<form method="GET" class="mb-4">
    <div class="d-flex flex-wrap gap-2">
        @php
        $ranges=[
            'all'=>__('messages.admin.all_time'),
            'year'=>__('messages.admin.all_year'),
            'month'=>__('messages.admin.month'),
            'week'=>__('messages.admin.week'),
            'day'=>__('messages.admin.day')
        ];
        @endphp

        @foreach($ranges as $k=>$label)
        <button name="range"
                value="{{ $k }}"
                class="btn btn-sm {{ ($range ?? 'all') == $k ? 'btn-primary':'btn-outline-secondary' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>
</form>

{{-- Create --}}
<div class="d-flex justify-content-end mb-4">
    <a href="{{ route('departments.create') }}"
       class="btn btn-success fw-semibold shadow">
        + {{ __('messages.admin.create_department') }}
    </a>
</div>

{{-- Departments --}}
<div class="dept-grid mb-4">
@foreach($deptStats as $d)
@php $deleted = !is_null($d->deleted_at); @endphp
<div class="sa-card h-100 {{ $deleted ? 'sa-deleted' : '' }}">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <h6 class="fw-bold mb-0">{{ $d->name }}</h6>
        @if($deleted)
        <span class="badge bg-danger">{{ __('messages.admin.deleted') }}</span>
        @endif
    </div>

    @if($deleted)
    <div class="small text-danger mb-2">
        🗑 {{ \Carbon\Carbon::parse($d->deleted_at)->format('d.m.Y H:i') }}
    </div>
    @endif

    <div class="card-stats">
        <div class="mb-2 sa-muted">{{ __('messages.admin.users') }}: <b>{{ $d->users_count }}</b></div>
        <div class="mb-2 sa-muted">{{ __('messages.admin.phones') }}: <b>{{ $d->active_phones_count }}</b></div>
        <div class="mb-2 sa-muted">{{ __('messages.admin.operations') }}: <b>{{ $d->message_groups_count }}</b></div>
        <div class="mb-3 sa-muted">{{ __('messages.admin.messages_count') }}: <b>{{ $d->telegram_messages_count }}</b></div>
    </div>

    <div class="card-footer">
        <a href="{{ route('departments.show',$d->id) }}" class="btn btn-sm btn-outline-warning">
            {{ __('messages.admin.details') }}
        </a>
    </div>
</div>
@endforeach
</div>

{{-- Pies --}}
@php
$charts = [
    ['id' => 'chartUsers', 'labels' => array_keys($chartUsersArr), 'values' => array_values($chartUsersArr), 'total' => $totals['users'] ?? array_sum(array_values($chartUsersArr)), 'title' => __('messages.admin.users')],
    ['id' => 'chartPhones', 'labels' => array_keys($chartPhonesArr), 'values' => array_values($chartPhonesArr), 'total' => $totals['phones'] ?? array_sum(array_values($chartPhonesArr)), 'title' => __('messages.admin.phones')],
    ['id' => 'chartGroups', 'labels' => array_keys($chartGroupsArr), 'values' => array_values($chartGroupsArr), 'total' => $totals['groups'] ?? array_sum(array_values($chartGroupsArr)), 'title' => __('messages.admin.operations')],
    ['id' => 'chartMessages', 'labels' => array_keys($chartMessagesArr), 'values' => array_values($chartMessagesArr), 'total' => $totals['messages'] ?? array_sum(array_values($chartMessagesArr)), 'title' => __('messages.admin.messages_count')],
];
@endphp

<div class="pies-row">
    @foreach($charts as $c)
    <div class="pie-card">
        <div class="pie-title sa-muted mb-2">{{ $c['title'] }}</div>

        <div class="pie-body">
            <canvas id="{{ $c['id'] }}" class="pie-canvas"></canvas>
            <div class="fw-bold mt-2" id="total{{ \Illuminate\Support\Str::studly(str_replace('chart','',$c['id'])) }}">{{ $c['total'] }}</div>
        </div>

        <div class="legend" id="legend{{ \Illuminate\Support\Str::studly(str_replace('chart','',$c['id'])) }}">
            @php $i=0; @endphp
            @foreach($c['labels'] as $name)
            <div class="legend-item" data-index="{{ $i }}">
                <span class="legend-dot" style="background: {{ $colorsArr[$i % max(1,count($colorsArr))] ?? '' }}"></span>
                <span>{{ $name }} ({{ $c['values'][$i] ?? 0 }})</span>
            </div>
            @php $i++; @endphp
            @endforeach
        </div>
    </div>
    @endforeach
</div>

{{-- Pass arrays to JS in a robust way --}}
<script>
const chartsData = {
    users: {
        labels: {!! json_encode(array_values($charts[0]['labels'])) !!},
        values: {!! json_encode(array_values($charts[0]['values'])) !!},
    },
    phones: {
        labels: {!! json_encode(array_values($charts[1]['labels'])) !!},
        values: {!! json_encode(array_values($charts[1]['values'])) !!},
    },
    groups: {
        labels: {!! json_encode(array_values($charts[2]['labels'])) !!},
        values: {!! json_encode(array_values($charts[2]['values'])) !!},
    },
    messages: {
        labels: {!! json_encode(array_values($charts[3]['labels'])) !!},
        values: {!! json_encode(array_values($charts[3]['values'])) !!},
    }
};
let colors = {!! json_encode($colorsArr) !!} || [];

/* If colors not provided, generate pleasant HSL colors */
function genColors(n){
  return Array.from({length:n}, (_,i) => `hsl(${Math.round(i*360/n)} 70% 55%)`);
}
if(!colors || colors.length === 0){
  const maxLabels = Math.max(
    chartsData.users.labels.length,
    chartsData.phones.labels.length,
    chartsData.groups.labels.length,
    chartsData.messages.labels.length
  ) || 6;
  colors = genColors(maxLabels);
}

const hidden = new Set();

function masked(arr){
  return arr.map((v,i) => hidden.has(i) ? 0 : v);
}

function sum(arr){ return arr.reduce((a,b)=>a+(Number(b)||0),0); }

function buildDoughnut(ctx, labels, data){
  return new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: labels,
      datasets: [{
        data: masked(data),
        backgroundColor: colors,
        borderColor: 'rgba(255,255,255,0.04)',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '60%',
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: function(context){
              const val = context.raw ?? 0;
              return `${context.label}: ${val}`;
            }
          }
        }
      }
    }
  });
}

const chartObjs = {};

document.addEventListener('DOMContentLoaded', () => {
  // Create charts if canvases exist
  const elU = document.getElementById('chartUsers');
  if(elU) chartObjs.users = buildDoughnut(elU.getContext('2d'), chartsData.users.labels, chartsData.users.values);

  const elP = document.getElementById('chartPhones');
  if(elP) chartObjs.phones = buildDoughnut(elP.getContext('2d'), chartsData.phones.labels, chartsData.phones.values);

  const elG = document.getElementById('chartGroups');
  if(elG) chartObjs.groups = buildDoughnut(elG.getContext('2d'), chartsData.groups.labels, chartsData.groups.values);

  const elM = document.getElementById('chartMessages');
  if(elM) chartObjs.messages = buildDoughnut(elM.getContext('2d'), chartsData.messages.labels, chartsData.messages.values);

  function updateAll(){
    if(chartObjs.users) { chartObjs.users.data.datasets[0].data = masked(chartsData.users.values); chartObjs.users.update(); }
    if(chartObjs.phones) { chartObjs.phones.data.datasets[0].data = masked(chartsData.phones.values); chartObjs.phones.update(); }
    if(chartObjs.groups) { chartObjs.groups.data.datasets[0].data = masked(chartsData.groups.values); chartObjs.groups.update(); }
    if(chartObjs.messages) { chartObjs.messages.data.datasets[0].data = masked(chartsData.messages.values); chartObjs.messages.update(); }

    // update totals
    const elU = document.getElementById('totalUsers');
    const elP = document.getElementById('totalPhones');
    const elG = document.getElementById('totalGroups');
    const elM = document.getElementById('totalMessages');
    if(elU) elU.innerText = sum(masked(chartsData.users.values));
    if(elP) elP.innerText = sum(masked(chartsData.phones.values));
    if(elG) elG.innerText = sum(masked(chartsData.groups.values));
    if(elM) elM.innerText = sum(masked(chartsData.messages.values));
  }

  // Legend item handlers (scoped per pie-card)
  document.querySelectorAll('.pie-card').forEach(card => {
    const legendItems = card.querySelectorAll('.legend-item');
    legendItems.forEach(el => {
      el.addEventListener('click', () => {
        const i = Number(el.dataset.index);
        if(hidden.has(i)) hidden.delete(i); else hidden.add(i);
        el.style.opacity = hidden.has(i) ? 0.35 : 1.0;
        updateAll();
      });
    });
  });

  // Fill legend colors
  document.querySelectorAll('.legend-dot').forEach((el,i)=>{
    el.style.background = colors[i % colors.length];
  });

  updateAll();
});
</script>

@endsection
