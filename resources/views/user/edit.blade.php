{{-- resources/views/users/edit.blade.php --}}
<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Edit User — Admin</title>

    <style>
        :root {
            --bg: #071427;
            --card: #0f2233;
            --muted: #9fb7dd;
            --text: #e7f4ff;
            --accent: #3b82f6;
            --yellow: #facc15;
            --danger: #ef4444;
            --input-bg: #071827;
            --input-border: rgba(255, 255, 255, 0.04);
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, Arial;
            padding: 18px;
        }

        .container {
            max-width: 920px;
            margin: 0 auto;
        }

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
        }

        .breadcrumbs {
            color: var(--muted);
            font-size: 0.95rem;
        }

        .card {
            background: var(--card);
            border-radius: 12px;
            padding: 18px;
            border: 1px solid rgba(255, 255, 255, 0.03);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.6);
            margin-bottom: 14px;
        }

        .card h3 {
            color: var(--yellow);
            margin-top: 0;
            margin-bottom: 10px;
        }

        .form-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .form-group {
            flex: 1 1 300px;
            min-width: 240px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        label {
            font-size: 0.95rem;
            color: var(--muted);
        }

        input[type=text],
        input[type=email],
        input[type=password],
        select {
            background: var(--input-bg);
            color: var(--text);
            border: 1px solid var(--input-border);
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 0.95rem;
            outline: none;
        }

        input:focus,
        select:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
            border-color: rgba(59, 130, 246, 0.9);
        }

        .help {
            color: var(--muted);
            font-size: 0.88rem;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-top: 8px;
        }

        .btn {
            padding: 8px 14px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.95rem;
        }

        .btn-primary {
            background: #16a34a;
            color: #fff;
        }

        .btn-secondary {
            background: var(--card);
            color: var(--muted);
            border: 1px solid rgba(255, 255, 255, 0.03);
        }

        .btn-danger {
            background: var(--danger);
            color: #fff;
        }

        .errors {
            background: rgba(239, 68, 68, 0.06);
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 12px;
            color: #ef4444;
        }

        .field-error {
            color: #ffb4b4;
            font-size: 0.9rem;
            margin-top: 4px;
        }

        .dropdown {
            position: relative;
            display: inline-block;
            width: 100%;
            max-width: 100%;
        }

        .dropdown-button {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid var(--input-border);
            background: var(--input-bg);
            cursor: pointer;
        }

        .dropdown-panel {
            position: absolute;
            left: 0;
            right: 0;
            margin-top: 8px;
            background: var(--card);
            border: 1px solid rgba(255, 255, 255, 0.04);
            border-radius: 8px;
            padding: 10px;
            z-index: 50;
            max-height: 300px;
            overflow: auto;
            box-shadow: 0 6px 20px rgba(2, 6, 23, 0.6);
        }

        .checkbox-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        @media (max-width:760px) {
            .form-row {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="topbar">
            <div>
                <div class="title">Foydalanuvchini tahrirlash</div>
                <div class="breadcrumbs">Admin panel / Foydalanuvchilar / Tahrirlash</div>
            </div>
            <div style="display:flex; gap:8px; align-items:center;">
                <a href="{{ url()->previous() }}" class="btn btn-secondary" style="text-decoration:none;">← Back</a>
            </div>
        </div>

        <div class="card">
            <h3>Foydalanuvchi ma'lumotlarini yangilash</h3>

            @if ($errors->any())
                <div class="errors">
                    <strong>Xatoliklar:</strong>
                    <ul style="margin:8px 0 0 16px; padding:0;">
                        @foreach ($errors->all() as $error)
                            <li style="margin-bottom:6px;">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('users.update', $user->id) }}">
                @csrf
                @method('PUT')

                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Ism (name) <span style="color:var(--danger)">*</span></label>
                        <input id="name" name="name" type="text" required
                            value="{{ old('name', $user->name) }}">
                        @error('name')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="telegram_id">Telegram ID <span style="color:var(--danger)">*</span></label>
                        <input id="telegram_id" name="telegram_id" type="text" required
                            value="{{ old('telegram_id', $user->telegram_id) }}">
                        @error('telegram_id')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="department_id">Bo'lim <span style="color:var(--danger)">*</span></label>
                        <select id="department_id" name="department_id" required>
                            <option value="">— Tanlang —</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}"
                                    {{ old('department_id', $user->department_id) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="role_id">Rol <span style="color:var(--danger)">*</span></label>
                        <select id="role_id" name="role_id" required>
                            <option value="">— Tanlang —</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" data-role-name="{{ $role->name }}"
                                    {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                        @error('role_id')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Conditional email/password -->
                <div id="credential-block" style="display:none; margin-top:12px;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input id="email" name="email" type="email"
                                value="{{ old('email', $user->email) }}">
                            @error('email')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="password">Parol (password)</label>
                            <div style="display:flex; gap:8px; align-items:center;">
                                <input id="password" name="password" type="password" style="flex:1;">
                                <button type="button" id="togglePassword" class="btn btn-secondary"
                                    style="padding:6px 10px;">Show</button>
                            </div>
                            <div class="help">Agar o‘zgartirmasangiz bo‘sh qoldiring</div>
                            @error('password')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Extra minutes -->
                <div class="form-row" style="margin-top:12px;">
                    <div class="form-group">
                        <label for="has_extra_minutes">Qo'shimcha daqiqalar</label>
                        <input type="checkbox" id="has_extra_minutes" name="has_extra_minutes" value="1"
                            {{ old('has_extra_minutes', $user->minuteAccess?->is_active ?? 0) ? 'checked' : '' }}>


                    </div>

                    <!-- Catalog multi-select -->
                    <div class="form-group">
                        <label>Catalog list</label>
                        <div class="dropdown" id="catalogDropdown">
                            <button type="button" class="dropdown-button" id="catalogBtn">
                                <span id="catalogBtnText">Select catalogs</span>
                                <span id="catalogCount" class="help">0</span>
                            </button>
                            <div class="dropdown-panel" id="catalogPanel" style="display:none;">
                                <input type="text" id="catalogSearch" placeholder="Search..."
                                    style="width:97%;padding:4px 6px;font-size:12px;margin-bottom:6px;border-radius:6px;border:1px solid rgba(255,255,255,.15); background:var(--input-bg); color:var(--text)">
                                <div class="checkbox-list">
                                    @foreach ($catalogs as $cat)
                                        <label style="display:flex; gap:4px; font-size:13px; line-height:1.2;">
                                            <input type="checkbox" class="catalog-checkbox" name="catalog_ids[]"
                                                value="{{ $cat->id }}"
                                                {{ in_array($cat->id, old('catalog_ids', $user->catalogs->pluck('id')->toArray())) ? 'checked' : '' }}>
                                            <span>{{ $cat->title }} <small
                                                    style="color:var(--muted);">({{ $cat->owner }})</small></span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions" style="margin-top:14px;">
                    <button type="submit" class="btn btn-primary">Update user</button>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary"
                        style="text-decoration:none;">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function() {
            const pw = document.getElementById('password');
            const toggleBtn = document.getElementById('togglePassword');
            const roleSelect = document.getElementById('role_id');
            const credBlock = document.getElementById('credential-block');
            const emailInput = document.getElementById('email');

            if (pw && toggleBtn) {
                toggleBtn.addEventListener('click', () => {
                    if (pw.type === 'password') {
                        pw.type = 'text';
                        toggleBtn.textContent = 'Hide';
                    } else {
                        pw.type = 'password';
                        toggleBtn.textContent = 'Show';
                    }
                });
            }

            function applyVisibility() {
                const opt = roleSelect.options[roleSelect.selectedIndex];
                const roleName = opt?.dataset?.roleName?.toLowerCase() || '';
                if (roleName.includes('admin') || roleName.includes('super')) {
                    credBlock.style.display = '';
                    emailInput?.setAttribute('required', 'required');
                    pw?.setAttribute('required', 'required');
                } else {
                    credBlock.style.display = 'none';
                    emailInput?.removeAttribute('required');
                    pw?.removeAttribute('required');
                }
            }

            roleSelect?.addEventListener('change', applyVisibility);
            document.addEventListener('DOMContentLoaded', applyVisibility);

            // Catalog dropdown
            const catalogBtn = document.getElementById('catalogBtn');
            const catalogPanel = document.getElementById('catalogPanel');
            const catalogDropdown = document.getElementById('catalogDropdown');
            const catalogCheckboxes = catalogPanel.querySelectorAll('.catalog-checkbox');
            const catalogCount = document.getElementById('catalogCount');
            const catalogBtnText = document.getElementById('catalogBtnText');
            const catalogSearch = document.getElementById('catalogSearch');

            function openCatalog() {
                catalogPanel.style.display = 'block';
                catalogBtn.setAttribute('aria-expanded', 'true');
            }

            function closeCatalog() {
                catalogPanel.style.display = 'none';
                catalogBtn.setAttribute('aria-expanded', 'false');
            }

            function toggleCatalog() {
                catalogPanel.style.display === 'block' ? closeCatalog() : openCatalog();
            }

            catalogBtn.addEventListener('click', e => {
                e.preventDefault();
                toggleCatalog();
            });
            document.addEventListener('click', e => {
                if (!catalogDropdown.contains(e.target)) closeCatalog();
            });
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') closeCatalog();
            });

            function updateCatalogCount() {
                let count = 0,
                    titles = [];
                catalogCheckboxes.forEach(cb => {
                    if (cb.checked) {
                        count++;
                        titles.push(cb.closest('label').innerText.trim());
                    }
                });
                catalogCount.textContent = count;
                if (count === 0) {
                    catalogBtnText.textContent = 'Select catalogs';
                } else if (count <= 2) {
                    catalogBtnText.textContent = titles.join(', ');
                } else {
                    catalogBtnText.textContent = count + ' selected';
                }
            }
            catalogCheckboxes.forEach(cb => cb.addEventListener('change', updateCatalogCount));
            updateCatalogCount();
            catalogSearch?.addEventListener('input', function() {
                const q = this.value.toLowerCase();
                catalogCheckboxes.forEach(cb => {
                    const label = cb.closest('label');
                    label.style.display = label.innerText.toLowerCase().includes(q) ? 'flex' : 'none';
                });
            });

        })();
    </script>
</body>

</html>
