@extends('layouts.kid')

@section('title', 'History - Grounding Buddy')

@section('header-title', 'My History')

@section('content')
    {{-- Weekly stats --}}
    <div class="stats-row mb-4">
        <div class="stat-card">
            <div class="stat-value">{{ $weekStats['chores_done'] }}</div>
            <div class="stat-label">Chores Done</div>
            <div class="stat-period">This week</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $weekStats['bonuses_done'] }}</div>
            <div class="stat-label">Bonuses Done</div>
            <div class="stat-period">This week</div>
        </div>
    </div>

    {{-- Filter tabs --}}
    <div class="filter-tabs mb-4">
        <a href="{{ route('app.history') }}" 
           class="filter-tab {{ $filter === 'all' ? 'active' : '' }}">
            All
        </a>
        <a href="{{ route('app.history', ['filter' => 'chores']) }}" 
           class="filter-tab {{ $filter === 'chores' ? 'active' : '' }}">
            Chores
        </a>
        <a href="{{ route('app.history', ['filter' => 'bonuses']) }}" 
           class="filter-tab {{ $filter === 'bonuses' ? 'active' : '' }}">
            Bonuses
        </a>
        <a href="{{ route('app.history', ['filter' => 'rewards']) }}" 
           class="filter-tab {{ $filter === 'rewards' ? 'active' : '' }}">
            Rewards
        </a>
    </div>

    @if($filter === 'rewards')
        {{-- Reward Ledger --}}
        <div class="card">
            @if($ledger->isEmpty())
                <div class="empty-state">
                    <span class="empty-icon">💰</span>
                    <p class="mb-0">No rewards yet. Complete bonuses to earn rewards!</p>
                </div>
            @else
                <div class="history-list">
                    @foreach($ledger as $entry)
                        <div class="history-item status-{{ $entry->type === 'credit' ? 'approved' : 'rejected' }}">
                            <div class="history-status-icon">
                                {{ $entry->type === 'credit' ? '+' : '−' }}
                            </div>
                            <div class="history-content">
                                <div class="history-title">{{ $entry->note ?? ucfirst($entry->source) }}</div>
                                <div class="history-meta">
                                    <span>{{ $entry->created_at?->diffForHumans() }}</span>
                                </div>
                            </div>
                            <div class="ledger-amounts">
                                @if($entry->cents != 0)
                                    <span class="ledger-amount {{ $entry->cents > 0 ? 'positive' : 'negative' }}">
                                        {{ $entry->cents > 0 ? '+' : '' }}${{ number_format(abs($entry->cents) / 100, 2) }}
                                    </span>
                                @endif
                                @if($entry->phone_min != 0)
                                    <span class="ledger-amount {{ $entry->phone_min > 0 ? 'positive' : 'negative' }}">
                                        📱 {{ $entry->phone_min > 0 ? '+' : '' }}{{ $entry->phone_min }}m
                                    </span>
                                @endif
                                @if($entry->games_min != 0)
                                    <span class="ledger-amount {{ $entry->games_min > 0 ? 'positive' : 'negative' }}">
                                        🎮 {{ $entry->games_min > 0 ? '+' : '' }}{{ $entry->games_min }}m
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @else
    {{-- Submissions list --}}
    <div class="card">
        @if($submissions->isEmpty())
            <div class="empty-state">
                <span class="empty-icon">📋</span>
                <p class="mb-0">No submissions yet. Complete a chore or bonus to see it here!</p>
            </div>
        @else
            <div class="history-list">
                @foreach($submissions as $sub)
                    <div class="history-item status-{{ $sub->status }}">
                        <div class="history-status-icon">
                            @if($sub->status === 'approved')
                                ✓
                            @elseif($sub->status === 'pending')
                                ⏳
                            @elseif($sub->status === 'rejected')
                                ↩
                            @else
                                •
                            @endif
                        </div>
                        
                        <div class="history-content">
                            <div class="history-title">
                                @if($sub->kind === 'base' && $sub->slot)
                                    {{ $sub->slot->title }}
                                @elseif($sub->kind === 'bonus' && $sub->bonusInstance?->definition)
                                    {{ $sub->bonusInstance->definition->title }}
                                @else
                                    Submission
                                @endif
                            </div>
                            <div class="history-meta">
                                <span class="history-type">
                                    {{ $sub->kind === 'bonus' ? '⭐ Bonus' : '📋 Chore' }}
                                </span>
                                <span class="history-date">
                                    {{ $sub->submitted_at?->diffForHumans() ?? 'Unknown' }}
                                </span>
                            </div>
                            @if($sub->status === 'rejected' && ($sub->kid_note ?? $sub->review_note))
                                <div class="history-note">
                                    {{ $sub->kid_note ?? $sub->review_note }}
                                </div>
                            @endif
                            @if($sub->status === 'rejected')
                                <div class="history-recovery">
                                    You can redo this and resubmit when ready.
                                </div>
                            @endif
                        </div>

                        <div class="history-status-badge">
                            @if($sub->status === 'approved')
                                <span class="badge badge-success">Done!</span>
                            @elseif($sub->status === 'pending')
                                <span class="badge badge-warning">Waiting</span>
                            @elseif($sub->status === 'rejected')
                                <span class="badge badge-attention">Try again</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($submissions->hasPages())
                <div class="pagination-wrapper mt-4">
                    {{ $submissions->links() }}
                </div>
            @endif
        @endif
    </div>
    @endif

    <div class="encouragement mt-4">
        <span class="encouragement-emoji">📊</span>
        <p class="mb-0">Keep up the great work!</p>
    </div>
