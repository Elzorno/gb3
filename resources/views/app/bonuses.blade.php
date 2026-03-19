@extends('layouts.kid')

@section('title', 'Bonuses - Grounding Buddy')

@section('header-title', 'Bonus Tasks')

@section('content')
    {{-- Current balances --}}
    <div class="card earnings-card text-center mb-4">
        <div class="earnings-amount">
            ${{ number_format($bankCents / 100, 2) }}
        </div>
        <div class="earnings-label">Cash Balance</div>
        @if($bankPhoneMin > 0 || $bankGamesMin > 0)
            <div class="balance-extras mt-2">
                @if($bankPhoneMin > 0)
                    <span class="balance-badge">📱 {{ $bankPhoneMin }} min</span>
                @endif
                @if($bankGamesMin > 0)
                    <span class="balance-badge">🎮 {{ $bankGamesMin }} min</span>
                @endif
            </div>
        @endif
        
        {{-- Payout request section --}}
        <div class="payout-section mt-3">
            @if($pendingPayout)
                <div class="payout-pending">
                    <span class="payout-status-badge">⏳ Payout requested</span>
                    <div class="payout-details">
                        @if($pendingPayout->requested_cents > 0)
                            <span>${{ number_format($pendingPayout->requested_cents / 100, 2) }}</span>
                        @endif
                        @if($pendingPayout->requested_phone_min > 0)
                            <span>📱 {{ $pendingPayout->requested_phone_min }} min</span>
                        @endif
                        @if($pendingPayout->requested_games_min > 0)
                            <span>🎮 {{ $pendingPayout->requested_games_min }} min</span>
                        @endif
                    </div>
                    <div class="payout-waiting">Waiting for review</div>
                </div>
            @elseif($hasPayableBalance)
                <form method="POST" action="{{ route('app.bonuses.payout') }}" class="payout-form">
                    @csrf
                    <button type="submit" class="payout-btn">
                        Request Payout
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- My active bonuses --}}
    @if($myActive->isNotEmpty())
        <div class="card mb-4">
            <h3 class="card-title">My Active Bonuses</h3>
            
            <div class="bonus-list">
                @foreach($myActive as $instance)
                    <div class="bonus-item status-{{ $instance->status }}">
                        <div class="bonus-content">
                            <div class="bonus-title">{{ $instance->definition?->title ?? 'Bonus' }}</div>
                            <div class="bonus-rewards">
                                @if($instance->definition?->reward_cents > 0)
                                    <span class="reward">💵 ${{ number_format($instance->definition->reward_cents / 100, 2) }}</span>
                                @endif
                                @if($instance->definition?->reward_phone_min > 0)
                                    <span class="reward">📱 +{{ $instance->definition->reward_phone_min }}min</span>
                                @endif
                                @if($instance->definition?->reward_games_min > 0)
                                    <span class="reward">🎮 +{{ $instance->definition->reward_games_min }}min</span>
                                @endif
                            </div>
                            <div class="bonus-status">
                                @if($instance->status === 'claimed')
                                    <span class="badge badge-info">Ready to submit</span>
                                @elseif($instance->status === 'pending')
                                    <span class="badge badge-warning">Waiting for review</span>
                                @elseif($instance->status === 'rejected')
                                    <span class="badge badge-attention">Try again</span>
                                @endif
                            </div>
                        </div>
                        
                        @if($instance->status === 'claimed' || $instance->status === 'rejected')
                            <form method="POST" action="{{ route('app.bonuses.submit') }}" enctype="multipart/form-data" class="bonus-submit-form">
                                @csrf
                                <input type="hidden" name="instance_id" value="{{ $instance->id }}">
                                <label class="bonus-action" style="cursor:pointer;">
                                    <input type="file" name="photo" accept="image/*" capture="environment" style="display:none;" onchange="handleBonusPhoto(this)">
                                    📸 Submit Proof
                                </label>
                                <span class="bonus-compress-status" style="display:none;font-size:0.8rem;color:var(--text-muted);">Compressing…</span>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Available bonuses --}}
    <div class="card mb-4">
        <h3 class="card-title">Available Bonuses</h3>
        <p class="text-muted mb-4">Claim a bonus to get started. Complete it and submit proof to earn rewards!</p>
        
        @if($available->isEmpty())
            <div class="empty-state">
                <span class="empty-icon">✨</span>
                <p class="mb-0">No bonuses available right now. Check back later!</p>
            </div>
        @else
            <div class="bonus-list">
                @foreach($available as $instance)
                    <div class="bonus-item available">
                        <div class="bonus-content">
                            <div class="bonus-title">{{ $instance->definition?->title ?? 'Bonus' }}</div>
                            <div class="bonus-rewards">
                                @if($instance->definition?->reward_cents > 0)
                                    <span class="reward">💵 ${{ number_format($instance->definition->reward_cents / 100, 2) }}</span>
                                @endif
                                @if($instance->definition?->reward_phone_min > 0)
                                    <span class="reward">📱 +{{ $instance->definition->reward_phone_min }}min</span>
                                @endif
                                @if($instance->definition?->reward_games_min > 0)
                                    <span class="reward">🎮 +{{ $instance->definition->reward_games_min }}min</span>
                                @endif
                            </div>
                        </div>
                        
                        <form method="POST" action="{{ route('app.bonuses.claim') }}" class="bonus-claim-form">
                            @csrf
                            <input type="hidden" name="instance_id" value="{{ $instance->id }}">
                            <button type="submit" class="bonus-claim-btn">
                                Claim
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Completed bonuses --}}
    @if($myCompleted->isNotEmpty())
        <div class="card">
            <h3 class="card-title">Completed This Week</h3>
            
            <div class="bonus-list completed-list">
                @foreach($myCompleted as $instance)
                    <div class="bonus-item completed">
                        <div class="bonus-check">✓</div>
                        <div class="bonus-content">
                            <div class="bonus-title">{{ $instance->definition?->title ?? 'Bonus' }}</div>
                            <div class="bonus-rewards">
                                @if($instance->definition?->reward_cents > 0)
                                    <span class="reward earned">+${{ number_format($instance->definition->reward_cents / 100, 2) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="encouragement mt-4">
        <span class="encouragement-emoji">⭐</span>
        <p class="mb-0">Bonuses are a great way to earn extra rewards!</p>
    </div>
@endsection

@push('head')
<style>
    /* Earnings card */
    .earnings-card {
        background: linear-gradient(135deg, var(--secondary), var(--secondary-dark, #4a7c6f));
        color: white;
    }

    .earnings-amount {
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1;
    }

    .earnings-label {
        font-size: 0.9375rem;
        opacity: 0.9;
        margin-top: 0.25rem;
    }

    .balance-extras {
        display: flex;
        justify-content: center;
        gap: 0.75rem;
    }

    .balance-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        background: rgba(255,255,255,0.2);
        border-radius: var(--border-radius);
        font-size: 0.875rem;
    }

    /* Bonus list */
    .bonus-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .bonus-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: var(--gray-50);
        border-radius: var(--border-radius);
        transition: all 0.2s ease;
    }

    .bonus-item.available {
        border-left: 4px solid var(--secondary);
    }

    .bonus-item.status-claimed {
        border-left: 4px solid var(--info);
    }

    .bonus-item.status-pending {
        border-left: 4px solid var(--warning);
    }

    .bonus-item.status-rejected {
        border-left: 4px solid var(--attention);
    }

    .bonus-item.completed {
        background: color-mix(in srgb, var(--success) 10%, white);
        border-left: 4px solid var(--success);
    }

    .bonus-check {
        flex-shrink: 0;
        width: 32px;
        height: 32px;
        background: var(--success);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
    }

    .bonus-content {
        flex: 1;
        min-width: 0;
    }

    .bonus-title {
        font-weight: 600;
        font-size: 1rem;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .bonus-rewards {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        font-size: 0.875rem;
    }

    .reward {
        color: var(--text-secondary);
    }

    .reward.earned {
        color: var(--success);
        font-weight: 600;
    }

    .bonus-status {
        margin-top: 0.5rem;
    }

    .bonus-claim-form {
        flex-shrink: 0;
        margin: 0;
    }

    .bonus-claim-btn {
        padding: 0.5rem 1.25rem;
        background: var(--secondary);
        color: white;
        border: none;
        border-radius: var(--border-radius);
        font-weight: 600;
        font-size: 0.9375rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .bonus-claim-btn:hover {
        background: var(--secondary-dark, #4a7c6f);
    }

    .bonus-action {
        flex-shrink: 0;
        padding: 0.5rem 1rem;
        background: var(--primary);
        color: white;
        border-radius: var(--border-radius);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.875rem;
    }

    /* Badges */
    .badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: var(--border-radius);
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-info {
        background: color-mix(in srgb, var(--info) 15%, white);
        color: var(--info-dark, #1565c0);
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

    /* Completed list */
    .completed-list .bonus-item {
        opacity: 0.8;
    }

    .completed-list .bonus-title {
        text-decoration: line-through;
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

    /* Payout section */
    .payout-section {
        border-top: 1px solid rgba(255,255,255,0.2);
        padding-top: 0.75rem;
    }

    .payout-pending {
        padding: 0.5rem;
        background: rgba(255,255,255,0.15);
        border-radius: var(--border-radius);
    }

    .payout-status-badge {
        display: inline-block;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .payout-details {
        display: flex;
        justify-content: center;
        gap: 0.75rem;
        margin-top: 0.25rem;
        font-size: 0.8125rem;
        opacity: 0.9;
    }

    .payout-waiting {
        font-size: 0.75rem;
        margin-top: 0.25rem;
        opacity: 0.75;
    }

    .payout-form {
        margin: 0;
    }

    .payout-btn {
        padding: 0.5rem 1.25rem;
        background: rgba(255,255,255,0.2);
        color: white;
        border: 1px solid rgba(255,255,255,0.3);
        border-radius: var(--border-radius);
        font-weight: 600;
        font-size: 0.9375rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .payout-btn:hover {
        background: rgba(255,255,255,0.3);
        border-color: rgba(255,255,255,0.5);
    }
</style>
@endpush

@push('scripts')
<script>
function handleBonusPhoto(input) {
    var file = input.files[0];
    if (!file) return;
    if (file.size > 20 * 1024 * 1024) {
        alert('Image is too large (max 20MB)');
        input.value = '';
        return;
    }
    var form = input.closest('form');
    var status = form.querySelector('.bonus-compress-status');
    var label = form.querySelector('.bonus-action');
    if (status) status.style.display = 'inline';
    if (label) label.style.display = 'none';
    compressImage(file, 1920, 0.85).then(function(result) {
        // Swap compressed file into the input and submit normally
        var dt = new DataTransfer();
        dt.items.add(result);
        input.files = dt.files;
        form.submit();
    });
}
</script>
@endpush
