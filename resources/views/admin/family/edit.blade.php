@extends('layouts.admin')

@section('title', 'Edit ' . $kid->display_name . ' - Grounding Buddy')

@section('header-title', 'Edit Kid')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.family') }}" class="text-muted" style="text-decoration: none;">
            ← Back to Family
        </a>
    </div>

    <div style="max-width: 600px;">
        {{-- Profile Section --}}
        <div class="card">
            <h3 class="card-title">Profile</h3>
            
            <div class="flex items-center gap-4 mb-6">
                <div class="kid-avatar-large">{{ strtoupper(substr($kid->display_name, 0, 1)) }}</div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700;">{{ $kid->display_name }}</div>
                    <div class="text-muted">Added {{ $kid->created_at->diffForHumans() }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.family.update', $kid) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="display_name" class="form-label">Name</label>
                    <input 
                        type="text" 
                        id="display_name" 
                        name="display_name" 
                        class="form-input"
                        value="{{ old('display_name', $kid->display_name) }}"
                        required
                    >
                    @error('display_name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    Save Changes
                </button>
            </form>
        </div>

        {{-- PIN Reset Section --}}
        <div class="card">
            <h3 class="card-title">Reset PIN</h3>
            <p class="text-muted mb-4">
                If {{ $kid->display_name }} forgot their PIN, you can set a new one here.
            </p>

            <form method="POST" action="{{ route('admin.family.reset-pin', $kid) }}">
                @csrf

                <div class="form-group">
                    <label for="pin" class="form-label">New PIN</label>
                    <input 
                        type="text" 
                        id="pin" 
                        name="pin" 
                        class="form-input"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        maxlength="6"
                        placeholder="6-digit PIN"
                        required
                        autocomplete="off"
                        style="font-family: monospace; letter-spacing: 0.5em; max-width: 200px;"
                    >
                    @error('pin')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="pin_confirmation" class="form-label">Confirm PIN</label>
                    <input 
                        type="text" 
                        id="pin_confirmation" 
                        name="pin_confirmation" 
                        class="form-input"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        maxlength="6"
                        placeholder="Confirm PIN"
                        required
                        autocomplete="off"
                        style="font-family: monospace; letter-spacing: 0.5em; max-width: 200px;"
                    >
                    @error('pin_confirmation')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn btn-attention">
                    Reset PIN
                </button>
            </form>
        </div>

        {{-- Privilege Status Section --}}
        <div class="card">
            <h3 class="card-title">Current Status</h3>
            
            @if($privileges)
                <div class="status-grid">
                    <div class="status-item {{ $privileges->phone_locked ? 'locked' : 'unlocked' }}">
                        <div class="status-icon">📱</div>
                        <div class="status-label">Phone</div>
                        <div class="status-value">
                            @if($privileges->phone_locked)
                                Locked
                                @if($privileges->phone_locked_until)
                                    <br><small class="text-muted">until {{ \Carbon\Carbon::parse($privileges->phone_locked_until)->format('M j, g:ia') }}</small>
                                @endif
                            @else
                                <span class="text-success">Available</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="status-item {{ $privileges->games_locked ? 'locked' : 'unlocked' }}">
                        <div class="status-icon">🎮</div>
                        <div class="status-label">Games</div>
                        <div class="status-value">
                            @if($privileges->games_locked)
                                Locked
                                @if($privileges->games_locked_until)
                                    <br><small class="text-muted">until {{ \Carbon\Carbon::parse($privileges->games_locked_until)->format('M j, g:ia') }}</small>
                                @endif
                            @else
                                <span class="text-success">Available</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="status-item {{ $privileges->other_locked ? 'locked' : 'unlocked' }}">
                        <div class="status-icon">🎬</div>
                        <div class="status-label">TV/Other</div>
                        <div class="status-value">
                            @if($privileges->other_locked)
                                Locked
                                @if($privileges->other_locked_until)
                                    <br><small class="text-muted">until {{ \Carbon\Carbon::parse($privileges->other_locked_until)->format('M j, g:ia') }}</small>
                                @endif
                            @else
                                <span class="text-success">Available</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('admin.privileges', ['kid' => $kid->id]) }}" class="btn btn-secondary">
                        Manage Privileges
                    </a>
                </div>
            @else
                <p class="text-muted">No privilege record found.</p>
            @endif
        </div>

        {{-- Danger Zone --}}
        <div class="card" style="border-color: var(--attention);">
            <h3 class="card-title" style="color: var(--attention-dark);">Danger Zone</h3>
            <p class="text-muted mb-4">
                Removing a kid will delete all their history, submissions, and data. This cannot be undone.
            </p>

            <button type="button" class="btn btn-attention" onclick="document.getElementById('delete-modal').classList.remove('hidden')">
                Remove {{ $kid->display_name }}
            </button>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div id="delete-modal" class="modal-overlay hidden">
        <div class="modal-content">
            <h3>Remove {{ $kid->display_name }}?</h3>
            <p class="text-muted">
                This will permanently delete {{ $kid->display_name }} and all their data including submissions, 
                history, and infraction records. This action cannot be undone.
            </p>

            <form method="POST" action="{{ route('admin.family.destroy', $kid) }}">
                @csrf
                @method('DELETE')

                <div class="form-group">
                    <label for="confirm_name" class="form-label">
                        Type "{{ $kid->display_name }}" to confirm
                    </label>
                    <input 
                        type="text" 
                        id="confirm_name" 
                        name="confirm_name" 
                        class="form-input"
                        placeholder="Type name to confirm"
                        required
                        autocomplete="off"
                    >
                    @error('confirm_name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="btn btn-attention">
                        Remove Permanently
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('delete-modal').classList.add('hidden')">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('head')
<style>
    .kid-avatar-large {
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--secondary);
        color: white;
        border-radius: 50%;
        font-weight: 700;
        font-size: 2rem;
        flex-shrink: 0;
    }
    
    .status-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: var(--space-4);
    }
    
    .status-item {
        text-align: center;
        padding: var(--space-4);
        border-radius: var(--border-radius);
        background: var(--neutral-50);
    }
    
    .status-item.locked {
        background: var(--attention-light);
    }
    
    .status-item.unlocked {
        background: var(--success-light);
    }
    
    .status-icon {
        font-size: 2rem;
        margin-bottom: var(--space-2);
    }
    
    .status-label {
        font-weight: 600;
        margin-bottom: var(--space-1);
    }
    
    .status-value {
        font-size: 0.875rem;
    }
    
    /* Modal styles */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: var(--space-4);
        z-index: 1000;
    }
    
    .modal-overlay.hidden {
        display: none;
    }
    
    .modal-content {
        background: var(--bg-card);
        border-radius: var(--border-radius-lg);
        padding: var(--space-6);
        max-width: 480px;
        width: 100%;
        box-shadow: var(--shadow-lg);
    }
    
    .modal-content h3 {
        color: var(--attention-dark);
    }
</style>
@endpush

@push('scripts')
<script>
    // Only allow digits in PIN fields
    document.querySelectorAll('input[name="pin"], input[name="pin_confirmation"]').forEach(function(input) {
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
    
    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.getElementById('delete-modal').classList.add('hidden');
        }
    });
    
    // Close modal on backdrop click
    document.getElementById('delete-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });
</script>
@endpush
