<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Infraction Review</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, sans-serif; margin: 2rem; }
        .card { border: 1px solid #d4d4d8; border-radius: 12px; padding: 1rem; max-width: 980px; margin-bottom: 1rem; }
        .item { border-top: 1px solid #e4e4e7; padding: .75rem 0; }
        .grid { display:grid; gap:.75rem; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); }
        .ok { color: #166534; }
        button { border: 1px solid #52525b; border-radius: 10px; background: #18181b; color: #fff; padding: .45rem .75rem; }
    </style>
</head>
<body>
<div class="card">
    <h1>Infraction Review</h1>
    @if (session('status'))
        <p class="ok">{{ session('status') }}</p>
    @endif
</div>

<div class="card">
    <h2>Due now</h2>
    @forelse($dueNow as $e)
        <div class="item">
            <div><strong>{{ $e->kid?->display_name }}</strong> · {{ $e->definition?->label }} · review_on {{ $e->review_on }}</div>
            <form method="post" action="{{ route('admin.infractions.review.decide') }}" class="grid" style="margin-top:.5rem;">
                @csrf
                <input type="hidden" name="event_id" value="{{ $e->id }}">
                <label>
                    Action
                    <select name="action">
                        <option value="review_only">review_only</option>
                        <option value="unlock">unlock</option>
                        <option value="shorten">shorten</option>
                    </select>
                </label>
                <label>
                    Keep minutes
                    <input type="number" name="keep_minutes" min="0" max="10080" value="240">
                </label>
                <label>
                    Reset strike
                    <input type="checkbox" name="reset_strike" value="1">
                </label>
                <label>
                    Note
                    <input type="text" name="review_note" value="" maxlength="400">
                </label>
                <div style="align-self:end;">
                    <button type="submit">Mark reviewed</button>
                </div>
            </form>
        </div>
    @empty
        <p>None due.</p>
    @endforelse
</div>

<div class="card">
    <h2>Upcoming (7 days)</h2>
    @forelse($upcoming as $e)
        <div class="item">
            <div><strong>{{ $e->kid?->display_name }}</strong> · {{ $e->definition?->label }} · review_on {{ $e->review_on }}</div>
        </div>
    @empty
        <p>No upcoming reviews.</p>
    @endforelse
</div>
</body>
</html>
