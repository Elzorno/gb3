@extends('layouts.admin')

@section('title', 'Add Infraction Rule - Grounding Buddy')

@section('header-title', 'Add Infraction Rule')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.definitions') }}" class="text-muted" style="text-decoration: none;">
            ← Back to Definitions
        </a>
    </div>

    <div class="card" style="max-width: 600px;">
        <form method="POST" action="{{ route('admin.definitions.infraction.store') }}">
            @csrf

            <div class="form-group">
                <label for="label" class="form-label">Infraction Name</label>
                <input 
                    type="text" 
                    id="label" 
                    name="label" 
                    class="form-input"
                    value="{{ old('label') }}"
                    placeholder="e.g., Disrespect to family member"
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
                    value="{{ old('code') }}"
                    placeholder="e.g., DISRESPECT"
                    pattern="[A-Z_]+"
                    title="Uppercase letters and underscores only"
                    style="max-width: 300px;"
                    required
                >
                <p class="form-hint">Short code for internal use (UPPERCASE_WITH_UNDERSCORES)</p>
                @error('code')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <h4 class="mt-6 mb-3">Consequence</h4>
            <p class="text-muted mb-4">How does this infraction affect grounding?</p>

            <div class="form-group">
                <label for="mode" class="form-label">Mode</label>
                <div class="radio-group">
                    <label class="radio-item">
                        <input 
                            type="radio" 
                            name="mode" 
                            value="add"
                            {{ old('mode', 'add') === 'add' ? 'checked' : '' }}
                        >
                        <div>
                            <strong>Add Days</strong>
                            <p class="text-sm text-muted mb-0">Adds days to existing grounding (stacks)</p>
                        </div>
                    </label>
                    <label class="radio-item">
                        <input 
                            type="radio" 
                            name="mode" 
                            value="set"
                            {{ old('mode') === 'set' ? 'checked' : '' }}
                        >
                        <div>
                            <strong>Set Days</strong>
                            <p class="text-sm text-muted mb-0">Sets grounding to exactly this many days (doesn't stack)</p>
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
                    value="{{ old('days', 1) }}"
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
                        {{ old('active', true) ? 'checked' : '' }}
                    >
                    <span>Active</span>
                </label>
                <p class="form-hint">Inactive infractions can't be recorded against kids.</p>
            </div>

            <div class="flex gap-4 mt-6">
                <button type="submit" class="btn btn-primary">
                    Create Infraction
                </button>
                <a href="{{ route('admin.definitions') }}" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
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

        .radio-item input[type="radio"]:checked + div {
            color: var(--primary);
        }

        .radio-item input[type="radio"]:checked ~ * {
            color: var(--primary);
        }
    </style>

    <script>
        // Auto-generate code from label
        document.getElementById('label').addEventListener('input', function() {
            const codeInput = document.getElementById('code');
            if (!codeInput.dataset.edited) {
                codeInput.value = this.value
                    .toUpperCase()
                    .replace(/[^A-Z0-9]+/g, '_')
                    .replace(/^_|_$/g, '');
            }
        });

        // Mark code as edited if user types in it
        document.getElementById('code').addEventListener('input', function() {
            this.dataset.edited = 'true';
        });
    </script>
@endsection
