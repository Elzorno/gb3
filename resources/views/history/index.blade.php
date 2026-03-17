@extends('layouts.kid')

@section('title', 'History - Grounding Buddy')

@section('header-title', 'My History')

@section('content')
    {{-- Balance Summary --}}
    <div class="card text-center">
        <h3 class="card-title">Your Points</h3>
        @php
            $totalPoints = $rows->where('status', 'approved')->sum(function($r) {
                return $r->points ?? 0;
            });
        @endphp
        <div style="font-size: 2.5rem; font-weight: 700; color: var(--success);">
            {{ number_format($totalPoints) }}
        </div>
        <p class="text-muted mb-0">total points earned</p>
    </div>

    {{-- Filter Form (collapsible on mobile) --}}
    <details class="card" style="padding: var(--space-4);">
        <summary style="cursor: pointer; font-weight: 600; margin: calc(-1 * var(--space-4)); padding: var(--space-4);">
            Filter History
        </summary>
        <form method="GET" action="{{ route('app.history') }}" class="mt-4">
            <div class="flex gap-2" style="flex-wrap: wrap;">
                <select name="status" class="form-select" style="width: auto; height: auto; padding: var(--space-2);">
                    <option value="" {{ $status === '' ? 'selected' : '' }}>All Status</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                <select name="kind" class="form-select" style="width: auto; height: auto; padding: var(--space-2);">
                    <option value="" {{ $kind === '' ? 'selected' : '' }}>All Types</option>
                    <option value="base" {{ $kind === 'base' ? 'selected' : '' }}>Tasks</option>
                    <option value="bonus" {{ $kind === 'bonus' ? 'selected' : '' }}>Bonuses</option>
                </select>
                <button type="submit" class="btn btn-secondary" style="padding: var(--space-2) var(--space-4);">
                    Apply
                </button>
            </div>
        </form>
    </details>

    {{-- Submissions List --}}
    <div class="card">
        <h3 class="card-title">Submissions</h3>
        
        @forelse($rows as $r)
            <div class="ledger-entry">
                <div>
                    <div style="font-weight: 600;">
                        {{ $r->kind === 'bonus' ? 'Bonus' : 'Task' }}
                    </div>
                    <div class="ledger-date">{{ \Carbon\Carbon::parse($r->submitted_at)->format('M j, g:ia') }}</div>
                </div>
                <div class="text-right">
                    @if($r->status === 'approved')
                        <span class="badge badge-success">Approved</span>
                    @elseif($r->status === 'rejected')
                        <span class="badge badge-attention">Try Again</span>
                    @else
                        <span class="badge badge-neutral">Pending</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="encouragement">
                <span class="encouragement-emoji">📋</span>
                <p>No submissions yet.</p>
                <p class="text-muted" style="font-size: 0.875rem;">Complete tasks to see them here!</p>
            </div>
        @endforelse

        @if($rows->hasPages())
            <div class="mt-4 text-center">
                {{ $rows->links() }}
            </div>
        @endif
    </div>

    {{-- Consequences Section --}}
    @if($infractions->isNotEmpty())
        <div class="card" style="border-left: 4px solid var(--attention);">
            <h3 class="card-title">Recent Consequences</h3>
            
            @foreach($infractions as $e)
                @php
                    $blocks = json_decode((string)($e->blocks_json ?? '{}'), true);
                    $blocks = is_array($blocks) ? $blocks : [];
                    $on = [];
                    foreach (['phone', 'games', 'other'] as $w) {
                        if ((int)($blocks[$w] ?? 0) === 1) {
                            $on[] = ucfirst($w);
                        }
                    }
                @endphp
                <div class="ledger-entry">
                    <div>
                        <div style="font-weight: 600;">
                            {{ $e->definition?->label ?? 'Consequence' }}
                        </div>
                        <div class="ledger-date">
                            {{ $e->days_applied }} day{{ $e->days_applied != 1 ? 's' : '' }}
                            @if(!empty($on))
                                · {{ implode(', ', $on) }}
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        @if($e->reviewed_at)
                            <span class="badge badge-neutral">Reviewed</span>
                        @else
                            <span class="badge badge-attention">Active</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
