<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Postix AI')</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">


<style>
:root {
--bg:#071427;--card:#0f2233;--muted:#9fb7dd;--text:#e7f4ff;--accent:#3b82f6;
}
body{background:var(--bg);color:var(--text);margin:0}
.layout{display:flex;min-height:100vh}
.sidebar{width:240px;background:#081a2f;padding:20px}
.sidebar a{display:block;color:var(--muted);text-decoration:none;padding:10px;border-radius:8px;margin-bottom:6px}
.sidebar a.active,.sidebar a:hover{background:var(--accent);color:#fff}
.content{flex:1;padding:20px}
.topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
</style>
</head>
<body>
<div class="layout">
<aside class="sidebar">
<h5 class="mb-3">Postix AI</h5>
<a href="{{ route('departments.index') }}">🏠 Menyu asosiy</a>
<a href="">👤 Foydalanuvchilar</a>
<a href="">📊 Operatsiyalar</a>
</aside>


<main class="content">
<div class="topbar">
<h4>@yield('page-title')</h4>
<div>
<button class="btn btn-sm btn-primary">+ Add</button>
<button class="btn btn-sm btn-danger">Logout</button>
</div>
</div>


@yield('content')
</main>
</div>
</body>
</html>