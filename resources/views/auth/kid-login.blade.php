@extends('layouts.base')

@section('title', 'Who Are You? - Grounding Buddy')

@section('body')
<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: var(--space-4);">
    <div class="card" style="width: 100%; max-width: 400px;">
        <div class="text-center mb-6">
            <h1 style="color: var(--secondary); margin-bottom: var(--space-2);">Who are you?</h1>
            <p class="text-muted mb-0">Tap your name and enter your PIN</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('app.login.submit') }}" id="kid-login-form">
            @csrf

            {{-- Kid selector - shows all kids as big tappable buttons --}}
            <div class="form-group">
                <div class="kid-selector" role="radiogroup" aria-label="Select your name">
                    @php
                        $kids = \App\Models\Kid::orderBy('sort_order')->get();
                    @endphp
                    
                    @forelse($kids as $kid)
                        <label class="kid-option">
                            <input 
                                type="radio" 
                                name="kid_id" 
                                value="{{ $kid->id }}" 
                                @if(old('kid_id', $kidId ?? '') == $kid->id) checked @endif
                                required
                            >
                            <span class="kid-option-content">
                                <span class="kid-avatar">{{ strtoupper(substr($kid->name, 0, 1)) }}</span>
                                <span class="kid-name">{{ $kid->name }}</span>
                            </span>
                        </label>
                    @empty
                        <div class="alert alert-info">
                            No kids have been added yet. Ask a parent to set up the app first.
                        </div>
                    @endforelse
                </div>
                @error('kid_id')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- PIN input --}}
            <div class="form-group">
                <label class="form-label text-center" style="display: block;">Enter your PIN</label>
                <div class="pin-input">
                    <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="pin-digit" data-index="0" autocomplete="off">
                    <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="pin-digit" data-index="1" autocomplete="off">
                    <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="pin-digit" data-index="2" autocomplete="off">
                    <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="pin-digit" data-index="3" autocomplete="off">
                    <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="pin-digit" data-index="4" autocomplete="off">
                    <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="pin-digit" data-index="5" autocomplete="off">
                </div>
                {{-- Hidden field to hold combined PIN --}}
                <input type="hidden" name="pin" id="pin-combined">
                @error('pin')
                    <p class="form-error text-center">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="background-color: var(--secondary);">
                Log In
            </button>
        </form>

        <div class="text-center mt-6">
            <a href="{{ route('admin.login') }}" style="font-size: 0.875rem;">
                Parent Login →
            </a>
        </div>
    </div>
</div>
@endsection

@push('head')
<style>
    /* Kid selector grid */
    .kid-selector {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: var(--space-3);
        margin-bottom: var(--space-4);
    }
    
    .kid-option {
        cursor: pointer;
    }
    
    .kid-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    
    .kid-option-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: var(--space-2);
        padding: var(--space-4);
        background: var(--neutral-100);
        border: 3px solid transparent;
        border-radius: var(--border-radius-lg);
        transition: all var(--transition-fast);
    }
    
    .kid-option input:checked + .kid-option-content {
        border-color: var(--secondary);
        background: var(--secondary-light);
    }
    
    .kid-option:hover .kid-option-content {
        background: var(--neutral-200);
    }
    
    .kid-avatar {
        width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--secondary);
        color: white;
        border-radius: 50%;
        font-size: 1.5rem;
        font-weight: 700;
    }
    
    .kid-name {
        font-weight: 600;
        color: var(--text-primary);
    }
    
    /* PIN input enhancements */
    .pin-input {
        margin-top: var(--space-2);
    }
    
    .pin-digit {
        font-family: monospace;
    }
</style>
@endpush

@push('scripts')
<script>
    // PIN input auto-advance and combine
    document.addEventListener('DOMContentLoaded', function() {
        const digits = document.querySelectorAll('.pin-digit');
        const combined = document.getElementById('pin-combined');
        const form = document.getElementById('kid-login-form');
        
        function updateCombined() {
            let pin = '';
            digits.forEach(d => pin += d.value);
            combined.value = pin;
        }
        
        digits.forEach((digit, idx) => {
            digit.addEventListener('input', function(e) {
                // Only allow digits
                this.value = this.value.replace(/[^0-9]/g, '');
                
                updateCombined();
                
                // Auto-advance to next field
                if (this.value && idx < digits.length - 1) {
                    digits[idx + 1].focus();
                }
            });
            
            digit.addEventListener('keydown', function(e) {
                // Handle backspace - go to previous field
                if (e.key === 'Backspace' && !this.value && idx > 0) {
                    digits[idx - 1].focus();
                }
            });
            
            // Handle paste
            digit.addEventListener('paste', function(e) {
                e.preventDefault();
                const paste = (e.clipboardData || window.clipboardData).getData('text');
                const pinDigits = paste.replace(/[^0-9]/g, '').slice(0, 6).split('');
                
                pinDigits.forEach((d, i) => {
                    if (digits[i]) digits[i].value = d;
                });
                
                updateCombined();
                
                // Focus last filled or first empty
                const lastIdx = Math.min(pinDigits.length, digits.length) - 1;
                if (lastIdx >= 0) digits[lastIdx].focus();
            });
        });
        
        // Ensure combined PIN is set before submit
        form.addEventListener('submit', function(e) {
            updateCombined();
            if (combined.value.length !== 6) {
                e.preventDefault();
                digits[combined.value.length].focus();
            }
        });
    });
</script>
@endpush
