@extends('layouts.admin')

@section('title', 'Edit Chore - Grounding Buddy')

@section('header-title', 'Edit Chore Slot')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.rotation') }}" class="text-muted" style="text-decoration: none;">
            ← Back to Rotation
        </a>
    </div>

    <div class="card" style="max-width: 600px;">
        <form method="POST" action="{{ route('admin.rotation.slot.update', $slot) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="title" class="form-label">Chore Name</label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    class="form-input"
                    value="{{ old('title', $slot->title) }}"
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
                        {{ old('active', $slot->active) ? 'checked' : '' }}
                    >
                    <span>Active</span>
                </label>
                <p class="form-hint">Inactive chores won't be assigned in the rotation.</p>
            </div>

            <div class="flex gap-4 mt-6">
                <button type="submit" class="btn btn-primary">
                    Save Changes
                </button>
                <a href="{{ route('admin.rotation') }}" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    {{-- Delete option --}}
    <div class="card mt-6" style="max-width: 600px; border-color: var(--attention);">
        <h3 class="card-title" style="color: var(--attention-dark);">Delete Chore</h3>
        <p class="text-muted mb-4">
            This will remove the chore from the rotation. Historical assignment data will be preserved.
        </p>
        
        <form method="POST" action="{{ route('admin.rotation.slot.destroy', $slot) }}" 
              onsubmit="return confirm('Are you sure you want to delete this chore?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-attention">
                Delete "{{ $slot->title }}"
            </button>
        </form>
    </div>
@endsection
