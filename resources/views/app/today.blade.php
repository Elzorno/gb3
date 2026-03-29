@extends('layouts.kid')

@section('title', 'Today - Grounding Buddy')

@section('header-title')
    Hi, {{ $kid?->display_name ?? session('kid_name', 'there') }}!
@endsection

@section('content')
    @php
        $total = $assignments->count();
        $completed = $assignments->where('status', 'approved')->count();
        $pending = $assignments->where('status', 'pending')->count();
        $actionableAssignments = $assignments->filter(fn ($assignment) => !in_array($assignment->status, ['approved', 'pending'], true))->values();
        $nextAssignment = $actionableAssignments->first();
        $percent = $total > 0 ? (int) round(($completed / $total) * 100) : 0;
        $progressLabel = $total > 0 ? "{$completed} of {$total} complete" : 'Nothing to complete today';

        $pausedPrivileges = $activeConsequence['pausedPrivileges'] ?? [];
        if ($isGrounded && empty($pausedPrivileges) && $kid?->privileges) {
            foreach (['phone' => 'Phone', 'games' => 'Games', 'other' => 'Other screen time'] as $type => $label) {
                if ($kid->privileges->{$type . '_locked'}) {
                    $pausedPrivileges[] = $label;
                }
            }
        }

        $supportCopy = $activeConsequence['nextStepText'] ?? 'Keep going one calm step at a time.';
        $reviewCopy = $activeConsequence['reviewText'] ?? ($pending > 0 ? ($pending === 1 ? '1 task is waiting for review' : "{$pending} tasks are waiting for review") : 'Nothing waiting right now');

        if (!$isWeekday) {
            $heroTitle = 'Today can be lighter';
            $heroBody = "It's the weekend. Check in only if your family asked for something specific.";
        } elseif ($total === 0) {
            $heroTitle = 'Your plan is open';
            $heroBody = 'No chores are assigned for today yet. You can still peek at the week if that helps you orient.';
        } elseif ($percent === 100) {
            $heroTitle = 'You are on track';
            $heroBody = 'Everything for today is complete. The next step is simply to enjoy the rest of your day.';
        } elseif ($nextAssignment) {
            $heroTitle = 'One calm step at a time';
            $heroBody = 'Start with the next routine below. Your family sees the same plan, so expectations stay predictable.';
        } else {
            $heroTitle = 'You are on track';
            $heroBody = 'Your remaining task is already in review. You do not need to repeat anything while you wait.';
        }
    @endphp

    <section class="today-shell">
        <section class="today-hero-card">
            <p class="today-eyebrow">Today</p>
            <h2 class="today-hero-title">{{ $heroTitle }}</h2>
            <p class="today-hero-copy">{{ $heroBody }}</p>

            <div class="today-progress">
                <div class="today-progress-track" aria-hidden="true">
                    <span style="width: {{ $percent }}%"></span>
                </div>
                <span>{{ $date->format('l, F j') }} · {{ $progressLabel }}</span>
            </div>
        </section>

        @if($nextAssignment)
            <section class="today-primary-card">
                <div class="section-header">
                    <h3>Next calm step</h3>
                    <span class="soft-badge">now</span>
                </div>

                <strong class="primary-task-title">{{ $nextAssignment->slot?->title ?? 'Task' }}</strong>
                @if($nextAssignment->status === 'rejected' && !empty($rejectionNotes[$nextAssignment->slot_id]))
                    <p class="primary-task-copy">{{ $rejectionNotes[$nextAssignment->slot_id] }}</p>
                @else
                    <p class="primary-task-copy">When you finish, send a photo so your grown-up can review it.</p>
                @endif

                <a href="{{ route('app.submit') }}?slot={{ $nextAssignment->slot_id }}" class="btn btn-primary btn-block">
                    {{ $nextAssignment->status === 'rejected' ? 'Try again and submit' : 'Open checklist' }}
                </a>
            </section>
        @endif

        <section class="today-chip-row" aria-label="Supportive status details">
            <div class="today-chip">
                <strong>{{ $isGrounded ? 'Paused' : 'Progress' }}</strong>
                <span>
                    @if($isGrounded)
                        {{ !empty($pausedPrivileges) ? implode(', ', $pausedPrivileges) : 'Privileges paused' }}
                    @else
                        {{ $progressLabel }}
                    @endif
                </span>
            </div>
            <div class="today-chip">
                <strong>{{ $isGrounded ? 'Review' : 'Waiting' }}</strong>
                <span>{{ $reviewCopy }}</span>
            </div>
            <div class="today-chip">
                <strong>Support</strong>
                <span>{{ $supportCopy }}</span>
            </div>
        </section>

        @if($isWeekday && $total > 0)
            <section class="today-list-card">
                <div class="section-header">
                    <h3>My rhythm</h3>
                    <span class="soft-badge">steady</span>
                </div>

                @foreach($assignments as $assignment)
                    @php
                        $status = $assignment->status;
                        $isRejected = $status === 'rejected';
                        $statusLabel = match ($status) {
                            'approved' => 'Done',
                            'pending' => 'Waiting',
                            'rejected' => 'Try again',
                            default => 'Now',
                        };
                        $statusClass = match ($status) {
                            'approved' => 'done',
                            'pending' => 'waiting',
                            'rejected' => 'retry',
                            default => 'current',
                        };
                        $metaText = match ($status) {
                            'approved' => 'Reviewed and complete',
                            'pending' => 'Sent and waiting for review',
                            'rejected' => $rejectionNotes[$assignment->slot_id] ?? 'Take another pass and submit when ready',
                            default => 'Ready when you are',
                        };
                    @endphp

                    <div class="today-task {{ $statusClass }}">
                        <span class="today-task-mark">{{ $statusLabel }}</span>
                        <div class="today-task-body">
                            <strong>{{ $assignment->slot?->title ?? 'Task' }}</strong>
                            <p>{{ $metaText }}</p>
                        </div>
                        @if(!in_array($status, ['approved', 'pending'], true))
                            <a href="{{ route('app.submit') }}?slot={{ $assignment->slot_id }}" class="task-link">
                                {{ $isRejected ? 'Resubmit' : 'Start' }}
                            </a>
                        @endif
                    </div>
                @endforeach
            </section>
        @else
            <section class="today-list-card">
                <div class="section-header">
                    <h3>My rhythm</h3>
                    <span class="soft-badge">light</span>
                </div>
                <p class="empty-state mb-0">
                    @if(!$isWeekday)
                        Weekend days can stay simple. Check the week view only if you want a bigger picture.
                    @else
                        No chores are listed for today.
                    @endif
                </p>
            </section>
        @endif

        <details class="today-disclosure">
            <summary>Family context</summary>
            <div class="today-disclosure-panel">
                <p>Use the shared week only when it helps you orient. You do not need to hold the whole plan in your head all at once.</p>
                <div class="disclosure-actions">
                    <a href="{{ route('app.rules') }}" class="btn btn-secondary btn-block">See My Week</a>
                </div>
            </div>
        </details>

        <details class="today-disclosure caregiver-note {{ $isGrounded ? 'open-tone' : '' }}">
            <summary>Caregiver note</summary>
            <div class="today-disclosure-panel">
                <p class="mb-0">
                    @if($isGrounded)
                        {{ $activeConsequence['reviewText'] ?? 'A review is coming soon.' }} Keep following the plan and check in calmly when today&apos;s work is done.
                    @elseif($pending > 0)
                        Something is already waiting for review. You can pause here until a grown-up checks it.
                    @else
                        Keep moving one step at a time. You do not need to rush.
                    @endif
                </p>
            </div>
        </details>

        @if(!$isGrounded)
            <section class="today-encouragement">
                <p class="mb-0">You&apos;re doing great. Small steady steps count.</p>
            </section>
        @endif
    </section>
