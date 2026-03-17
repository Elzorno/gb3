@extends('layouts.admin')

@section('title', 'Add Kid - Grounding Buddy')

@section('header-title', 'Add New Kid')

@section('content')
    <div class="card" style="max-width: 600px;">
        <form method="POST" action="{{ route('admin.family.store') }}">
            @csrf

            <div class="form-group">
                <label for="display_name" class="form-label">Name</label>
                <input 
                    type="text" 
                    id="display_name" 
                    name="display_name" 
                    class="form-input"
                    value="{{ old('display_name') }}"
                    placeholder="Enter child's name"
                    required
                    autofocus
                >
                <p class="form-hint">This is how they'll be displayed in the app.</p>
                @error('display_name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="pin" class="form-label">Login PIN</label>
                <input 
                    type="text" 
                    id="pin" 
                    name="pin" 
                    class="form-input"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="6"
                    placeholder="6-digit PIN"
                    required
                    autocomplete="off"
                    style="font-family: monospace; letter-spacing: 0.5em; max-width: 200px;"
                >
                <p class="form-hint">
                    A 6-digit number for logging into the kid app. 
                    Choose something they can remember but others won't guess.
                </p>
                @error('pin')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-4 mt-6">
                <button type="submit" class="btn btn-primary">
                    Add Kid
                </button>
                <a href="{{ route('admin.family') }}" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    // Only allow digits in PIN field
    document.getElementById('pin').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
</script>
@endpush
