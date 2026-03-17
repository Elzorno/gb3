@extends('layouts.kid')

@section('title', 'Today - Grounding Buddy')

@section('header-title')
    Hi, {{ $kid?->display_name ?? session('kid_name', 'there') }}!
@endsection

@section('content')
    {{-- Status banner if grounded --}}
    @if($isGrounded)
        <div class="status-banner status-banner-grounded mb-4">
            <div class="status-banner-icon">⏳</div>
            <div class="status-banner-content">
                <div class="status-banner-title">On Consequence</div>
                <p class="status-banner-text mb-0">
                    Complete your tasks and show good behavior to work toward getting back on track.
                </p>
            </div>
        </div>
    @endif

    {{-- Daily Progress --}}
    <div class="card text-center mb-4">
        <p class="text-muted mb-2">{{ $date->format('l, F j') }}</p>
        
        @if (!$isWeekday)
            <div class="encouragement">
                <span class="encouragement-emoji">🎉</span>
                <p class="mb-0">It's the weekend! Enjoy your day.</p>
            </div>
        @elseif ($assignments->isEmpty())
            <div class="encouragement">
                <span class="encouragement-emoji">✨</span>
                <p class="mb-0">No chores assigned today. You're free!</p>
            </div>
        @else
            {{-- Progress ring --}}
            @php
                $total = $assignments->count();
                $completed = $assignments->whereIn('status', ['approved', 'completed'])->count();
                $pending = $assignments->where('status', 'pending')->count();
                $percent = $total > 0 ? round(($completed / $total) * 100) : 0;
                $circumference = 2 * 3.14159 * 45;
                $dashOffset = $circumference - ($circumference * $percent / 100);
            @endphp
            
            <div class="progress-ring mb-3">
                <svg width="140" height="140" class="progress-ring-circle">
                    <circle class="progress-ring-bg" cx="70" cy="70" r="45"></circle>
                    <circle 
                        class="progress-ring-progress" 
                        cx="70" 
                        cy="70" 
                        r="45"
                        stroke-dasharray="{{ $circumference }}"
                        stroke-dashoffset="{{ $dashOffset }}"
                        transform="rotate(-90 70 70)"
                    ></circle>
                    <text x="70" y="65" text-anchor="middle" class="progress-ring-text">{{ $completed }}/{{ $total }}</text>
                    <text x="70" y="85" text-anchor="middle" class="progress-ring-label">done</text>
                </svg>
            </div>
            
            @if($percent == 100)
                <div class="encouragement success-encouragement">
                    <span class="encouragement-emoji">🌟</span>
                    <p class="mb-0">Amazing! All tasks completed today!</p>
                </div>
            @elseif($pending > 0)
                <p class="text-muted mb-0">{{ $pending }} task{{ $pending > 1 ? 's' : '' }} waiting for review</p>
            @endif
        @endif
    </div>

    {{-- Task List --}}
    @if ($isWeekday && $assignments->isNotEmpty())
        <div class="card mb-4">
            <h3 class="card-title">Today's Chores</h3>
            
            <ul class="checklist">
                @foreach($assignments as $a)
                    @php
                        $isComplete = in_array($a->status, ['approved', 'completed']);
                        $isPending = $a->status === 'pending';
                        $isRejected = $a->status === 'rejected';
                    @endphp
                    <li class="checklist-item {{ $isComplete ? 'completed' : '' }} {{ $isPending ? 'pending' : '' }} {{ $isRejected ? 'rejected' : '' }}">
                        <div class="checklist-checkbox">
                            @if($isComplete)
                                ✓
                            @elseif($isPending)
                                ⏳
                            @elseif($isRejected)
                                ↩
                            @endif
                        </div>
                        <div class="checklist-content">
                            <div class="checklist-title">{{ $a->slot?->title ?? 'Task' }}</div>
                            <div class="checklist-meta">
                                @if($isComplete)
                                    <span class="text-success">Approved!</span>
                                @elseif($isPending)
                                    <span class="text-warning">Waiting for review</span>
                                @elseif($isRejected)
                                    <span class="text-attention">Needs to be redone</span>
                                @else
                                    <span>Not started</span>
                                @endif
                            </div>
                        </div>
                        
                        @if(!$isComplete && !$isPending)
                            <a href="{{ route('app.submit') }}?slot={{ $a->slot_id }}" class="checklist-action">
                                Submit
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Submit proof button (prominent CTA) --}}
        @php
            $incomplete = $assignments->whereNotIn('status', ['approved', 'completed', 'pending'])->count();
        @endphp
        @if($incomplete > 0)
            <a href="{{ route('app.submit') }}" class="big-action-btn">
                <span class="big-action-btn-icon">📸</span>
                <span class="big-action-btn-text">Submit Completed Chore</span>
                <span class="big-action-btn-sub">Take a photo showing your work</span>
            </a>
        @endif
    @endif

    {{-- Positive reinforcement --}}
    @if (!$isGrounded)
        <div class="encouragement mt-4">
            <span class="encouragement-emoji">⭐</span>
            <p class="mb-0">You're doing great! Keep it up!</p>
        </div>
    @endif
