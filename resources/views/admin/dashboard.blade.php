@extends('layouts.admin')

@section('title', 'Dashboard - Grounding Buddy')

@section('header-title', $familyName . ' Dashboard')

@section('content')
    {{-- Freeze Banner --}}
    @if($isFrozen)
        <div class="freeze-banner mb-4">
            <strong>Writes are frozen.</strong>
            All create, update, and delete actions are blocked until the freeze is lifted.
            Contact the system administrator or remove the freeze flag file.
        </div>
    @endif

    {{-- Quick Stats --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $kids->count() }}</div>
            <div class="stat-label">Kids</div>
        </div>
        
        <a href="{{ route('admin.reviews') }}" class="stat-card stat-card-link" style="{{ $pendingReviews > 0 ? 'border:1px solid var(--attention);background:var(--attention-light);' : '' }}">
            <div class="stat-value" style="color: {{ $pendingReviews > 0 ? 'var(--attention)' : 'var(--success)' }}">
                {{ $pendingReviews }}
            </div>
            <div class="stat-label">Pending Reviews</div>
        </a>
        
        <div class="stat-card">
            <div class="stat-value">{{ $todaySubmissions }}</div>
            <div class="stat-label">Today's Activity</div>
        </div>
        
        @if($kidsNeedingAttention > 0)
        <div class="stat-card" style="background-color: var(--attention-light); border: 1px solid var(--attention);">
            <div class="stat-value" style="color: var(--attention-dark);">{{ $kidsNeedingAttention }}</div>
            <div class="stat-label">Privileges Paused</div>
        </div>
        @endif
    </div>

    {{-- Due Infraction Reviews --}}
    @if($dueInfractionReviews->isNotEmpty())
        <div class="card mb-4" style="border-left: 4px solid var(--primary);">
            <div class="card-header">
                <h3 class="card-title">Consequence Reviews Due</h3>
                <a href="{{ route('admin.infractions.review') }}" class="btn btn-primary btn-sm">Review All</a>
            </div>
            @foreach($dueInfractionReviews as $evt)
                <div class="dash-review-item">
                    <strong>{{ $evt->kid?->display_name }}</strong> —
                    {{ $evt->definition?->label }}
                    <span class="text-muted">(due {{ $evt->review_on }})</span>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Pending Submissions (quick inline view) --}}
    @if($pendingItems->isNotEmpty())
        <div class="card mb-4" style="border-left: 4px solid var(--attention);">
            <div class="card-header">
                <h3 class="card-title">Waiting for Review</h3>
                <a href="{{ route('admin.reviews') }}" class="btn btn-attention btn-sm">Review All</a>
            </div>
            @foreach($pendingItems as $sub)
                <div class="dash-review-item flex justify-between items-center">
                    <div>
                        <strong>{{ $sub->kid?->display_name }}</strong> —
                        @if($sub->kind === 'base' && $sub->slot)
                            {{ $sub->slot->title }}
                        @elseif($sub->kind === 'bonus')
                            Bonus task
                        @else
                            Submission
                        @endif
                        <span class="text-muted">{{ $sub->submitted_at?->diffForHumans() }}</span>
                    </div>
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('admin.reviews.decide') }}">
                            @csrf
                            <input type="hidden" name="submission_id" value="{{ $sub->id }}">
                            <input type="hidden" name="decision" value="approved">
                            <button type="submit" class="btn btn-success btn-sm">Approve</button>
                        </form>
                    </div>
                </div>
            @endforeach
            @if($pendingReviews > $pendingItems->count())
                <div class="text-muted text-center p-2" style="font-size: 0.875rem;">
                    + {{ $pendingReviews - $pendingItems->count() }} more
                </div>
            @endif
        </div>
    @endif

    {{-- Pending Payout Requests --}}
    @if($pendingPayouts->isNotEmpty())
        <div class="card mb-4" style="border-left: 4px solid var(--secondary);">
            <div class="card-header">
                <h3 class="card-title">Payout Requests</h3>
                <a href="{{ route('admin.payouts') }}" class="btn btn-secondary btn-sm">Review All</a>
            </div>
            @foreach($pendingPayouts as $payout)
                <div class="dash-review-item flex justify-between items-center">
                    <div>
                        <strong>{{ $payout->kid?->display_name }}</strong> —
                        @if($payout->requested_cents > 0)
                            ${{ number_format($payout->requested_cents / 100, 2) }}
                        @endif
                        @if($payout->requested_phone_min > 0)
                            📱 {{ $payout->requested_phone_min }}min
                        @endif
                        @if($payout->requested_games_min > 0)
                            🎮 {{ $payout->requested_games_min }}min
                        @endif
                        <span class="text-muted">{{ $payout->requested_at?->diffForHumans() }}</span>
                    </div>
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('admin.payouts.decide') }}">
                            @csrf
                            <input type="hidden" name="payout_id" value="{{ $payout->id }}">
                            <input type="hidden" name="decision" value="approved">
                            <button type="submit" class="btn btn-success btn-sm">Approve</button>
                        </form>
                    </div>
                </div>
            @endforeach
            @if($pendingPayoutCount > $pendingPayouts->count())
                <div class="text-muted text-center p-2" style="font-size: 0.875rem;">
                    + {{ $pendingPayoutCount - $pendingPayouts->count() }} more
                </div>
            @endif
        </div>
    @endif

    {{-- Active Locks Detail --}}
    @if($activeLocks->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Active Privilege Pauses</h3>
                <a href="{{ route('admin.privileges') }}" class="btn btn-secondary btn-sm">Manage</a>
            </div>
            @foreach($activeLocks as $item)
                <div class="dash-review-item">
                    <strong>{{ $item['kid']->display_name }}</strong>
                    <div class="lock-badges mt-1">
                        @foreach($item['locks'] as $lock)
                            <span class="badge badge-attention">
                                {{ $lock['label'] }}
                                @if($lock['until'])
                                    — until {{ $lock['until']->format('M j g:ia') }}
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Kids Overview --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Kids</h3>
            <a href="{{ route('admin.family') }}" class="btn btn-secondary">Manage Family</a>
        </div>

        @if($kids->isEmpty())
            <div class="text-center p-6">
                <p class="text-muted mb-4">No kids have been added yet.</p>
                <a href="{{ route('admin.family') }}" class="btn btn-primary">Add Your First Kid</a>
            </div>
        @else
            <div class="admin-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kids as $kid)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <span class="kid-avatar-small">{{ strtoupper(substr($kid->name, 0, 1)) }}</span>
                                        <span>{{ $kid->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($kid->is_grounded ?? false)
                                        <span class="badge badge-attention">Privileges paused</span>
                                    @else
                                        <span class="badge badge-success">Good standing</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.family') }}?kid={{ $kid->id }}" class="btn btn-secondary" style="padding: var(--space-2) var(--space-4);">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Pending Reviews --}}
    @if($pendingReviews > 0)
    <div class="card" style="border-left: 4px solid var(--attention);">
        <div class="card-header">
            <h3 class="card-title">Pending Reviews</h3>
            <a href="{{ route('admin.reviews') }}" class="btn btn-attention">Review All</a>
        </div>
        <p class="text-muted mb-0">
            There {{ $pendingReviews === 1 ? 'is' : 'are' }} {{ $pendingReviews }} 
            submission{{ $pendingReviews === 1 ? '' : 's' }} waiting for your review.
        </p>
    </div>
    @endif

    {{-- Quick Actions --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Quick Actions</h3>
        </div>
        <div class="flex gap-4" style="flex-wrap: wrap;">
            <a href="{{ route('admin.reviews') }}" class="btn btn-outline">
                Review Submissions
            </a>
            <a href="{{ route('admin.definitions') }}" class="btn btn-outline">
                Edit Rules & Bonuses
            </a>
            <a href="{{ route('admin.settings') }}" class="btn btn-outline">
                App Settings
            </a>
        </div>
    </div>
@endsection

@push('head')
<style>
    .kid-avatar-small {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        background: var(--secondary);
        color: white;
        border-radius: 50%;
        font-weight: 600;
        font-size: 0.875rem;
    }
    .stat-card-link {
        text-decoration: none;
        color: inherit;
        cursor: pointer;
        transition: transform 0.1s ease;
    }
    .stat-card-link:hover {
        transform: scale(1.03);
    }
    .dash-review-item {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.9375rem;
    }
    .dash-review-item:last-child {
        border-bottom: none;
    }
    .lock-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }
    .freeze-banner {
        padding: 1rem;
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: var(--border-radius);
        color: #664d03;
        font-size: 0.9375rem;
    }
    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
</style>
@endpush
