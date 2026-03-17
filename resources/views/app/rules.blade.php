@extends('layouts.kid')

@section('title', 'Weekly Schedule - Grounding Buddy')

@section('header-title', 'Weekly Schedule')

@section('content')
    <div class="card">
        <p class="text-muted mb-4 text-center">
            See what everyone's doing this week. Your name is highlighted!
        </p>

        {{-- Week navigation info --}}
        <div class="week-header text-center mb-4">
            <span class="week-label">
                {{ $weekDays[0]->format('M j') }} - {{ $weekDays[4]->format('M j, Y') }}
            </span>
        </div>

        {{-- Schedule grid --}}
        <div class="schedule-wrapper">
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
                                            @if($status === 'approved' || $status === 'completed')
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

        {{-- Legend --}}
        <div class="schedule-legend mt-4">
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
                <span>Redo</span>
            </div>
        </div>
    </div>

    <div class="encouragement mt-4">
        <span class="encouragement-emoji">📋</span>
        <p class="mb-0">Everyone helps keep the family running smoothly!</p>
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
</style>
@endpush
