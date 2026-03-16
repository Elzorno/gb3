<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rotation Today</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, sans-serif; margin: 2rem; }
        .card { border: 1px solid #d4d4d8; border-radius: 12px; padding: 1rem; max-width: 720px; }
        .muted { color: #52525b; }
        table { width: 100%; border-collapse: collapse; margin-top: 0.75rem; }
        th, td { border-bottom: 1px solid #e4e4e7; text-align: left; padding: 0.45rem 0.25rem; }
        a.btn { display: inline-block; margin-top: 0.85rem; padding: 0.45rem 0.8rem; border: 1px solid #a1a1aa; border-radius: 10px; text-decoration: none; }
    </style>
</head>
<body>
<div class="card">
    <h1>Rotation Today</h1>
    <p class="muted">Date: {{ $date }}</p>

    @if (!$isWeekday)
        <p>Weekend mode. No base rotation assignments.</p>
    @elseif ($assignments->isEmpty())
        <p>No assignments available. Define kids, slots, and a rotation rule first.</p>
    @else
        <table>
            <thead>
            <tr>
                <th>Kid</th>
                <th>Slot</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            @foreach($assignments as $a)
                <tr>
                    <td>{{ $a->kid?->display_name }}</td>
                    <td>{{ $a->slot?->title }}</td>
                    <td>{{ $a->status }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    <a class="btn" href="{{ route('rewrite.home') }}">Back Home</a>
</div>
</body>
</html>
