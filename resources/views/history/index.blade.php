<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>History</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, sans-serif; margin: 2rem; }
        .card { border: 1px solid #d4d4d8; border-radius: 12px; padding: 1rem; max-width: 920px; }
        .item { border-top: 1px solid #e4e4e7; padding: .8rem 0; }
        button { border: 1px solid #52525b; border-radius: 10px; background: #18181b; color: #fff; padding: .35rem .7rem; }
    </style>
</head>
<body>
<div class="card">
    <h1>Your History</h1>

    <form method="get" action="{{ route('history.index') }}" style="display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:.75rem;">
        <select name="status">
            <option value="" {{ $status==='' ? 'selected' : '' }}>all status</option>
            <option value="pending" {{ $status==='pending' ? 'selected' : '' }}>pending</option>
            <option value="approved" {{ $status==='approved' ? 'selected' : '' }}>approved</option>
            <option value="rejected" {{ $status==='rejected' ? 'selected' : '' }}>rejected</option>
        </select>
        <select name="kind">
            <option value="" {{ $kind==='' ? 'selected' : '' }}>all kinds</option>
            <option value="base" {{ $kind==='base' ? 'selected' : '' }}>base</option>
            <option value="bonus" {{ $kind==='bonus' ? 'selected' : '' }}>bonus</option>
        </select>
        <select name="per_page">
            <option value="10" {{ $perPage===10 ? 'selected' : '' }}>10</option>
            <option value="20" {{ $perPage===20 ? 'selected' : '' }}>20</option>
            <option value="50" {{ $perPage===50 ? 'selected' : '' }}>50</option>
        </select>
        <button type="submit">Apply</button>
    </form>

    @forelse($rows as $r)
        <div class="item">
            <div><strong>{{ $r->kind }}</strong> · {{ $r->status }} · {{ $r->submitted_at }}</div>
            <div>slot_id={{ $r->slot_id }} · proof={{ $r->proof_path }}</div>
        </div>
    @empty
        <p>No submissions found.</p>
    @endforelse

    <div style="margin-top:.85rem;">
        {{ $rows->links() }}
    </div>
</div>
</body>
</html>
