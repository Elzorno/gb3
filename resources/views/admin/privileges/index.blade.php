@extends('layouts.admin')

@section('title', 'Privileges & Grounding - Grounding Buddy')

@section('header-title', 'Privileges & Grounding')

@section('header-subtitle')
    Manage screen time and grounding status
@endsection

@section('content')
    {{-- Overview grid --}}
    <div class="privilege-grid">
        @foreach($kids as $kid)
            @php
                $priv = $kid->privileges;
                $hasGrounding = $priv && ($priv->phone_locked || $priv->games_locked || $priv->other_locked);
            @endphp
            <div class="card privilege-card {{ $hasGrounding ? 'grounded' : '' }}">
                <div class="flex justify-between items-start mb-3">
                    <h3 class="card-title mb-0">{{ $kid->display_name }}</h3>
                    @if($hasGrounding)
                        <span class="badge badge-attention">Privileges paused</span>
                    @else
                        <span class="badge badge-success">Good standing</span>
                    @endif
                </div>

                {{-- Privilege status indicators --}}
                <div class="privilege-indicators mb-4">
                    @foreach(['phone' => 'Phone', 'games' => 'Games', 'other' => 'Other'] as $type => $label)
                        @php
                            $locked = $priv && $priv->{$type . '_locked'};
                            $until = $priv ? $priv->{$type . '_locked_until'} : null;
                            $bankField = "bank_{$type}_min";
                            $bankMin = $priv ? $priv->$bankField : 0;
                        @endphp
                        <div class="privilege-item {{ $locked ? 'locked' : 'unlocked' }}">
                            <div class="privilege-status">
                                <span class="privilege-icon">
                                    @if($locked)
                                        🔒
                                    @else
                                        ✓
                                    @endif
                                </span>
                                <span class="privilege-label">{{ $label }}</span>
                            </div>
                            @if($locked && $until)
                                <span class="privilege-until text-sm text-muted">
                                    Until {{ $until->format('M j, g:i A') }}
                                </span>
                            @elseif($locked)
                                <span class="privilege-until text-sm text-muted">Indefinite</span>
                            @endif
                            @if($bankMin > 0 && !$locked)
                                <span class="privilege-bank text-sm" style="color: var(--safe);">
                                    +{{ $bankMin }} min banked
                                </span>
                            @endif
                        </div>
                    @endforeach
                    @if($priv && ($priv->bank_cents ?? 0) > 0)
                        <div class="privilege-item unlocked">
                            <div class="privilege-status">
                                <span class="privilege-icon">💵</span>
                                <span class="privilege-label">Cash</span>
                            </div>
                            <span class="privilege-bank text-sm" style="color: var(--safe);">
                                ${{ number_format($priv->bank_cents / 100, 2) }}
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Quick actions --}}
                <div class="flex gap-2 flex-wrap">
                    <a href="{{ route('admin.privileges.show', $kid) }}" class="btn btn-secondary btn-sm">
                        Manage
                    </a>
                    @if($hasGrounding)
                        <form method="POST" action="{{ route('admin.privileges.lift', $kid) }}" class="mb-0">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">
                                Lift Grounding
                            </button>
                        </form>
                    @else
                        <button 
                            type="button" 
                            class="btn btn-attention btn-sm"
                            onclick="showGroundingModal('{{ $kid->id }}', '{{ addslashes($kid->display_name) }}')"
                        >
                            Ground
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Quick Grounding Modal --}}
    <div id="grounding-modal" class="modal" style="display: none;">
        <div class="modal-backdrop" onclick="hideGroundingModal()"></div>
        <div class="modal-content card">
            <h3 class="card-title">Ground <span id="ground-kid-name"></span></h3>
            
            <form method="POST" action="" id="grounding-form">
                @csrf
                
                <div class="form-group">
                    <label class="form-label">Restrict Privileges</label>
                    <div class="checkbox-group">
                        <label class="checkbox-item">
                            <input type="checkbox" name="types[]" value="phone" checked>
                            <span>Phone</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="types[]" value="games" checked>
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
                        <option value="7">1 week</option>
                        <option value="14">2 weeks</option>
                    </select>
                </div>

                <div class="flex gap-3 justify-end mt-4">
                    <button type="button" class="btn btn-secondary" onclick="hideGroundingModal()">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-attention">
                        Apply Grounding
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .privilege-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1rem;
        }

        .privilege-card.grounded {
            border-left: 4px solid var(--attention);
        }

        .privilege-indicators {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .privilege-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem;
            border-radius: var(--border-radius);
            background: var(--gray-50);
        }

        .privilege-item.locked {
            background: color-mix(in srgb, var(--attention) 10%, white);
        }

        .privilege-item.unlocked {
            background: color-mix(in srgb, var(--safe) 10%, white);
        }

        .privilege-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex: 0 0 100px;
        }

        .privilege-icon {
            font-size: 1rem;
        }

        .privilege-label {
            font-weight: 500;
        }

        .privilege-until,
        .privilege-bank {
            margin-left: auto;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        .modal {
            position: fixed;
            inset: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            position: relative;
            max-width: 450px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
    </style>

    <script>
        function showGroundingModal(kidId, kidName) {
            document.getElementById('ground-kid-name').textContent = kidName;
            document.getElementById('grounding-form').action = '/admin/privileges/' + kidId + '/ground';
            document.getElementById('grounding-modal').style.display = 'flex';
        }

        function hideGroundingModal() {
            document.getElementById('grounding-modal').style.display = 'none';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideGroundingModal();
            }
        });
    </script>
@endsection
