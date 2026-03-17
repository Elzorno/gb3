@extends('layouts.admin')

@section('title', 'Add Chore - Grounding Buddy')

@section('header-title', 'Add Chore Slot')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.rotation') }}" class="text-muted" style="text-decoration: none;">
            ← Back to Rotation
        </a>
    </div>

    <div class="card" style="max-width: 600px;">
        <form method="POST" action="{{ route('admin.rotation.slot.store') }}">
            @csrf

            <div class="form-group">
                <label for="title" class="form-label">Chore Name</label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    class="form-input"
                    value="{{ old('title') }}"
                    placeholder="e.g., Dishes, Trash, Help Cook"
                    required
                    autofocus
                >
                @error('title')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="checkbox-item" style="width: fit-content;">
                    <input 
                        type="checkbox" 
                        name="active" 
                        value="1"
                        {{ old('active', true) ? 'checked' : '' }}
                    >
                    <span>Active</span>
                </label>
                <p class="form-hint">Inactive chores won't be assigned in the rotation.</p>
            </div>

            <div class="flex gap-4 mt-6">
                <button type="submit" class="btn btn-primary">
                    Add Chore
                </button>
                <a href="{{ route('admin.rotation') }}" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
