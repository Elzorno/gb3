<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kid Login</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, sans-serif; margin: 2rem; }
        .card { border: 1px solid #d4d4d8; border-radius: 12px; padding: 1rem; max-width: 420px; }
        .field { display: grid; gap: 0.35rem; margin-top: 0.75rem; }
        input { height: 2.25rem; border: 1px solid #a1a1aa; border-radius: 8px; padding: 0 0.6rem; }
        button { margin-top: 1rem; height: 2.4rem; border: 1px solid #52525b; border-radius: 10px; background: #18181b; color: #fff; padding: 0 0.9rem; }
        .err { color: #991b1b; font-size: 0.9rem; margin-top: 0.35rem; }
        .ok { color: #166534; font-size: 0.92rem; }
        .note { margin-top: 0.75rem; color: #3f3f46; font-size: 0.92rem; }
    </style>
</head>
<body>
<div class="card">
    <h1>Kid Login</h1>

    @if (session('status'))
        <p class="ok">{{ session('status') }}</p>
    @endif

    <form method="post" action="{{ route('kid.login.submit') }}">
        @csrf

        <div class="field">
            <label for="kid_id">Kid ID</label>
            <input id="kid_id" name="kid_id" type="number" min="1" required value="{{ old('kid_id', $kidId) }}">
            @error('kid_id')<div class="err">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="pin">PIN (6 digits)</label>
            <input id="pin" name="pin" type="password" inputmode="numeric" pattern="[0-9]*" maxlength="6" required>
            @error('pin')<div class="err">{{ $message }}</div>@enderror
        </div>

        <button type="submit">Log In</button>
    </form>

    <div class="note">Login validates the entered PIN against this kid account in the rewrite database.</div>
</div>
</body>
</html>
