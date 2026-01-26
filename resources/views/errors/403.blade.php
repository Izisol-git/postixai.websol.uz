@extends('layouts.app')

@section('title', __('messages.errors.403_title'))

@section('page-title')
    {{ __('messages.errors.forbidden') }}
@endsection

@section('page-subtitle')
    {{ __('messages.errors.forbidden_sub') }}
@endsection

@section('content')

<div class="d-flex justify-content-center align-items-center" style="min-height:70vh">

    <div class="card p-4 text-center" style="max-width:600px;width:100%">

        <h1 class="mb-2">403</h1>

        <h5 class="mb-3">
            {{ __('messages.errors.forbidden') }}
        </h5>

        <p class="text-muted mb-4">
            {{ __('messages.errors.forbidden_sub') }}
        </p>

        <div class="d-flex justify-content-center gap-2">

            <a href="{{ url()->previous() }}" class="btn btn-theme btn-sm">
                ← {{ __('messages.errors.back') }}
            </a>

            <a href="{{ route('departments.dashboard', $department ?? 1) }}" class="btn btn-primary btn-sm">
                {{ __('messages.errors.home') }}
            </a>

        </div>

    </div>

</div>

@endsection
