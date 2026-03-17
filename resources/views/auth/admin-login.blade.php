@extends('layouts.base')

@section('title', 'Admin Login - Grounding Buddy')

@section('body')
<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: var(--space-4);">
    <div class="card" style="width: 100%; max-width: 400px;">
        <div class="text-center mb-6">
            <h1 style="color: var(--primary); margin-bottom: var(--space-2);">Grounding Buddy</h1>
            <p class="text-muted mb-0">Parent/Admin Login</p>
        </div>

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-input" 
                    required 
                    autofocus
                    autocomplete="current-password"
                >
                @error('password')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg">
                Log In
            </button>
        </form>

        <div class="text-center mt-6">
            <a href="{{ route('app.login') }}" style="font-size: 0.875rem;">
                Kid Login →
            </a>
        </div>
    </div>
</div>
@endsection
