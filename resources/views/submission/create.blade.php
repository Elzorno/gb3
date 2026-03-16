<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Submit Proof</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, sans-serif; margin: 2rem; }
        .card { border: 1px solid #d4d4d8; border-radius: 12px; padding: 1rem; max-width: 720px; }
        .field { margin-top: 0.75rem; display:grid; gap:.35rem; }
        input { height: 2.25rem; border: 1px solid #a1a1aa; border-radius: 8px; padding: 0 .6rem; }
        button { margin-top: 1rem; height: 2.4rem; border: 1px solid #52525b; border-radius: 10px; background: #18181b; color: #fff; padding: 0 .9rem; }
        .ok { color:#166534; }
        .err { color:#991b1b; }
    </style>
</head>
<body>
<div class="card">
    <h1>Submit Base Chore Proof</h1>
    <p>Kid: <strong>{{ $kid?->display_name ?? 'not logged in' }}</strong></p>

    @if (session('status'))
        <p class="ok">{{ session('status') }}</p>
    @endif

    <form method="post" action="{{ route('submission.storeBase') }}">
        @csrf

        <div class="field">
            <label for="day">Day (YYYY-MM-DD)</label>
            <input id="day" name="day" value="{{ old('day', now()->format('Y-m-d')) }}" required>
            @error('day')<div class="err">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="slot_id">Slot ID</label>
            <input id="slot_id" name="slot_id" type="number" min="1" value="{{ old('slot_id') }}" required>
            @error('slot_id')<div class="err">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="proof_path">Proof Path</label>
            <input id="proof_path" name="proof_path" value="{{ old('proof_path', 'uploads/NO_PHOTO') }}" required>
            @error('proof_path')<div class="err">{{ $message }}</div>@enderror
        </div>

        <button type="submit">Submit</button>
    </form>
</div>
</body>
</html>
