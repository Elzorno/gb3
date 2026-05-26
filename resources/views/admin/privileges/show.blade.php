@extends('layouts.admin')

@section('title', $kid->display_name . ' - Privileges - Grounding Buddy')

@section('header-title', $kid->display_name . "'s Privileges")

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.privileges') }}" class="text-muted" style="text-decoration: none;">
            ← Back to All Kids
        </a>
    </div>

    <div class="privilege-detail-grid">
        {{-- Current Status --}}
        <div class="card">
            <h3 class="card-title">Current Status</h3>
            
            @php
                $hasGrounding = $privilege->phone_locked || $privilege->games_locked || $privilege->other_locked;
            @endphp

            @if($hasGrounding)
                <div class="alert alert-attention mb-4">
                    {{ $kid->display_name }} has privileges paused.
                </div>
            @else
                <div class="alert alert-success mb-4">
                    {{ $kid->display_name }} is in good standing.
                </div>
            @endif

            {{-- Individual privilege toggles --}}
            <div class="privilege-toggles">
                @foreach(['phone' => 'Phone', 'games' => 'Games', 'other' => 'Other'] as $type => $label)
                    @php
                        $locked = $privilege->{$type . '_locked'};
                        $until = $privilege->{$type . '_locked_until'};
                    @endphp
                    <div class="privilege-toggle-row {{ $locked ? 'locked' : '' }}">
                        <div class="privilege-info">
                            <span class="privilege-icon">{{ $locked ? '🔒' : '✓' }}</span>
                            <div>
                                <strong>{{ $label }}</strong>
                                @if($locked)
                                    <span class="text-attention"> - Locked</span>
                                    @if($until)
                                        <p class="text-muted text-sm mb-0">
                                            Until {{ $until->format('M j, Y g:i A') }}
                                            ({{ $until->diffForHumans() }})
                                        </p>
                                    @else
                                        <p class="text-muted text-sm mb-0">Indefinitely</p>
                                    @endif
                                @else
                                    <span class="text-safe"> - Available</span>
                                @endif
                            </div>
                        </div>
                        
                        <form method="POST" action="{{ route('admin.privileges.toggle', $kid) }}" class="mb-0">
                            @csrf
                            <input type="hidden" name="type" value="{{ $type }}">
                            <button type="submit" class="btn {{ $locked ? 'btn-success' : 'btn-attention' }} btn-sm">
                                {{ $locked ? 'Unlock' : 'Lock' }}
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

            @if($hasGrounding)
                <form method="POST" action="{{ route('admin.privileges.lift', $kid) }}" class="mt-4">
                    @csrf
                    <button type="submit" class="btn btn-success w-full">
                        Lift All Grounding
                    </button>
                </form>
            @endif
        </div>

        {{-- Time Banks --}}
        <div class="card">
            <h3 class="card-title">Balances</h3>
            <p class="text-muted mb-4">
                Balances earned through bonuses and good behavior.
            </p>

            <div class="balance-display mb-4 p-3" style="background: var(--neutral-50); border-radius: var(--border-radius);">
                <div class="flex justify-between items-center mb-2">
                    <span class="form-label mb-0">💵 Cash Balance</span>
                    <strong>${{ number_format(($privilege->bank_cents ?? 0) / 100, 2) }}</strong>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <span class="form-label mb-0">📱 Phone Minutes</span>
                    <strong>{{ $privilege->bank_phone_min ?? 0 }} min</strong>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <span class="form-label mb-0">🎮 Game Minutes</span>
                    <strong>{{ $privilege->bank_games_min ?? 0 }} min</strong>
                </div>
                <div class="flex justify-between items-center">
                    <span class="form-label mb-0">📺 Other Minutes</span>
                    <strong>{{ $privilege->bank_other_min ?? 0 }} min</strong>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.privileges.bank', $kid) }}">
                @csrf
                
                @foreach(['phone' => 'Phone', 'games' => 'Games', 'other' => 'Other'] as $type => $label)
                    @php $bankField = "bank_{$type}_min"; @endphp
                    <div class="form-group">
                        <label for="{{ $bankField }}" class="form-label">
                            {{ $label }} Bank (minutes)
                        </label>
                        <div class="flex gap-2 items-center">
                            <input 
                                type="number" 
                                id="{{ $bankField }}" 
                                name="{{ $bankField }}" 
                                class="form-input"
                                value="{{ $privilege->$bankField ?? 0 }}"
                                min="0"
                                step="5"
                            >
                            <span class="text-muted">
                                = {{ floor(($privilege->$bankField ?? 0) / 60) }}h {{ ($privilege->$bankField ?? 0) % 60 }}m
                            </span>
                        </div>
                    </div>
                @endforeach

                <button type="submit" class="btn btn-primary">
                    Update Banks
                </button>
            </form>
        </div>

        {{-- Quick Ground --}}
        <div class="card">
            <h3 class="card-title">Apply Grounding</h3>
            <p class="text-muted mb-4">
                Set specific privileges to be locked for a duration.
            </p>

            <form method="POST" action="{{ route('admin.privileges.ground', $kid) }}">
                @csrf
                
                <div class="form-group">
                    <label class="form-label">Privileges to Lock</label>
                    <div class="checkbox-group">
                        <label class="checkbox-item">
                            <input type="checkbox" name="types[]" value="phone" 
                                {{ !$privilege->phone_locked ? 'checked' : '' }}>
                            <span>Phone</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="types[]" value="games" 
                                {{ !$privilege->games_locked ? 'checked' : '' }}>
                            <span>Games</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="types[]" value="other">
                            <span>Other (TV, etc.)</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="duration_days" class="form-label">Duration</label>
                    <select name="duration_days" id="duration_days" class="form-input">
                        <option value="0">Until I lift it</option>
                        <option value="1">1 day</option>
                        <option value="2">2 days</option>
                        <option value="3">3 days</option>
                        <option value="5">5 days</option>
                        <option value="7">1 week</option>
                        <option value="14">2 weeks</option>
                        <option value="30">1 month</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-attention">
                    Apply Grounding
                </button>
            </form>
        </div>
    </div>

    <style>
        .privilege-detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 350px), 1fr));
            gap: 1.5rem;
        }

        .privilege-toggles {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .privilege-toggle-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            border-radius: var(--border-radius);
            background: var(--gray-50);
        }

        .privilege-toggle-row.locked {
            background: color-mix(in srgb, var(--attention) 10%, white);
            border-left: 3px solid var(--attention);
        }

        .privilege-info {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .privilege-icon {
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .alert {
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius);
        }

        .alert-attention {
            background: color-mix(in srgb, var(--attention) 15%, white);
            border: 1px solid var(--attention);
            color: var(--attention-dark);
        }

        .alert-success {
            background: color-mix(in srgb, var(--safe) 15%, white);
            border: 1px solid var(--safe);
            color: var(--safe-dark);
        }

        .text-attention {
            color: var(--attention);
        }

        .text-safe {
            color: var(--safe);
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        @media (max-width: 640px) {
            .privilege-toggle-row {
                flex-direction: column;
                align-items: stretch;
                gap: 0.75rem;
            }

            .privilege-info {
                min-width: 0;
            }

            .balance-display .flex {
                align-items: flex-start;
                flex-direction: column;
                gap: 0.25rem;
            }

            .form-group .flex.gap-2.items-center {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
@endsection
