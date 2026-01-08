@extends('layouts.app')

@section('title','Dashboard')
@section('page-title','Asosiy Dashboard')

@section('content')

{{-- ===== STATISTICS ===== --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-center p-3">
            <h2 class="text-primary mb-1">1,248</h2>
            <small class="text-muted">Jami foydalanuvchilar</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-center p-3">
            <h2 class="text-success mb-1">8,420</h2>
            <small class="text-muted">Yuborilgan xabarlar</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-center p-3">
            <h2 class="text-warning mb-1">312</h2>
            <small class="text-muted">Faol operatsiyalar</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-center p-3">
            <h2 class="text-danger mb-1">7</h2>
            <small class="text-muted">Xatoliklar</small>
        </div>
    </div>
</div>

{{-- ===== MAIN GRID ===== --}}
<div class="row g-4">

    {{-- LEFT COLUMN --}}
    <div class="col-lg-8">

        {{-- LAST OPERATIONS --}}
        <div class="card p-3 mb-4">
            <h5 class="mb-3">📊 So‘nggi operatsiyalar</h5>

            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Foydalanuvchi</th>
                            <th>Amal</th>
                            <th>Status</th>
                            <th>Sana</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#1203</td>
                            <td>@john_doe</td>
                            <td>Xabar yuborildi</td>
                            <td><span class="badge bg-success">Success</span></td>
                            <td>2026-01-07</td>
                        </tr>
                        <tr>
                            <td>#1202</td>
                            <td>@alex_admin</td>
                            <td>Login</td>
                            <td><span class="badge bg-primary">Info</span></td>
                            <td>2026-01-07</td>
                        </tr>
                        <tr>
                            <td>#1201</td>
                            <td>@bot_user</td>
                            <td>Xatolik</td>
                            <td><span class="badge bg-danger">Error</span></td>
                            <td>2026-01-06</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- SYSTEM ACTIVITY --}}
        <div class="card p-3">
            <h5 class="mb-3">⚡ Tizim faolligi</h5>

            <div class="list-group list-group-flush">
                <div class="list-group-item bg-transparent text-light border-0 px-0">
                    <strong>@admin</strong> yangi foydalanuvchi qo‘shdi
                    <div class="text-muted small">2 daqiqa oldin</div>
                </div>

                <div class="list-group-item bg-transparent text-light border-0 px-0">
                    <strong>@moderator</strong> operatsiyani bekor qildi
                    <div class="text-muted small">10 daqiqa oldin</div>
                </div>

                <div class="list-group-item bg-transparent text-light border-0 px-0">
                    <strong>System</strong> cron job ishga tushdi
                    <div class="text-muted small">1 soat oldin</div>
                </div>
            </div>
        </div>

    </div>

    {{-- RIGHT COLUMN --}}
    <div class="col-lg-4">

        {{-- LAST USERS --}}
        <div class="card p-3 mb-4">
            <h5 class="mb-3">👥 Oxirgi foydalanuvchilar</h5>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <strong>@john_doe</strong>
                    <div class="text-muted small">Telegram</div>
                </div>
                <span class="badge bg-success">Active</span>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <strong>@alex_admin</strong>
                    <div class="text-muted small">Web</div>
                </div>
                <span class="badge bg-primary">Admin</span>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>@bot_user</strong>
                    <div class="text-muted small">API</div>
                </div>
                <span class="badge bg-warning text-dark">Pending</span>
            </div>
        </div>

        {{-- QUICK ACTIONS --}}
        <div class="card p-3">
            <h5 class="mb-3">⚙️ Tezkor amallar</h5>

            <div class="d-grid gap-2">
                <a href="" class="btn btn-outline-primary">
                    👤 Foydalanuvchilar
                </a>

                <a href="" class="btn btn-outline-warning">
                    📊 Operatsiyalar
                </a>

                <button class="btn btn-outline-danger">
                    🚨 Tizimni tekshirish
                </button>
            </div>
        </div>

    </div>
</div>

@endsection
