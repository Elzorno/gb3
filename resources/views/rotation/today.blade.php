@extends('layouts.kid')

@section('title', 'Today - Grounding Buddy')

@section('header-title', 'Today')

@section('content')
    {{-- Status banner if grounded --}}
    @if(isset($currentKid) && ($currentKid->is_grounded ?? false))
        <div class="status-banner status-banner-grounded">
            <div class="status-banner-title">Some privileges are paused</div>
            <p class="status-banner-text">
                Complete your tasks to work toward getting back on track.
            </p>
        </div>
    @endif

    {{-- Daily Progress --}}
    <div class="card text-center">
        <h3 class="card-title">Today's Progress</h3>
        <p class="text-muted">{{ $date }}</p>
        
        @if (!$isWeekday)
            <div class="encouragement">
                <span class="encouragement-emoji">🎉</span>
                <p>It's the weekend! Enjoy your day.</p>
            </div>
        @elseif ($assignments->isEmpty())
            <div class="encouragement">
                <span class="encouragement-emoji">📝</span>
                <p>No tasks assigned yet. Check back later!</p>
            </div>
        @else
            {{-- Progress ring --}}
            @php
                $total = $assignments->count();
                $completed = $assignments->where('status', 'approved')->count();
                $percent = $total > 0 ? round(($completed / $total) * 100) : 0;
                $circumference = 2 * 3.14159 * 45;
                $dashOffset = $circumference - ($circumference * $percent / 100);
            @endphp
            
            <div class="progress-ring">
                <svg width="120" height="120" class="progress-ring-circle">
                    <circle class="progress-ring-bg" cx="60" cy="60" r="45"></circle>
                    <circle 
                        class="progress-ring-progress" 
                        cx="60" 
                        cy="60" 
                        r="45"
                        stroke-dasharray="{{ $circumference }}"
                        stroke-dashoffset="{{ $dashOffset }}"
                        transform="rotate(-90 60 60)"
                    ></circle>
                    <text x="60" y="55" text-anchor="middle" class="progress-ring-text">{{ $completed }}/{{ $total }}</text>
                    <text x="60" y="75" text-anchor="middle" class="progress-ring-label">tasks</text>
                </svg>
            </div>
            
            @if($percent == 100)
                <div class="alert alert-success">
                    Great job! You've completed all your tasks today!
                </div>
            @endif
        @endif
    </div>

    {{-- Task List --}}
    @if ($isWeekday && $assignments->isNotEmpty())
        <div class="card">
            <h3 class="card-title">Today's Tasks</h3>
            
            <ul class="checklist">
                @foreach($assignments as $a)
                    <li class="checklist-item {{ $a->status === 'approved' ? 'completed' : '' }}">
                        <div class="checklist-checkbox"></div>
                        <div class="checklist-content">
                            <div class="checklist-title">{{ $a->slot?->title ?? 'Task' }}</div>
                            <div class="checklist-meta">
                                @if($a->status === 'approved')
                                    Done — nice work!
                                @elseif($a->status === 'pending')
                                    Waiting for review
                                @else
                                    Not started
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Submit proof button --}}
        @if($assignments->where('status', '!=', 'approved')->count() > 0)
            <a href="{{ route('app.submit') }}" class="big-action-btn">
                Submit Proof
                <span class="big-action-btn-sub">Take a photo showing your completed task</span>
            </a>
        @endif
    @endif

    {{-- Encouragement for good behavior --}}
    @if (!($currentKid->is_grounded ?? false))
        <div class="encouragement">
            <span class="encouragement-emoji">⭐</span>
            <p>You're doing great! Keep it up!</p>
        </div>
    @endif
@endsection