@endsection

@push('head')
<style>
    .today-shell {
        display: grid;
        gap: var(--space-4);
        padding-bottom: var(--space-4);
    }

    .today-hero-card,
    .today-primary-card,
    .today-list-card,
    .today-disclosure,
    .today-encouragement {
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.94);
        box-shadow: 0 12px 28px rgba(45, 55, 72, 0.08);
    }

    .today-hero-card {
        padding: 1.1rem;
        background: linear-gradient(160deg, rgba(255, 255, 255, 0.98), rgba(236, 244, 242, 0.98));
    }

    .today-eyebrow {
        margin-bottom: var(--space-2);
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--text-muted);
    }

    .today-hero-title,
    .today-primary-card h3,
    .today-list-card h3,
    .today-disclosure summary {
        font-family: "Avenir Next Rounded", "Trebuchet MS", "Segoe UI", sans-serif;
    }

    .today-hero-title {
        margin-bottom: var(--space-3);
        font-size: clamp(1.8rem, 6vw, 2.4rem);
        line-height: 1.05;
    }

    .today-hero-copy,
    .primary-task-copy,
    .today-task p,
    .today-disclosure-panel p,
    .empty-state {
        color: var(--text-secondary);
    }

    .today-progress {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-top: var(--space-4);
        font-size: 0.9rem;
        color: var(--text-secondary);
    }

    .today-progress-track {
        width: 100%;
        height: 10px;
        border-radius: 999px;
        background: #dfe8e6;
        overflow: hidden;
    }

    .today-progress-track span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--success) 0%, var(--primary) 100%);
    }

    .today-primary-card,
    .today-list-card {
        padding: 1rem;
    }

    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: var(--space-3);
        margin-bottom: var(--space-4);
    }

    .section-header h3 {
        margin: 0;
        font-size: 1.25rem;
    }

    .soft-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
        background: rgba(74, 144, 164, 0.12);
        color: var(--primary-dark);
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: lowercase;
    }

    .primary-task-title {
        display: block;
        font-size: 1.25rem;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .today-chip-row {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .today-chip {
        padding: 0.85rem 0.75rem;
        border-radius: 20px;
        background: rgba(232, 167, 86, 0.14);
        text-align: center;
        color: #6e562f;
        min-height: 100%;
    }

    .today-chip strong,
    .today-chip span {
        display: block;
    }

    .today-chip strong {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .today-chip span {
        margin-top: 0.35rem;
        font-size: 0.9rem;
        font-weight: 600;
        line-height: 1.35;
    }

    .today-task {
        display: flex;
        align-items: start;
        gap: 0.75rem;
        padding: 0.9rem 0;
        border-top: 1px solid rgba(45, 55, 72, 0.08);
    }

    .today-task:first-of-type {
        border-top: 0;
        padding-top: 0;
    }

    .today-task:last-of-type {
        padding-bottom: 0;
    }

    .today-task-mark {
        min-width: 4.25rem;
        padding: 0.35rem 0.5rem;
        border-radius: 999px;
        background: #edf3f2;
        color: var(--text-primary);
        text-align: center;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .today-task.done .today-task-mark {
        background: rgba(124, 181, 134, 0.18);
        color: var(--success-dark);
    }

    .today-task.waiting .today-task-mark {
        background: rgba(74, 144, 164, 0.15);
        color: var(--primary-dark);
    }

    .today-task.retry .today-task-mark {
        background: rgba(232, 167, 86, 0.18);
        color: var(--attention-dark);
    }

    .today-task.current .today-task-mark {
        background: rgba(107, 142, 123, 0.18);
        color: var(--secondary-dark);
    }

    .today-task-body {
        flex: 1;
        min-width: 0;
    }

    .today-task-body strong {
        display: block;
        margin-bottom: 0.2rem;
    }

    .today-task-body p {
        margin: 0;
        font-size: 0.92rem;
    }

    .task-link {
        flex-shrink: 0;
        align-self: center;
        font-size: 0.9rem;
        font-weight: 700;
    }

    .today-disclosure {
        overflow: hidden;
    }

    .today-disclosure summary {
        list-style: none;
        cursor: pointer;
        padding: 1rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .today-disclosure summary::-webkit-details-marker {
        display: none;
    }

    .today-disclosure-panel {
        padding: 0 1rem 1rem;
    }

    .disclosure-actions {
        margin-top: var(--space-4);
    }

    .caregiver-note.open-tone {
        background: color-mix(in srgb, var(--attention) 8%, white);
    }

    .today-encouragement {
        padding: 1rem 1.1rem;
        background: color-mix(in srgb, var(--success) 10%, white);
        color: var(--success-dark);
        text-align: center;
        font-weight: 600;
    }

    @media (max-width: 640px) {
        .today-shell {
            gap: 0.9rem;
        }

        .today-hero-card,
        .today-primary-card,
        .today-list-card,
        .today-disclosure,
        .today-encouragement {
            margin-left: 0;
            margin-right: 0;
            border-radius: 22px;
        }

        .today-chip-row {
            grid-template-columns: 1fr;
        }

        .today-task {
            flex-wrap: wrap;
        }

        .task-link {
            width: 100%;
            padding-top: 0.25rem;
        }
    }
</style>
@endpush
