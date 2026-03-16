<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Review Queue</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, sans-serif; margin: 2rem; }
        .card { border: 1px solid #d4d4d8; border-radius: 12px; padding: 1rem; max-width: 920px; }
        .item { border-top: 1px solid #e4e4e7; padding: .8rem 0; }
        button { border: 1px solid #52525b; border-radius: 10px; background: #18181b; color: #fff; padding: .35rem .7rem; }
        input { height: 2rem; border: 1px solid #a1a1aa; border-radius: 8px; padding: 0 .5rem; }
        .ok { color:#166534; }
    </style>
</head>
<body>
<div class="card">
    <h1>Pending Review</h1>

    @if (session('status'))
        <p class="ok">{{ session('status') }}</p>
    @endif

    @forelse($pending as $p)
        <div class="item">
            <div><strong>{{ $p->kid?->display_name ?? ('Kid#'.$p->kid_id) }}</strong> · {{ $p->kind }} · {{ $p->submitted_at }}</div>
            <div>slot_id={{ $p->slot_id }} · proof={{ $p->proof_path }}</div>
            <form method="post" action="{{ route('review.decide') }}" style="margin-top:.45rem; display:flex; gap:.5rem; align-items:center; flex-wrap:wrap;">
                @csrf
                <input type="hidden" name="submission_id" value="{{ $p->id }}">
                <input type="text" name="note" placeholder="Optional note">
                <button type="submit" name="decision" value="approved">Approve</button>
                <button type="submit" name="decision" value="rejected">Reject</button>
            </form>
        </div>
    @empty
        <p>No pending submissions.</p>
    @endforelse
</div>
</body>
</html>
