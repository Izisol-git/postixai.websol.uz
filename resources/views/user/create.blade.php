{{-- resources/views/users/create.blade.php --}}
<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>POSTIX AI — Yangi foydalanuvchi</title>

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

        html,
        body {
            height: 100%
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, Arial;
            padding: 18px;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 920px;
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

        /* Form */
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

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        select,
        button {
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
            background: transparent;
            color: var(--text);
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

        .btn-toggle {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.03);
            cursor: pointer;
            background: var(--input-bg)
        }

        .btn-danger {
            background: var(--danger);
            color: #fff;
        }

        /* checkbox-list */
        .checkbox-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 8px 6px;
            max-height: 260px;
            overflow: auto;
        }

        /* dropdown */
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
            box-shadow: 0 6px 20px rgba(2, 6, 23, 0.6);
        }

        .note {
            color: var(--muted);
            font-size: 0.9rem;
            margin-top: 6px;
        }

        .field-error {
            color: #ffb4b4;
            font-size: 0.9rem;
            margin-top: 4px;
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
                <div class="title">Foydalanuvchi yaratish</div>
                <div class="breadcrumbs">Admin panel / Foydalanuvchilar / Yangi</div>
            </div>

            <div style="display:flex; gap:8px; align-items:center;">
                <a href="{{ url()->previous() }}" class="btn btn-secondary" style="text-decoration:none;">
                    ← Back
                </a>

                <form method="POST" action="{{ route('logout') }}" style="display:inline">
                    @csrf
                    <button type="submit" class="btn btn-secondary">Logout</button>
                </form>
            </div>
        </div>

        <div class="card">
            <h3>Yangi foydalanuvchi qo'shish</h3>

            {{-- Errors --}}
            @if ($errors->any())
                <div class="errors"
                    style="background: rgba(239,68,68,0.06); padding:10px;border-radius:8px; margin-bottom:12px;">
                    <strong>Xatoliklar:</strong>
                    <ul style="margin:8px 0 0 16px; padding:0;">
                        @foreach ($errors->all() as $error)
                            <li style="margin-bottom:6px;">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('users.store') }}">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label for="name">1) Name</label>
                        <input id="name" name="name" type="text" required value="{{ old('name') }}"
                            autocomplete="name" />
                        @error('name')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="telegram_id">2) Telegram ID</label>
                        <input id="telegram_id" name="telegram_id" type="text" required value="7164651" />
                        @error('telegram_id')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="department_id">3) Department</label>
                        <select id="department_id" name="department_id" required>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}"
                                    {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="role_id">4) Role</label>
                        <select id="role_id" name="role_id" required>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" data-role-name="{{ $role->name }}"
                                    {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                        @error('role_id')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- admin+ conditional -->
                <div id="credential-block" style="display:none; margin-top:12px;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">5) Email</label>
                            <input id="email" name="email" type="email" value="" />
                            @error('email')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">6) Password</label>
                            <div style="display:flex; gap:8px; align-items:center;">
                                <input id="password" name="password" type="password" style="flex:1;" />
                                <button type="button" id="togglePassword" class="btn btn-secondary"
                                    style="padding:6px 10px;">Show</button>
                            </div>
                            @error('password')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row" style="margin-top:8px;">
                        <div class="form-group">
                            <label for="max_users">7) User create limit</label>
                            <input id="max_users" name="max_users" type="number" min="0"
                                value="{{ old('max_users', 10) }}" />
                            @error('max_users')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>


                    </div>
                </div>

                <!-- Minute packages (if any) -->


                <!-- 8) Qo'shimcha daqiqalar (HAMMA uchun) -->
                <div class="form-row" style="margin-top:12px;">
                    <div class="form-group">
                        <label for="has_extra_minutes">8) Qo'shimcha daqiqalar qo'shish</label>
                        <div style="display:flex;gap:12px;align-items:center;">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <input type="checkbox" id="has_extra_minutes" name="has_extra_minutes" value="1"
                                    {{ old('has_extra_minutes') ? 'checked' : '' }}>

                            </div>


                        </div>
                    </div>

                    <!-- Contact catalog multi-select as dropdown (catalogs) -->
                    <div class="form-group">
                        <label>9) Catalog list</label>

                        <div class="dropdown" id="catalogDropdown">
                            <button type="button" class="dropdown-button" id="catalogBtn">
                                <span id="catalogBtnText">Select catalogs</span>
                                <span id="catalogCount" class="help">0</span>
                            </button>

                            <div class="dropdown-panel" id="catalogPanel" style="display:none;">
                                <input type="text" id="catalogSearch" placeholder="Search..."
                                    style="width:97%;padding:4px 6px;font-size:12px;margin-bottom:6px;
                border-radius:6px;border:1px solid rgba(255,255,255,.15);
                background:var(--input-bg);color:var(--text)">

                                <div class="checkbox-list" style="max-height:220px;overflow:auto;">
                                    @foreach ($catalogs as $cat)
                                        <label style="display:flex;gap:4px;font-size:13px;line-height:1.2;">
                                            <input type="checkbox" class="catalog-checkbox" name="catalog_ids[]"
                                                value="{{ $cat->id }}"
                                                {{ in_array($cat->id, old('catalog_ids', [])) ? 'checked' : '' }}>
                                            <span>
                                                {{ $cat->title }}
                                                <small style="font-size:11px;color:var(--muted);">
                                                    ({{ $cat->owner }})
                                                </small>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>


                </div>

                <div class="form-actions" style="margin-top:14px;">
                    <button type="submit" class="btn btn-primary">Create user</button>
                    <a href="{{ route('departments.index') }}" class="btn btn-secondary"
                        style="text-decoration:none;">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function() {
            // Password toggle
            const pw = document.getElementById('password');
            const toggleBtn = document.getElementById('togglePassword');
            if (pw && toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    if (pw.type === 'password') {
                        pw.type = 'text';
                        toggleBtn.textContent = 'Hide';
                    } else {
                        pw.type = 'password';
                        toggleBtn.textContent = 'Show';
                    }
                });
            }

            // Role visibility (admin+ means contains admin or super)
            const roleSelect = document.getElementById('role_id');
            const credBlock = document.getElementById('credential-block');

            function getSelectedRoleName() {
                const opt = roleSelect?.options[roleSelect.selectedIndex];
                return opt?.dataset?.roleName?.toString().toLowerCase() || '';
            }

            function isAdminPlus(roleName) {
                if (!roleName) return false;
                return roleName.includes('admin') || roleName.includes('super');
            }

            function applyRoleVisibility() {
                const roleName = getSelectedRoleName();
                if (isAdminPlus(roleName)) {
                    credBlock.style.display = '';
                    document.getElementById('email')?.setAttribute('required', 'required');
                    document.getElementById('password')?.setAttribute('required', 'required');
                } else {
                    credBlock.style.display = 'none';
                    document.getElementById('email')?.removeAttribute('required');
                    document.getElementById('password')?.removeAttribute('required');
                }
            }

            roleSelect && roleSelect.addEventListener('change', applyRoleVisibility);
            document.addEventListener('DOMContentLoaded', applyRoleVisibility);
            if (document.readyState === 'interactive' || document.readyState === 'complete') applyRoleVisibility();

            // Extra minutes toggle (visible for everyone)





            (function() {
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
                    let count = 0;
                    let titles = [];

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

                catalogCheckboxes.forEach(cb => {
                    cb.addEventListener('change', updateCatalogCount);
                });

                updateCatalogCount();

                // 🔍 SEARCH
                catalogSearch.addEventListener('input', function() {
                    const q = this.value.toLowerCase();

                    catalogCheckboxes.forEach(cb => {
                        const label = cb.closest('label');
                        const text = label.innerText.toLowerCase();
                        label.style.display = text.includes(q) ? 'flex' : 'none';
                    });
                });
            })();

            catalogBtn && catalogBtn.addEventListener('click', function(e) {
                e.preventDefault();
                toggleCatalog();
            });

            // close on outside click
            document.addEventListener('click', function(e) {
                if (!catalogDropdown.contains(e.target)) {
                    closeCatalog();
                }
            });

            // update count
            function updateCatalogCount() {
                let count = 0;
                const titles = [];
                for (let i = 0; i < catalogCheckboxes.length; i++) {
                    if (catalogCheckboxes[i].checked) {
                        count++;
                        // take sibling text content
                        const label = catalogCheckboxes[i].closest('label');
                        if (label) {
                            const txt = label.innerText.trim();
                            titles.push(txt);
                        }
                    }
                }
                catalogCount.textContent = count;
                if (count === 0) {
                    catalogBtnText.textContent = 'Select catalogs';
                } else if (count <= 2) {
                    catalogBtnText.textContent = titles.join(', ');
                } else {
                    catalogBtnText.textContent = count + ' selected';
                }
            }

            // attach change listeners
            for (let i = 0; i < catalogCheckboxes.length; i++) {
                catalogCheckboxes[i].addEventListener('change', updateCatalogCount);
            }

            // init
            document.addEventListener('DOMContentLoaded', updateCatalogCount);
            if (document.readyState === 'interactive' || document.readyState === 'complete') updateCatalogCount();

            // accessibility: ESC closes dropdown
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeCatalog();
            });
        })();
    </script>
</body>

</html>
