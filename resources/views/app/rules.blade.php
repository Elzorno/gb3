@extends('layouts.kid')

@section('title', 'My Week - Grounding Buddy')

@section('header-title', 'My Week')

@section('content')
    {{-- My schedule first --}}
    <div class="card mb-4">
        <div class="week-header text-center mb-3">
            <span class="week-label">
                {{ $weekDays[0]->format('M j') }} – {{ $weekDays[4]->format('M j, Y') }}
            </span>
        </div>

        <div class="my-week-grid">
            @foreach($weekDays as $day)
                @php
                    $dayStr = $day->format('Y-m-d');
                    $assignment = $schedule[$dayStr][$currentKidId] ?? null;
                    $isToday = $dayStr === $today;
                    $status = $assignment?->status ?? 'none';
                @endphp
                <div class="my-day-card {{ $isToday ? 'is-today' : '' }} status-{{ $status }}">
                    <div class="my-day-header">
                        <span class="my-day-name">{{ $day->format('D') }}</span>
                        <span class="my-day-number">{{ $day->format('j') }}</span>
                        @if($isToday)
                            <span class="today-dot"></span>
                        @endif
                    </div>
                    <div class="my-day-body">
                        @if($assignment && $assignment->slot)
                            <div class="my-day-chore">{{ $assignment->slot->title }}</div>
                            <div class="my-day-status">
                                @if($status === 'approved')
                                    <span class="badge badge-success">Done!</span>
                                @elseif($status === 'pending')
                                    <span class="badge badge-warning">Waiting</span>
                                @elseif($status === 'rejected')
                                    <span class="badge badge-attention">Try again</span>
                                @else
                                    <span class="badge">To do</span>
                                @endif
                            </div>
                        @else
                            <div class="my-day-chore text-muted">No chore</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Recovery path when grounded --}}
    @if($isGrounded ?? false)
        <div class="card recovery-card mb-4">
            <h3 class="card-title">How to Get Back on Track</h3>
            <ol class="recovery-steps">
                <li>Complete your daily tasks on time</li>
                <li>Wait for your locks to be reviewed</li>
                <li>Show good behavior — each day counts</li>
            </ol>
        </div>
    @endif

    {{-- Compact family schedule (collapsed by default) --}}
    <details class="card mb-4">
        <summary class="family-schedule-toggle">
            <span>See family schedule</span>
        </summary>

        <div class="schedule-wrapper mt-3">
            <table class="schedule-grid">
                <thead>
                    <tr>
                        <th class="schedule-corner"></th>
                        @foreach($weekDays as $day)
                            <th class="schedule-day {{ $day->format('Y-m-d') === $today ? 'is-today' : '' }}">
                                <span class="day-name">{{ $day->format('D') }}</span>
                                <span class="day-number">{{ $day->format('j') }}</span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($kids as $kid)
                        <tr class="{{ $kid->id === $currentKidId ? 'is-current-kid' : '' }}">
                            <td class="schedule-kid">
                                <span class="kid-avatar">{{ strtoupper(substr($kid->display_name, 0, 1)) }}</span>
                                <span class="kid-name">{{ $kid->display_name }}</span>
                                @if($kid->id === $currentKidId)
                                    <span class="you-badge">You</span>
                                @endif
                            </td>
                            @foreach($weekDays as $day)
                                @php
                                    $dayStr = $day->format('Y-m-d');
                                    $assignment = $schedule[$dayStr][$kid->id] ?? null;
                                    $isToday = $dayStr === $today;
                                    $status = $assignment?->status ?? 'none';
                                @endphp
                                <td class="schedule-cell {{ $isToday ? 'is-today' : '' }} status-{{ $status }}">
                                    @if($assignment && $assignment->slot)
                                        <div class="chore-name">{{ $assignment->slot->title }}</div>
                                        <div class="chore-status">
                                            @if($status === 'approved')
                                                <span class="status-icon">✓</span>
                                            @elseif($status === 'pending')
                                                <span class="status-icon">⏳</span>
                                            @elseif($status === 'rejected')
                                                <span class="status-icon">↩</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="no-chore">-</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </details>

    {{-- Legend --}}
    <div class="card">
        <div class="schedule-legend">
            <div class="legend-item">
                <span class="legend-icon status-approved">✓</span>
                <span>Done</span>
            </div>
            <div class="legend-item">
                <span class="legend-icon status-pending">⏳</span>
                <span>Waiting</span>
            </div>
            <div class="legend-item">
                <span class="legend-icon status-rejected">↩</span>
                <span>Try again</span>
            </div>
        </div>
    </div>
@endsection

