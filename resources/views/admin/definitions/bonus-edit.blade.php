@extends('layouts.admin')

@section('title', 'Edit Bonus Task - Grounding Buddy')

@section('header-title', 'Edit Bonus Task')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.definitions') }}" class="text-muted" style="text-decoration: none;">
            ← Back to Definitions
        </a>
    </div>

    <div class="card" style="max-width: 600px;">
        <form method="POST" action="{{ route('admin.definitions.bonus.update', $bonus) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="title" class="form-label">Bonus Name</label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    class="form-input"
                    value="{{ old('title', $bonus->title) }}"
                    required
                    autofocus
                >
                @error('title')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <h4 class="mt-6 mb-3">Rewards</h4>
            <p class="text-muted mb-4">What does the child earn for completing this bonus?</p>

            <div class="form-row">
                <div class="form-group">
                    <label for="reward_cents" class="form-label">💵 Money (cents)</label>
                    <input 
                        type="number" 
                        id="reward_cents" 
                        name="reward_cents" 
                        class="form-input"
                        value="{{ old('reward_cents', $bonus->reward_cents) }}"
                        min="0"
                        step="25"
                    >
                    <p class="form-hint">e.g., 100 = $1.00</p>
                </div>

                <div class="form-group">
                    <label for="reward_phone_min" class="form-label">📱 Phone Time (min)</label>
                    <input 
                        type="number" 
                        id="reward_phone_min" 
                        name="reward_phone_min" 
                        class="form-input"
                        value="{{ old('reward_phone_min', $bonus->reward_phone_min) }}"
                        min="0"
                        step="5"
                    >
                </div>

                <div class="form-group">
                    <label for="reward_games_min" class="form-label">🎮 Game Time (min)</label>
                    <input 
                        type="number" 
                        id="reward_games_min" 
                        name="reward_games_min" 
                        class="form-input"
                        value="{{ old('reward_games_min', $bonus->reward_games_min) }}"
                        min="0"
                        step="5"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="max_per_week" class="form-label">Max Per Week</label>
                <select name="max_per_week" id="max_per_week" class="form-input" style="max-width: 200px;">
                    @for($i = 1; $i <= 7; $i++)
                        <option value="{{ $i }}" {{ old('max_per_week', $bonus->max_per_week) == $i ? 'selected' : '' }}>
                            {{ $i }} time{{ $i > 1 ? 's' : '' }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="form-group">
                <label class="checkbox-item" style="width: fit-content;">
                    <input 
                        type="checkbox" 
                        name="active" 
                        value="1"
                        {{ old('active', $bonus->active) ? 'checked' : '' }}
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
        <h3 class="card-title" style="color: var(--attention-dark);">Delete Bonus</h3>
        <p class="text-muted mb-4">
            This will permanently remove this bonus definition. Historical data will be preserved.
        </p>
        
        <form method="POST" action="{{ route('admin.definitions.bonus.destroy', $bonus) }}" 
              onsubmit="return confirm('Are you sure you want to delete this bonus?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-attention">
                Delete "{{ $bonus->title }}"
            </button>
        </form>
    </div>

    <style>
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }
    </style>
@endsection
