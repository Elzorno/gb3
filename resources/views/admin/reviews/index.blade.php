@extends('layouts.admin')

@section('title', 'Review Submissions - Grounding Buddy')

@section('header-title', 'Review Submissions')

@section('header-subtitle')
    @if($pendingCount > 0)
        <span class="badge badge-warning">{{ $pendingCount }} pending</span>
    @else
        <span class="badge badge-success">All caught up!</span>
    @endif
@endsection

@section('content')
    {{-- Filter bar --}}
    <div class="card mb-4">
        <form method="GET" action="{{ route('admin.reviews') }}" class="flex flex-wrap gap-4 items-end">
            <div class="form-group mb-0" style="min-width: 150px;">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-input">
                    <option value="">All</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <div class="form-group mb-0" style="min-width: 150px;">
                <label for="kind" class="form-label">Type</label>
                <select name="kind" id="kind" class="form-input">
                    <option value="">All Types</option>
                    <option value="base" {{ $kind === 'base' ? 'selected' : '' }}>Chore</option>
                    <option value="bonus" {{ $kind === 'bonus' ? 'selected' : '' }}>Bonus</option>
                </select>
            </div>

            <div class="form-group mb-0" style="min-width: 150px;">
                <label for="kid_id" class="form-label">Kid</label>
                <select name="kid_id" id="kid_id" class="form-input">
                    <option value="">All Kids</option>
                    @foreach($kids as $kid)
                        <option value="{{ $kid->id }}" {{ $kidId === $kid->id ? 'selected' : '' }}>
                            {{ $kid->display_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-secondary">
                Filter
            </button>

            @if($status !== 'pending' || $kind !== '' || $kidId !== 0)
                <a href="{{ route('admin.reviews') }}" class="btn btn-link">
                    Clear Filters
                </a>
            @endif
        </form>
    </div>

    {{-- Submissions list --}}
    @if($rows->isEmpty())
        <div class="card text-center py-6">
            <p class="text-muted mb-0">No submissions found matching your filters.</p>
        </div>
    @else
        <div class="review-list">
            @foreach($rows as $submission)
                <div class="card review-card mb-3" data-submission-id="{{ $submission->id }}">
                    <div class="review-header flex justify-between items-start mb-3">
                        <div>
                            <span class="badge {{ $submission->kind === 'bonus' ? 'badge-info' : '' }}">
                                {{ $submission->kind === 'bonus' ? 'Bonus' : 'Chore' }}
                            </span>
                            <span class="badge 
                                {{ $submission->status === 'pending' ? 'badge-warning' : '' }}
                                {{ $submission->status === 'approved' ? 'badge-success' : '' }}
                                {{ $submission->status === 'rejected' ? 'badge-attention' : '' }}
                            ">
                                {{ ucfirst($submission->status) }}
                            </span>
                        </div>
                        <span class="text-muted text-sm">
                            {{ $submission->submitted_at?->diffForHumans() ?? 'Unknown time' }}
                        </span>
                    </div>

                    <div class="review-content flex gap-4">
                        {{-- Proof image --}}
                        @if($submission->proof_path)
                            <div class="review-proof" style="flex: 0 0 150px;">
                                <a href="{{ asset('storage/' . $submission->proof_path) }}" target="_blank">
                                    <img 
                                        src="{{ asset('storage/' . $submission->proof_path) }}" 
                                        alt="Proof photo"
                                        style="width: 150px; height: 150px; object-fit: cover; border-radius: var(--border-radius);"
                                    >
                                </a>
                            </div>
                        @else
                            <div class="review-proof" style="flex: 0 0 150px;">
                                <div style="width: 150px; height: 150px; background: var(--gray-100); border-radius: var(--border-radius); display: flex; align-items: center; justify-content: center;">
                                    <span class="text-muted">No photo</span>
                                </div>
                            </div>
                        @endif

                        {{-- Details --}}
                        <div class="review-details flex-1">
                            <h3 class="card-title mb-1">
                                {{ $submission->kid?->display_name ?? 'Unknown' }}
                            </h3>
                            <p class="text-muted mb-2">
                                @if($submission->kind === 'base' && $submission->slot)
                                    {{ $submission->slot->title }}
                                @elseif($submission->kind === 'bonus')
                                    Bonus task
                                @else
                                    Submission
                                @endif
                                &bull; {{ $submission->day?->format('M j, Y') ?? '' }}
                            </p>

                            @if($submission->status !== 'pending' && $submission->review_note)
                                <div class="review-note mt-2 p-2" style="background: var(--gray-100); border-radius: var(--border-radius);">
                                    <strong>Note:</strong> {{ $submission->review_note }}
                                </div>
                            @endif
                        </div>

                        {{-- Action buttons (for pending only) --}}
                        @if($submission->status === 'pending')
                            <div class="review-actions flex flex-col gap-2" style="flex: 0 0 auto;">
                                <form method="POST" action="{{ route('admin.reviews.decide') }}" class="mb-0">
                                    @csrf
                                    <input type="hidden" name="submission_id" value="{{ $submission->id }}">
                                    <input type="hidden" name="decision" value="approved">
                                    <button type="submit" class="btn btn-success btn-sm w-full">
                                        Approve
                                    </button>
                                </form>
                                
                                <button 
                                    type="button" 
                                    class="btn btn-attention btn-sm"
                                    onclick="showRejectModal({{ $submission->id }}, '{{ addslashes($submission->kid?->display_name ?? 'Unknown') }}')"
                                >
                                    Reject
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $rows->links() }}
        </div>
    @endif

    {{-- Reject Modal --}}
    <div id="reject-modal" class="modal" style="display: none;">
        <div class="modal-backdrop" onclick="hideRejectModal()"></div>
        <div class="modal-content card">
            <h3 class="card-title">Reject Submission</h3>
            <p class="text-muted mb-3">
                Rejecting submission from <strong id="reject-kid-name"></strong>
            </p>
            
            <form method="POST" action="{{ route('admin.reviews.decide') }}" id="reject-form">
                @csrf
                <input type="hidden" name="submission_id" id="reject-submission-id" value="">
                <input type="hidden" name="decision" value="rejected">
                
                <div class="form-group">
                    <label for="reject-note" class="form-label">Note (optional)</label>
                    <textarea 
                        name="note" 
                        id="reject-note" 
                        class="form-input"
                        rows="3"
                        placeholder="Explain why this was rejected..."
                    ></textarea>
                    <p class="form-hint">This note will be visible to the child.</p>
                </div>

                <div class="flex gap-3 justify-end">
                    <button type="button" class="btn btn-secondary" onclick="hideRejectModal()">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-attention">
                        Reject Submission
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .review-card {
            transition: box-shadow 0.2s ease;
        }
        .review-card:hover {
            box-shadow: var(--shadow-md);
        }
        
        .modal {
            position: fixed;
            inset: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            position: relative;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        .badge-info {
            background: var(--primary-light);
            color: var(--primary-dark);
        }
    </style>

    <script>
        function showRejectModal(submissionId, kidName) {
            document.getElementById('reject-submission-id').value = submissionId;
            document.getElementById('reject-kid-name').textContent = kidName;
            document.getElementById('reject-note').value = '';
            document.getElementById('reject-modal').style.display = 'flex';
        }

        function hideRejectModal() {
            document.getElementById('reject-modal').style.display = 'none';
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideRejectModal();
            }
        });
    </script>
@endsection