@endsection

@push('head')
<style>
    /* Stats row */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    .stat-card {
        background: var(--bg-card);
        border-radius: var(--border-radius-lg);
        padding: 1rem;
        text-align: center;
        box-shadow: var(--shadow-sm);
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--secondary);
        line-height: 1;
    }

    .stat-label {
        font-weight: 600;
        font-size: 0.9375rem;
        color: var(--text-primary);
        margin-top: 0.25rem;
    }

    .stat-period {
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-top: 0.125rem;
    }

    /* Filter tabs */
    .filter-tabs {
        display: flex;
        gap: 0.5rem;
        background: var(--gray-100);
        padding: 0.25rem;
        border-radius: var(--border-radius);
    }

    .filter-tab {
        flex: 1;
        padding: 0.625rem 1rem;
        text-align: center;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9375rem;
        color: var(--text-secondary);
        border-radius: var(--border-radius);
        transition: all 0.2s ease;
    }

    .filter-tab.active {
        background: white;
        color: var(--secondary);
        box-shadow: var(--shadow-sm);
    }

    /* History list */
    .history-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .history-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1rem;
        background: var(--gray-50);
        border-radius: var(--border-radius);
        border-left: 4px solid var(--gray-300);
    }

    .history-item.status-approved {
        border-left-color: var(--success);
    }

    .history-item.status-pending {
        border-left-color: var(--warning);
    }

    .history-item.status-rejected {
        border-left-color: var(--attention);
    }

    .history-status-icon {
        flex-shrink: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 1rem;
        font-weight: 700;
        background: var(--gray-200);
        color: var(--gray-600);
    }

    .status-approved .history-status-icon {
        background: var(--success);
        color: white;
    }

    .status-pending .history-status-icon {
        background: var(--warning-light);
        color: var(--warning-dark);
    }

    .status-rejected .history-status-icon {
        background: color-mix(in srgb, var(--attention) 20%, white);
        color: var(--attention-dark);
    }

    .history-content {
        flex: 1;
        min-width: 0;
    }

    .history-title {
        font-weight: 600;
        font-size: 1rem;
        color: var(--text-primary);
    }

    .history-meta {
        display: flex;
        gap: 0.75rem;
        font-size: 0.8125rem;
        color: var(--text-muted);
        margin-top: 0.25rem;
    }

    .history-note {
        margin-top: 0.5rem;
        padding: 0.5rem;
        background: color-mix(in srgb, var(--attention) 10%, white);
        border-radius: var(--border-radius);
        font-size: 0.875rem;
        color: var(--attention-dark);
    }

    .history-recovery {
        margin-top: 0.25rem;
        font-size: 0.8rem;
        color: var(--secondary);
        font-style: italic;
    }

    .history-status-badge {
        flex-shrink: 0;
    }

    /* Badges */
    .badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: var(--border-radius);
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-success {
        background: color-mix(in srgb, var(--success) 15%, white);
        color: var(--success-dark);
    }

    .badge-warning {
        background: color-mix(in srgb, var(--warning) 15%, white);
        color: var(--warning-dark);
    }

    .badge-attention {
        background: color-mix(in srgb, var(--attention) 15%, white);
        color: var(--attention-dark);
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 2rem 1rem;
    }

    .empty-icon {
        font-size: 2.5rem;
        display: block;
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: var(--text-muted);
    }

    /* Pagination */
    .pagination-wrapper {
        display: flex;
        justify-content: center;
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

    /* Ledger amounts */
    .ledger-amounts {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.125rem;
        flex-shrink: 0;
    }

    .ledger-amount {
        font-weight: 600;
        font-size: 0.875rem;
    }

    .ledger-amount.positive {
        color: var(--success-dark);
    }

    .ledger-amount.negative {
        color: var(--attention-dark);
    }

    @media (max-width: 640px) {
        .stats-row {
            grid-template-columns: 1fr;
        }

        .filter-tabs {
            overflow-x: auto;
            padding-bottom: 0.35rem;
            scrollbar-width: none;
        }

        .filter-tabs::-webkit-scrollbar {
            display: none;
        }

        .filter-tab {
            flex: 0 0 auto;
            min-width: 6rem;
        }

        .history-item {
            flex-direction: column;
        }

        .history-meta {
            flex-direction: column;
            gap: 0.25rem;
        }

        .history-status-badge {
            width: 100%;
        }
    }
</style>
@endpush
