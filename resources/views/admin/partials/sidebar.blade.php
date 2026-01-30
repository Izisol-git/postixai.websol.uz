
<div id="sidebarTitle" class="mb-3">
    <h5 class="mb-1">{{ $department->name ?? __('messages.layout.departments') }}</h5>
    @if(isset($department))
        <div class="small text-muted">
            ID: {{ $department->id }} • {{ $department->users_count ?? '-' }} {{ __('messages.admin.users') }}
        </div>
    @endif
</div>

<div class="sidebar-section">
    <h6>{{ __('messages.admin.navigation') }}</h6>
    <a href="{{ route('superadmin.departments.show', $department->id ?? '#') }}" class="sidebar-link btn-sidebar">🏠{{ __('messages.admin.dashboard') }}</a>
    <a href="{{ route('superadmin.departments.users', [$department->id, 'tab' => 'users']) }}" class="sidebar-link btn-sidebar">👤{{ __('messages.admin.users') }}</a>
    <a href="{{ route('superadmin.departments.operations', [$department->id, 'tab' => 'groups']) }}" class="sidebar-link btn-sidebar">📊{{ __('messages.admin.operations') }}</a>
</div>


