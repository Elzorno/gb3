@extends('layouts.admin')

@section('title', 'Edit Infraction Rule - Grounding Buddy')

@section('header-title', 'Edit Infraction Rule')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.definitions') }}" class="text-muted" style="text-decoration: none;">
            ← Back to Definitions
        </a>
    </div>

    <div class="card" style="max-width: 600px;">
        <form method="POST" action="{{ route('admin.definitions.infraction.update', $infraction) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="label" class="form-label">Infraction Name</label>
                <input 
                    type="text" 
                    id="label" 
                    name="label" 
                    class="form-input"
                    value="{{ old('label', $infraction->label) }}"
                    required
                    autofocus
                >
                @error('label')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="code" class="form-label">Code</label>
                <input 
                    type="text" 
                    id="code" 
                    name="code" 
                    class="form-input"
                    value="{{ old('code', $infraction->code) }}"
                    pattern="[A-Z_]+"
                    title="Uppercase letters and underscores only"
                    style="max-width: 300px;"
                    required
                >
                <p class="form-hint">Short code for internal use</p>
                @error('code')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <h4 class="mt-6 mb-3">Consequence</h4>

            <div class="form-group">
                <label for="mode" class="form-label">Mode</label>
                <div class="radio-group">
                    <label class="radio-item">
                        <input 
                            type="radio" 
                            name="mode" 
                            value="add"
                            {{ old('mode', $infraction->mode) === 'add' ? 'checked' : '' }}
                        >
                        <div>
                            <strong>Add Days</strong>
                            <p class="text-sm text-muted mb-0">Adds days to existing grounding</p>
                        </div>
                    </label>
                    <label class="radio-item">
                        <input 
                            type="radio" 
                            name="mode" 
                            value="set"
                            {{ old('mode', $infraction->mode) === 'set' ? 'checked' : '' }}
                        >
                        <div>
                            <strong>Set Days</strong>
                            <p class="text-sm text-muted mb-0">Sets grounding to exactly this many days</p>
                        </div>
                    </label>
                </div>
                @error('mode')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="days" class="form-label">Days</label>
                <input 
                    type="number" 
                    id="days" 
                    name="days" 
                    class="form-input"
                    value="{{ old('days', $infraction->days) }}"
                    min="0"
                    max="365"
                    style="max-width: 150px;"
                >
                <p class="form-hint">Number of grounding days. 0 = warning only.</p>
                @error('days')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="checkbox-item" style="width: fit-content;">
                    <input 
                        type="checkbox" 
                        name="active" 
                        value="1"
                        {{ old('active', $infraction->active) ? 'checked' : '' }}
                    >
                    <span>Active</span>
                </label>
            </div>

            <div class="flex gap-4 mt-6">
                <button type="submit" class="btn btn-primary">
                    Save Changes
                </button>
                <a href="{{ route('admin.definitions') }}" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    {{-- Delete option --}}
    <div class="card mt-6" style="max-width: 600px; border-color: var(--attention);">
        <h3 class="card-title" style="color: var(--attention-dark);">Delete Infraction</h3>
        <p class="text-muted mb-4">
            This will permanently remove this infraction definition. Historical records will be preserved.
        </p>
        
        <form method="POST" action="{{ route('admin.definitions.infraction.destroy', $infraction) }}" 
              onsubmit="return confirm('Are you sure you want to delete this infraction rule?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-attention">
                Delete "{{ $infraction->label }}"
            </button>
        </form>
    </div>

    <style>
        .radio-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .radio-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.75rem;
            border: 1px solid var(--gray-200);
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: border-color 0.2s, background 0.2s;
        }

        .radio-item:hover {
            border-color: var(--primary-light);
            background: var(--gray-50);
        }
    </style>
@endsection
