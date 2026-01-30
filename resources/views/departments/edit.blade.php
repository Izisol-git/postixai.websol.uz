@extends('admin.layouts.app')

@section('title', __('messages.departments.edit_title', ['default' => 'Department tahrirlash']))
@section('page-title', __('messages.departments.edit'))

@section('show-back', true)
@section('show-sidebar', true)

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-7 col-lg-8 col-md-10">

        <div class="card p-4 shadow-sm">

            <h5 class="mb-3 text-warning fw-bold">
                {{ __('messages.departments.edit') ?? 'Department tahrirlash' }}
            </h5>

            {{-- Errors --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                action="{{ route('departments.update', $department->id) }}"
                method="POST"
                class="mt-3"
            >
                @csrf
                @method('PUT')

                {{-- Name --}}
                <div class="mb-3">

                    <label class="form-label">
                        {{ __('messages.departments.name') ?? 'Nomi' }}
                    </label>

                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        value="{{ old('name', $department->name) }}"
                        placeholder="{{ __('messages.departments.placeholder') ?? 'Department nomi' }}"
                        required
                    >

                    <div class="muted-small mt-1">
                        {{ __('messages.departments.hint') ?? 'Masalan: Marketing, Sales, Support' }}
                    </div>

                </div>

                {{-- Buttons --}}
                <div class="d-flex justify-content-end gap-2 mt-4">

                    <a href="{{ route('departments.show', $department->id) }}"
                       class="btn btn-outline-secondary btn-sm">

                        {{ __('messages.common.cancel') ?? 'Bekor qilish' }}
                    </a>

                    <button type="submit"
                            class="btn btn-primary btn-sm px-4 fw-bold">

                        {{ __('messages.common.save') ?? 'Saqlash' }}
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>

@endsection
