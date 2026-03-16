<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bonuses</title>
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
    <h1>Bonuses</h1>
    <p>Week start: {{ $week }}</p>

    @if (session('status'))
        <p class="ok">{{ session('status') }}</p>
    @endif

    @forelse($instances as $inst)
        <div class="item">
            <div><strong>{{ $inst->definition?->title ?? ('Bonus#'.$inst->bonus_def_id) }}</strong> · status={{ $inst->status }}</div>

            @if($inst->status === 'available' && $kidId > 0)
                <form method="post" action="{{ route('bonus.claim') }}" style="margin-top:.4rem;">
                    @csrf
                    <input type="hidden" name="instance_id" value="{{ $inst->id }}">
                    <button type="submit">Claim</button>
                </form>
            @endif

            @if(in_array($inst->status, ['claimed','rejected'], true) && (int)$inst->claimed_by_kid_id === (int)$kidId)
                <form method="post" action="{{ route('bonus.submit') }}" style="margin-top:.4rem; display:flex; gap:.5rem; align-items:center;">
                    @csrf
                    <input type="hidden" name="instance_id" value="{{ $inst->id }}">
                    <input type="text" name="proof_path" value="uploads/NO_PHOTO">
                    <button type="submit">Submit Proof</button>
                </form>
            @endif
        </div>
    @empty
        <p>No bonus definitions available.</p>
    @endforelse
</div>
</body>
</html>
