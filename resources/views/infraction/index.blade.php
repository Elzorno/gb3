<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Infractions</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, sans-serif; margin: 2rem; }
        .card { border: 1px solid #d4d4d8; border-radius: 12px; padding: 1rem; max-width: 960px; }
        .grid { display:grid; gap:.75rem; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }
        .item { border-top: 1px solid #e4e4e7; padding: .75rem 0; }
        .ok { color: #166534; }
        button { border: 1px solid #52525b; border-radius: 10px; background: #18181b; color: #fff; padding: .45rem .75rem; }
    </style>
</head>
<body>
<div class="card">
    <h1>Infraction Admin</h1>
    @if (session('status'))
        <p class="ok">{{ session('status') }}</p>
    @endif

    <form method="post" action="{{ route('infraction.apply') }}" class="grid" style="margin-bottom:.75rem;">
        @csrf
        <label>
            Kid
            <select name="kid_id" required>
                <option value="">select</option>
                @foreach($kids as $kid)
                    <option value="{{ $kid->id }}">{{ $kid->display_name }}</option>
                @endforeach
            </select>
        </label>
        <label>
            Definition
            <select name="infraction_def_id" required>
                <option value="">select</option>
                @foreach($defs as $def)
                    <option value="{{ $def->id }}">{{ $def->label }} ({{ $def->code }})</option>
                @endforeach
            </select>
        </label>
        <label>
            Note
            <input type="text" name="note" maxlength="300" value="">
        </label>
        <div style="align-self:end;">
            <button type="submit">Apply</button>
        </div>
    </form>

    <p><a href="{{ route('infraction.review') }}">Open review queue</a></p>

    <h2>Recent events</h2>
    @forelse($events as $e)
        <div class="item">
            <div><strong>{{ $e->kid?->display_name }}</strong> · {{ $e->definition?->label }} · {{ $e->ts }}</div>
            <div>strike {{ $e->strike_before }} -> {{ $e->strike_after }} · days {{ $e->days_applied }} · mode {{ $e->mode }}</div>
        </div>
    @empty
        <p>No infractions yet.</p>
    @endforelse
</div>
</body>
</html>
