@extends('layouts.admin')

@section('title', 'Add Bonus Task - Grounding Buddy')

@section('header-title', 'Add Bonus Task')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.definitions') }}" class="text-muted" style="text-decoration: none;">
            ← Back to Definitions
        </a>
    </div>

    <div class="card" style="max-width: 600px;">
        <form method="POST" action="{{ route('admin.definitions.bonus.store') }}">
            @csrf

            <div class="form-group">
                <label for="title" class="form-label">Bonus Name</label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    class="form-input"
                    value="{{ old('title') }}"
                    placeholder="e.g., Help with laundry"
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
                        value="{{ old('reward_cents', 0) }}"
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
                        value="{{ old('reward_phone_min', 0) }}"
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
                        value="{{ old('reward_games_min', 0) }}"
                        min="0"
                        step="5"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="max_per_week" class="form-label">Max Per Week</label>
                <select name="max_per_week" id="max_per_week" class="form-input" style="max-width: 200px;">
                    @for($i = 1; $i <= 7; $i++)
                        <option value="{{ $i }}" {{ old('max_per_week', 1) == $i ? 'selected' : '' }}>
                            {{ $i }} time{{ $i > 1 ? 's' : '' }}
                        </option>
                    @endfor
                </select>
                <p class="form-hint">How many times can this bonus be claimed per week?</p>
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
                <p class="form-hint">Inactive bonuses won't be available to kids.</p>
            </div>

            <div class="flex gap-4 mt-6">
                <button type="submit" class="btn btn-primary">
                    Create Bonus
                </button>
                <a href="{{ route('admin.definitions') }}" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
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
