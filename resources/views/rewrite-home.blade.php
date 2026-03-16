<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GB2 Rewrite</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, sans-serif; margin: 2rem; }
        .card { border: 1px solid #d4d4d8; border-radius: 12px; padding: 1rem; max-width: 720px; }
        .muted { color: #52525b; font-size: 0.95rem; }
        .ok { color: #166534; }
        a.btn, button.btn { display: inline-block; margin-top: 0.75rem; padding: 0.5rem 0.9rem; border: 1px solid #a1a1aa; border-radius: 10px; text-decoration: none; background: #fafafa; }
    </style>
</head>
<body>
<div class="card">
    <h1>GB2 Rewrite Home</h1>
    @if (session('status'))
        <p class="ok">{{ session('status') }}</p>
    @endif
    <p class="muted">Auth/Session module scaffold is active. This screen confirms kid session bootstrap in rewrite app.</p>
    <p>Current kid session id: <strong>{{ session('gb2_kid_id', 'none') }}</strong></p>

    <a class="btn" href="{{ route('kid.login') }}">Kid Login</a>
    <a class="btn" href="{{ route('rotation.today') }}">Rotation Today</a>
    <form method="post" action="{{ route('kid.logout') }}" style="display:inline-block">
        @csrf
        <button class="btn" type="submit">Logout</button>
    </form>
</div>
</body>
</html>