@endsection

@push('head')
<style>
    /* Status banner for grounding */
    .status-banner {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1rem;
        border-radius: var(--border-radius-lg);
        background: color-mix(in srgb, var(--attention) 10%, white);
        border: 1px solid var(--attention);
    }

    .status-banner-icon {
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .status-banner-title {
        font-weight: 600;
        color: var(--attention-dark);
        margin-bottom: 0.25rem;
    }

    .status-banner-text {
        color: var(--text-secondary);
        font-size: 0.9375rem;
    }

    /* Progress ring */
    .progress-ring {
        display: inline-block;
    }

    .progress-ring-bg {
        fill: none;
        stroke: var(--gray-200);
        stroke-width: 8;
    }

    .progress-ring-progress {
        fill: none;
        stroke: var(--secondary);
        stroke-width: 8;
        stroke-linecap: round;
        transition: stroke-dashoffset 0.5s ease;
    }

    .progress-ring-text {
        font-size: 1.75rem;
        font-weight: 700;
        fill: var(--text-primary);
    }

    .progress-ring-label {
        font-size: 0.875rem;
        fill: var(--text-muted);
    }

    /* Checklist styles */
    .checklist {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .checklist-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: var(--gray-50);
        border-radius: var(--border-radius);
        margin-bottom: 0.75rem;
        transition: all 0.2s ease;
    }

    .checklist-item:last-child {
        margin-bottom: 0;
    }

    .checklist-checkbox {
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        border: 3px solid var(--gray-300);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        font-weight: 700;
        background: white;
    }

    .checklist-item.completed .checklist-checkbox {
        background: var(--success);
        border-color: var(--success);
        color: white;
    }

    .checklist-item.pending .checklist-checkbox {
        background: var(--warning-light);
        border-color: var(--warning);
        color: var(--warning-dark);
    }

    .checklist-item.rejected .checklist-checkbox {
        background: color-mix(in srgb, var(--attention) 20%, white);
        border-color: var(--attention);
        color: var(--attention-dark);
    }

    .checklist-content {
        flex: 1;
        min-width: 0;
    }

    .checklist-title {
        font-weight: 600;
        font-size: 1.0625rem;
        color: var(--text-primary);
    }

    .checklist-item.completed .checklist-title {
        text-decoration: line-through;
        color: var(--text-muted);
    }

    .checklist-meta {
        font-size: 0.875rem;
        color: var(--text-muted);
        margin-top: 0.125rem;
    }

    .checklist-action {
        flex-shrink: 0;
        padding: 0.5rem 1rem;
        background: var(--secondary);
        color: white;
        border-radius: var(--border-radius);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .text-success { color: var(--success); }
    .text-warning { color: var(--warning-dark); }
    .text-attention { color: var(--attention); }

    /* Big action button */
    .big-action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 1.5rem;
        background: var(--secondary);
        color: white;
        border-radius: var(--border-radius-lg);
        text-decoration: none;
        transition: transform 0.2s, box-shadow 0.2s;
        box-shadow: var(--shadow-md);
    }

    .big-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .big-action-btn:active {
        transform: scale(0.98);
    }

    .big-action-btn-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .big-action-btn-text {
        font-size: 1.125rem;
        font-weight: 700;
    }

    .big-action-btn-sub {
        font-size: 0.875rem;
        opacity: 0.9;
        margin-top: 0.25rem;
    }

    /* Encouragement */
    .encouragement {
        padding: 1rem;
        text-align: center;
    }

    .encouragement-emoji {
        font-size: 2rem;
        display: block;
        margin-bottom: 0.5rem;
    }

    .encouragement p {
        color: var(--text-secondary);
    }

    .success-encouragement {
        background: color-mix(in srgb, var(--success) 10%, white);
        border-radius: var(--border-radius);
    }

    .success-encouragement p {
        color: var(--success-dark);
        font-weight: 600;
    }
</style>
@endpush
