@extends('layouts.kid')

@section('title', 'Submit Proof - Grounding Buddy')

@section('header-title')
    Submit Proof
@endsection

@section('content')
    @if (session('status'))
        <div class="alert alert-success mb-4">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error mb-4">
            <strong>Oops!</strong> Please fix the following:
            <ul class="mt-2 mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Task Selection --}}
    @if (!$selectedSlot)
        <div class="card mb-4">
            <h3 class="card-title">Which task are you submitting?</h3>
            
            @if($assignments->isEmpty())
                <div class="encouragement">
                    <span class="encouragement-emoji">✨</span>
                    <p class="mb-0">All tasks are done or waiting for review!</p>
                </div>
                <a href="{{ route('app.today') }}" class="btn btn-secondary mt-3">
                    ← Back to Today
                </a>
            @else
                <div class="task-selector">
                    @foreach($assignments as $a)
                        <a href="{{ route('app.submit', ['slot' => $a->slot_id]) }}" 
                           class="task-option {{ $a->status === 'rejected' ? 'task-option-redo' : '' }}">
                            <div class="task-option-icon">
                                @if($a->status === 'rejected')
                                    ↩
                                @else
                                    📋
                                @endif
                            </div>
                            <div class="task-option-content">
                                <div class="task-option-title">{{ $a->slot?->title ?? 'Task' }}</div>
                                @if($a->status === 'rejected')
                                    <div class="task-option-badge badge-attention">Try again</div>
                                @else
                                    <div class="task-option-badge">Tap to submit</div>
                                @endif
                            </div>
                            <div class="task-option-arrow">→</div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @else
        {{-- Submission Form --}}
        <div class="card mb-4">
            <h3 class="card-title">{{ $selectedSlot->title }}</h3>
            
            @if($selectedSlot->description)
                <p class="text-muted mb-3">{{ $selectedSlot->description }}</p>
            @endif
            
            @if($selectedAssignment?->status === 'rejected')
                <div class="status-banner status-banner-attention mb-3">
                    <div class="status-banner-icon">↩</div>
                    <div class="status-banner-content">
                        <div class="status-banner-title">Try Again</div>
                        <p class="status-banner-text mb-0">
                            Complete this task and submit a new photo when ready.
                        </p>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('app.submit.store') }}" enctype="multipart/form-data" id="submitForm">
                @csrf
                <input type="hidden" name="slot_id" value="{{ $selectedSlot->id }}">
                <input type="hidden" name="day" value="{{ $today->format('Y-m-d') }}">

                {{-- Photo upload area --}}
                <div class="photo-upload-container mb-4">
                    <label for="photo" class="photo-upload-area" id="photoUploadArea">
                        <input 
                            type="file" 
                            id="photo" 
                            name="photo" 
                            accept="image/*"
                            capture="environment"
                            class="photo-upload-input"
                            required
                        >
                        
                        <div class="photo-upload-prompt" id="photoPrompt">
                            <div class="photo-upload-icon">📸</div>
                            <div class="photo-upload-text">Tap to take a photo</div>
                            <div class="photo-upload-hint">or choose from gallery</div>
                        </div>
                        
                        <div class="photo-preview-container" id="photoPreview" style="display: none;">
                            <img src="" alt="Preview" class="photo-preview-img" id="previewImg">
                            <div class="photo-preview-overlay">
                                <span>Tap to change</span>
                            </div>
                        </div>
                    </label>
                </div>

                {{-- Tips --}}
                <div class="tips-box mb-4">
                    <h4 class="tips-title">📝 Tips for a good photo:</h4>
                    <ul class="tips-list">
                        <li>Make sure the area is well lit</li>
                        <li>Show the completed work clearly</li>
                        <li>Include the whole area in the photo</li>
                    </ul>
                </div>

                {{-- Action Buttons --}}
                <div class="submit-actions">
                    <button type="submit" class="btn btn-primary btn-large" id="submitBtn" disabled>
                        <span class="btn-icon">✓</span>
                        Submit for Review
                    </button>
                    <a href="{{ route('app.submit') }}" class="btn btn-secondary">
                        ← Choose Different Task
                    </a>
                </div>
            </form>
        </div>
    @endif
@endsection