@push('head')
<style>
    .week-header {
        padding: 0.5rem;
    }

    .week-label {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .schedule-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin: 0 -1rem;
        padding: 0 1rem;
    }

    .schedule-grid {
        width: 100%;
        border-collapse: collapse;
        min-width: 500px;
    }

    .schedule-grid th,
    .schedule-grid td {
        border: 1px solid var(--gray-200);
        padding: 0.5rem;
        text-align: center;
        vertical-align: middle;
    }

    .schedule-corner {
        background: var(--gray-50);
        width: 100px;
    }

    .schedule-day {
        background: var(--gray-50);
        min-width: 70px;
    }

    .schedule-day.is-today {
        background: var(--secondary);
        color: white;
    }

    .day-name {
        display: block;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .day-number {
        display: block;
        font-size: 1.125rem;
        font-weight: 700;
    }

    .schedule-kid {
        background: var(--gray-50);
        text-align: left !important;
        white-space: nowrap;
    }

    .is-current-kid .schedule-kid {
        background: color-mix(in srgb, var(--secondary) 15%, white);
    }

    .kid-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        background: var(--secondary);
        color: white;
        border-radius: 50%;
        font-weight: 700;
        font-size: 0.75rem;
        margin-right: 0.5rem;
        vertical-align: middle;
    }

    .kid-name {
        font-weight: 500;
        font-size: 0.9375rem;
        vertical-align: middle;
    }

    .you-badge {
        display: inline-block;
        margin-left: 0.5rem;
        padding: 0.125rem 0.375rem;
        background: var(--secondary);
        color: white;
        border-radius: var(--border-radius);
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        vertical-align: middle;
    }

    .schedule-cell {
        min-height: 60px;
        background: white;
    }

    .schedule-cell.is-today {
        background: color-mix(in srgb, var(--secondary) 5%, white);
    }

    .is-current-kid .schedule-cell {
        background: color-mix(in srgb, var(--secondary) 8%, white);
    }

    .is-current-kid .schedule-cell.is-today {
        background: color-mix(in srgb, var(--secondary) 15%, white);
    }

    .chore-name {
        font-size: 0.8125rem;
        font-weight: 500;
        color: var(--text-primary);
        line-height: 1.2;
    }

    .chore-status {
        margin-top: 0.25rem;
    }

    .status-icon {
        font-size: 1rem;
    }

    .status-approved .status-icon,
    .status-completed .status-icon {
        color: var(--success);
    }

    .status-pending .status-icon {
        color: var(--warning);
    }

    .status-rejected .status-icon {
        color: var(--attention);
    }

    .no-chore {
        color: var(--gray-300);
        font-size: 1.25rem;
    }

    /* Legend */
    .schedule-legend {
        display: flex;
        justify-content: center;
        gap: 1.5rem;
        padding-top: 0.75rem;
        border-top: 1px solid var(--gray-200);
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        font-size: 0.8125rem;
        color: var(--text-muted);
    }

    .legend-icon {
        font-size: 1rem;
    }

    .legend-icon.status-approved {
        color: var(--success);
    }

    .legend-icon.status-pending {
        color: var(--warning);
    }

    .legend-icon.status-rejected {
        color: var(--attention);
    }

    /* Encouragement */
    .encouragement {
        text-align: center;
        padding: 1rem;
    }

    .encouragement-emoji {
        font-size: 2rem;
        display: block;
        margin-bottom: 0.5rem;
    }

    .encouragement p {
        color: var(--text-secondary);
    }

    /* My Week - self-focused day cards */
    .my-week-grid {
        display: flex;
        gap: 0.5rem;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scroll-snap-type: x mandatory;
        padding-bottom: 0.25rem; /* room for scrollbar if shown */
        margin: 0 -1rem;
        padding-left: 1rem;
        padding-right: 1rem;
    }
    .my-week-grid::-webkit-scrollbar {
        display: none;
    }
    .my-day-card {
        flex: 0 0 auto;
        width: calc((100% - 2rem) / 3); /* ~3 visible cards with peek */
        min-width: 110px;
        background: var(--gray-50);
        border-radius: var(--border-radius);
        padding: 0.75rem 0.5rem;
        text-align: center;
        border: 2px solid transparent;
        scroll-snap-align: start;
    }
    /* On wider screens, show all 5 */
    @media (min-width: 480px) {
        .my-week-grid {
            overflow-x: visible;
            margin: 0;
            padding-left: 0;
            padding-right: 0;
        }
        .my-day-card {
            flex: 1 1 0;
            width: auto;
            min-width: 0;
        }
    }
    .my-day-card.is-today {
        border-color: var(--secondary);
        background: color-mix(in srgb, var(--secondary) 10%, white);
    }
    .my-day-card.status-approved {
        background: color-mix(in srgb, var(--success) 10%, white);
    }
    .my-day-header {
        position: relative;
        margin-bottom: 0.35rem;
    }
    .my-day-name {
        display: block;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
    }
    .my-day-number {
        font-size: 1.25rem;
        font-weight: 700;
    }
    .today-dot {
        display: inline-block;
        width: 6px;
        height: 6px;
        background: var(--secondary);
        border-radius: 50%;
        vertical-align: middle;
        margin-left: 2px;
    }
    .my-day-chore {
        font-size: 0.85rem;
        font-weight: 500;
        line-height: 1.3;
        margin-bottom: 0.35rem;
        word-break: break-word;
        hyphens: auto;
    }
    .my-day-status .badge {
        font-size: 0.7rem;
        padding: 0.15rem 0.5rem;
    }

    /* Recovery card */
    .recovery-card {
        border-left: 4px solid var(--secondary);
    }
    .recovery-steps {
        padding-left: 1.25rem;
        margin: 0.5rem 0 0;
        color: var(--text-secondary);
    }
    .recovery-steps li {
        padding: 0.25rem 0;
    }

    /* Family schedule toggle */
    .family-schedule-toggle {
        cursor: pointer;
        font-weight: 600;
        color: var(--text-secondary);
        padding: 0.25rem 0;
    }
    .family-schedule-toggle:hover {
        color: var(--secondary);
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var today = document.querySelector('.my-day-card.is-today');
        if (today) {
            today.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'instant' });
        }
    });
</script>
@endpush
