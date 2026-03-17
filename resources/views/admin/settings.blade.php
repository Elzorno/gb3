@extends('layouts.admin')

@section('title', 'Settings - Grounding Buddy')

@section('header-title', 'Settings')

@section('content')
    @if(session('success'))
        <div class="alert alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Branding Settings --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Branding</h3>
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4">
            @csrf
            
            <div class="form-group">
                <label for="family_name" class="form-label">Family Name</label>
                <input 
                    type="text" 
                    id="family_name" 
                    name="family_name" 
                    class="form-input"
                    value="{{ old('family_name', $familyName ?? '') }}"
                    placeholder="The Smith Family"
                >
                @error('family_name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
                <p class="form-hint">Displayed in the app header.</p>
            </div>
            
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>

    {{-- Security Settings --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Security</h3>
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="section" value="password">
            
            <div class="form-group">
                <label for="current_password" class="form-label">Current Password</label>
                <input 
                    type="password" 
                    id="current_password" 
                    name="current_password" 
                    class="form-input"
                    autocomplete="current-password"
                >
            </div>
            
            <div class="form-group">
                <label for="new_password" class="form-label">New Password</label>
                <input 
                    type="password" 
                    id="new_password" 
                    name="new_password" 
                    class="form-input"
                    autocomplete="new-password"
                    minlength="8"
                >
                <p class="form-hint">At least 8 characters.</p>
            </div>
            
            <div class="form-group">
                <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                <input 
                    type="password" 
                    id="new_password_confirmation" 
                    name="new_password_confirmation" 
                    class="form-input"
                    autocomplete="new-password"
                >
            </div>
            
            <button type="submit" class="btn btn-primary">Change Password</button>
        </form>
    </div>

    {{-- Data Management --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data Management</h3>
        </div>

        <p class="text-muted mb-4">
            These actions affect your data and cannot be undone easily.
        </p>

        <div class="flex gap-4" style="flex-wrap: wrap;">
            <button type="button" class="btn btn-secondary" onclick="alert('Export coming soon')">
                Export Data
            </button>
            <button type="button" class="btn btn-attention" onclick="alert('Reset coming soon')">
                Reset Week
            </button>
        </div>
    </div>
@endsection