@push('styles')
<style>
    /* Task Selector */
    .task-selector {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .task-option {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
    }
    
    .task-option:hover, .task-option:focus {
        border-color: #3b82f6;
        background: #eff6ff;
    }
    
    .task-option-redo {
        border-color: #fbbf24;
        background: #fefce8;
    }
    
    .task-option-icon {
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        background: white;
        border-radius: 10px;
        flex-shrink: 0;
    }
    
    .task-option-content {
        flex: 1;
        min-width: 0;
    }
    
    .task-option-title {
        font-weight: 600;
        font-size: 1.1rem;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }
    
    .task-option-badge {
        font-size: 0.85rem;
        color: #64748b;
    }
    
    .badge-attention {
        color: #b45309;
        font-weight: 500;
    }
    
    .task-option-arrow {
        font-size: 1.25rem;
        color: #94a3b8;
    }
    
    /* Photo Upload */
    .photo-upload-container {
        width: 100%;
    }
    
    .photo-upload-area {
        display: block;
        width: 100%;
        min-height: 200px;
        border: 3px dashed #cbd5e1;
        border-radius: 16px;
        cursor: pointer;
        overflow: hidden;
        position: relative;
        transition: all 0.2s ease;
        background: #f8fafc;
    }
    
    .photo-upload-area:hover, .photo-upload-area:focus-within {
        border-color: #3b82f6;
        background: #eff6ff;
    }
    
    .photo-upload-input {
        position: absolute;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        top: 0;
        left: 0;
    }
    
    .photo-upload-prompt {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        text-align: center;
        min-height: 200px;
    }
    
    .photo-upload-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }
    
    .photo-upload-text {
        font-size: 1.2rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }
    
    .photo-upload-hint {
        font-size: 0.9rem;
        color: #64748b;
    }
    
    .photo-preview-container {
        position: relative;
        width: 100%;
        min-height: 200px;
    }
    
    .photo-preview-img {
        width: 100%;
        max-height: 400px;
        object-fit: contain;
        display: block;
    }
    
    .photo-preview-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(transparent, rgba(0,0,0,0.7));
        color: white;
        padding: 1.5rem 1rem 1rem;
        text-align: center;
        font-size: 0.9rem;
    }
    
    /* Tips Box */
    .tips-box {
        background: #fef3c7;
        border: 1px solid #fbbf24;
        border-radius: 12px;
        padding: 1rem;
    }
    
    .tips-title {
        font-size: 1rem;
        font-weight: 600;
        color: #92400e;
        margin: 0 0 0.75rem 0;
    }
    
    .tips-list {
        margin: 0;
        padding-left: 1.25rem;
        color: #78350f;
    }
    
    .tips-list li {
        margin-bottom: 0.25rem;
    }
    
    .tips-list li:last-child {
        margin-bottom: 0;
    }
    
    /* Submit Actions */
    .submit-actions {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .btn-large {
        padding: 1rem 1.5rem;
        font-size: 1.1rem;
        justify-content: center;
    }
    
    .btn-icon {
        margin-right: 0.5rem;
    }
    
    .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    /* Alerts */
    .alert {
        padding: 1rem;
        border-radius: 12px;
    }
    
    .alert-success {
        background: #dcfce7;
        border: 1px solid #86efac;
        color: #166534;
    }
    
    .alert-error {
        background: #fef3c7;
        border: 1px solid #fbbf24;
        color: #92400e;
    }
    
    .alert ul {
        padding-left: 1.25rem;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const photoInput = document.getElementById('photo');
    const photoPrompt = document.getElementById('photoPrompt');
    const photoPreview = document.getElementById('photoPreview');
    const previewImg = document.getElementById('previewImg');
    const submitBtn = document.getElementById('submitBtn');
    
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    alert('Please select an image file');
                    photoInput.value = '';
                    return;
                }
                
                // Validate file size (10MB max)
                if (file.size > 10 * 1024 * 1024) {
                    alert('Image is too large. Please choose a smaller image (max 10MB)');
                    photoInput.value = '';
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(event) {
                    previewImg.src = event.target.result;
                    photoPrompt.style.display = 'none';
                    photoPreview.style.display = 'block';
                    submitBtn.disabled = false;
                };
                reader.readAsDataURL(file);
            } else {
                // No file selected
                photoPrompt.style.display = 'flex';
                photoPreview.style.display = 'none';
                submitBtn.disabled = true;
            }
        });
    }
    
    // Prevent double submission
    const submitForm = document.getElementById('submitForm');
    if (submitForm) {
        submitForm.addEventListener('submit', function(e) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';
        });
    }
});
</script>
@endpush
