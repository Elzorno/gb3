@extends('layouts.kid')

@section('title', 'Bonuses - Grounding Buddy')

@section('header-title', 'Bonus Opportunities')

@section('content')
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <p class="text-muted mb-4">Week of {{ $week }}</p>

        @forelse($instances as $inst)
            @php
                $isMine = (int)($inst->claimed_by_kid_id ?? 0) === (int)$kidId;
                $canClaim = $inst->status === 'available' && $kidId > 0;
                $canSubmit = in_array($inst->status, ['claimed', 'rejected'], true) && $isMine;
            @endphp
            
            <div class="bonus-card {{ $inst->status === 'submitted' || $inst->status === 'approved' ? 'bonus-claimed' : '' }}">
                <div class="bonus-title">{{ $inst->definition?->title ?? 'Bonus Opportunity' }}</div>
                <div class="bonus-points">+{{ $inst->definition?->points ?? 0 }} points</div>
                
                @if($inst->status === 'approved')
                    <span class="badge badge-success mt-4">Completed!</span>
                @elseif($inst->status === 'submitted')
                    <span class="badge badge-neutral mt-4">Waiting for Review</span>
                @elseif($inst->status === 'rejected')
                    <div class="alert alert-attention mt-4 mb-0 text-left">
                        <strong>Needs Redo</strong>
                        <p class="mb-0" style="font-weight: normal;">Please try again.</p>
                    </div>
                @elseif($isMine)
                    <span class="badge badge-attention mt-4">You claimed this!</span>
                @endif
                
                @if($canClaim)
                    <form method="POST" action="{{ route('app.bonuses.claim') }}" class="mt-4">
                        @csrf
                        <input type="hidden" name="instance_id" value="{{ $inst->id }}">
                        <button type="submit" class="btn btn-attention btn-block">
                            I'll Do This!
                        </button>
                    </form>
                @endif
                
                @if($canSubmit)
                    <a href="{{ route('app.submit') }}?bonus={{ $inst->id }}" class="btn btn-success btn-block mt-4">
                        Submit Proof
                    </a>
                @endif
            </div>
        @empty
            <div class="encouragement">
                <span class="encouragement-emoji">🎯</span>
                <p>No bonus opportunities available this week.</p>
                <p class="text-muted" style="font-size: 0.875rem;">Check back later - new ones might appear!</p>
            </div>
        @endforelse
    </div>

    <div class="encouragement">
        <span class="encouragement-emoji">⭐</span>
        <p>Bonuses are a great way to earn extra points!</p>
    </div>
@endsection
