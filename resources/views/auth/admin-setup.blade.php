@extends('layouts.base')

@section('title', 'Setup - Grounding Buddy')

@section('body')
<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: var(--space-4);">
    <div class="card" style="width: 100%; max-width: 480px;">
        <div class="text-center mb-6">
            <h1 style="color: var(--primary); margin-bottom: var(--space-2);">Welcome to Grounding Buddy</h1>
            <p class="text-muted mb-0">Let's set up your family's account</p>
        </div>

        <form method="POST" action="{{ route('admin.setup.submit') }}">
            @csrf

            <div class="form-group">
                <label for="family_name" class="form-label">Family Name</label>
                <input 
                    type="text" 
                    id="family_name" 
                    name="family_name" 
                    class="form-input" 
                    value="{{ old('family_name') }}"
                    placeholder="e.g., The Smith Family"
                    required 
                    autofocus
                >
                <p class="form-hint">This will be shown in the app header.</p>
                @error('family_name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Admin Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-input" 
                    required
                    minlength="8"
                    autocomplete="new-password"
                >
                <p class="form-hint">At least 8 characters. This is for parent access only.</p>
                @error('password')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input 
                    type="password" 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    class="form-input" 
                    required
                    autocomplete="new-password"
                >
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg mt-6">
                Complete Setup
            </button>
        </form>
    </div>
</div>
@endsection
