@extends('layouts.admin')

@section('title', 'Payout Requests - Grounding Buddy')

@section('header-title', 'Payout Requests')

@section('content')
    @if($payouts->isEmpty())
        <div class="card text-center py-6">
            <p class="text-muted mb-0">No pending payout requests.</p>
        </div>
    @else
        <div class="payout-list">
            @foreach($payouts as $payout)
                <div class="card payout-card mb-3" data-payout-id="{{ $payout->id }}">
                    <div class="payout-header flex justify-between items-start mb-3">
                        <div>
                            <span class="kid-name">{{ $payout->kid?->display_name ?? 'Unknown' }}</span>
                            <span class="badge badge-warning">Pending</span>
                        </div>
                        <span class="text-muted text-sm">
                            {{ $payout->requested_at?->diffForHumans() ?? 'Unknown time' }}
                        </span>
                    </div>

                    <div class="payout-content mb-3">
                        <div class="payout-amounts">
                            @if($payout->requested_cents > 0)
                                <div class="payout-amount">
                                    <span class="amount-icon">💵</span>
                                    <span class="amount-value">${{ number_format($payout->requested_cents / 100, 2) }}</span>
                                </div>
                            @endif
                            @if($payout->requested_phone_min > 0)
                                <div class="payout-amount">
                                    <span class="amount-icon">📱</span>
                                    <span class="amount-value">{{ $payout->requested_phone_min }} min</span>
                                </div>
                            @endif
                            @if($payout->requested_games_min > 0)
                                <div class="payout-amount">
                                    <span class="amount-icon">🎮</span>
                                    <span class="amount-value">{{ $payout->requested_games_min }} min</span>
                                </div>
                            @endif
                            @if($payout->requested_other_min > 0)
                                <div class="payout-amount">
                                    <span class="amount-icon">📺</span>
                                    <span class="amount-value">{{ $payout->requested_other_min }} min</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="payout-actions flex gap-2">
                        <form method="POST" action="{{ route('admin.payouts.decide') }}" class="mb-0">
                            @csrf
                            <input type="hidden" name="payout_id" value="{{ $payout->id }}">
                            <input type="hidden" name="decision" value="approved">
                            <button type="submit" class="btn btn-success">
                                Approve Payout
                            </button>
                        </form>
                        
                        <button 
                            type="button" 
                            class="btn btn-outline"
                            onclick="toggleDenyForm({{ $payout->id }})"
                        >
                            Deny
                        </button>
                    </div>

                    {{-- Hidden deny form with note --}}
                    <div id="deny-form-{{ $payout->id }}" class="deny-form mt-3" style="display: none;">
                        <form method="POST" action="{{ route('admin.payouts.decide') }}">
                            @csrf
                            <input type="hidden" name="payout_id" value="{{ $payout->id }}">
                            <input type="hidden" name="decision" value="denied">
                            
                            <div class="form-group mb-2">
                                <label for="note-{{ $payout->id }}" class="form-label">Reason (optional)</label>
                                <input 
                                    type="text" 
                                    name="note" 
                                    id="note-{{ $payout->id }}"
                                    class="form-input"
                                    placeholder="Optional reason for denial"
                                    maxlength="400"
                                >
                            </div>
                            
                            <div class="flex gap-2">
                                <button type="submit" class="btn btn-attention">
                                    Confirm Denial
                                </button>
                                <button type="button" class="btn btn-link" onclick="toggleDenyForm({{ $payout->id }})">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-4">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
            Back to Dashboard
        </a>
    </div>
@endsection

@push('head')
<style>
    .payout-card {
        border-left: 4px solid var(--warning);
    }

    .kid-name {
        font-size: 1.125rem;
        font-weight: 600;
        margin-right: 0.5rem;
    }

    .payout-amounts {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .payout-amount {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.75rem;
        background: var(--gray-100);
        border-radius: var(--border-radius);
    }

    .amount-icon {
        font-size: 1.25rem;
    }

    .amount-value {
        font-weight: 600;
        font-size: 1rem;
    }

    .deny-form {
        padding: 1rem;
        background: var(--gray-50);
        border-radius: var(--border-radius);
    }

    .badge-warning {
        background: color-mix(in srgb, var(--warning) 15%, white);
        color: var(--warning-dark);
    }
</style>
@endpush

@push('scripts')
<script>
    function toggleDenyForm(payoutId) {
        const form = document.getElementById('deny-form-' + payoutId);
        if (form) {
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    }
</script>
@endpush
