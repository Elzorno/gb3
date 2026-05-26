@extends('layouts.admin')

@section('title', 'Reviews - Grounding Buddy')

@section('header-title', 'Reviews')

@section('header-subtitle')
    @if($pendingWorkCount > 0)
        <span class="badge badge-warning">{{ $pendingWorkCount }} pending</span>
    @else
        <span class="badge badge-success">All caught up!</span>
    @endif
@endsection

@section('content')
    @if($pendingPayoutCount > 0)
        <div class="card mb-4" style="border-left: 4px solid var(--secondary);">
            <div class="card-header">
                <h3 class="card-title">Payout Requests Waiting</h3>
                <span class="badge badge-info">{{ $pendingPayoutCount }} pending</span>
            </div>
            <div class="review-list">
                @foreach($pendingPayouts as $payout)
                    <div class="card review-card payout-review-card mb-3" data-payout-id="{{ $payout->id }}">
                        <div class="review-header flex justify-between items-start mb-3">
                            <div>
                                <span class="badge badge-info">Payout</span>
                                <span class="badge badge-warning">Pending</span>
                            </div>
                            <span class="text-muted text-sm">
                                {{ $payout->requested_at?->diffForHumans() ?? 'Unknown time' }}
                            </span>
                        </div>

                        <div class="review-content flex gap-4">
                            <div class="review-proof payout-review-summary">
                                <div class="payout-review-icon">💵</div>
                            </div>

                            <div class="review-details flex-1">
                                <h3 class="card-title mb-1">
                                    {{ $payout->kid?->display_name ?? 'Unknown' }}
                                </h3>
                                <p class="text-muted mb-2">Requested payout from earned bank</p>
                                <div class="payout-amounts">
                                    @if($payout->requested_cents > 0)
                                        <span class="badge badge-info">${{ number_format($payout->requested_cents / 100, 2) }}</span>
                                    @endif
                                    @if($payout->requested_phone_min > 0)
                                        <span class="badge badge-info">{{ $payout->requested_phone_min }}m phone</span>
                                    @endif
                                    @if($payout->requested_games_min > 0)
                                        <span class="badge badge-info">{{ $payout->requested_games_min }}m games</span>
                                    @endif
                                    @if($payout->requested_other_min > 0)
                                        <span class="badge badge-info">{{ $payout->requested_other_min }}m other</span>
                                    @endif
                                </div>
                            </div>

                            <div class="review-actions flex flex-col gap-2" style="flex: 0 0 auto;">
                                <form method="POST" action="{{ route('admin.payouts.decide') }}" class="mb-0">
                                    @csrf
                                    <input type="hidden" name="payout_id" value="{{ $payout->id }}">
                                    <input type="hidden" name="decision" value="approved">
                                    <button type="submit" class="btn btn-success btn-sm w-full">
                                        Approve
                                    </button>
                                </form>

                                <button
                                    type="button"
                                    class="btn btn-attention btn-sm"
                                    onclick="showPayoutDenyModal({{ $payout->id }}, '{{ addslashes($payout->kid?->display_name ?? 'Unknown') }}')"
                                >
                                    Deny
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Filter bar --}}
    <div class="card mb-4">
        <form method="GET" action="{{ route('admin.reviews') }}" class="review-filter-form flex flex-wrap gap-4 items-end">
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
                                <a href="{{ route('admin.submissions.proof', $submission) }}" target="_blank" rel="noreferrer">
                                    <img 
                                        src="{{ route('admin.submissions.proof', $submission) }}" 
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

                            @if($submission->status !== 'pending' && ($submission->admin_note || $submission->kid_note || $submission->review_note))
                                <div class="review-note mt-2 p-2" style="background: var(--gray-100); border-radius: var(--border-radius);">
                                    @if($submission->kid_note)
                                        <div><strong>Kid note:</strong> {{ $submission->kid_note }}</div>
                                    @elseif($submission->review_note)
                                        <div><strong>Kid note:</strong> {{ $submission->review_note }}</div>
                                    @endif
                                    @if($submission->admin_note)
                                        <div class="mt-1"><strong>Internal note:</strong> {{ $submission->admin_note }}</div>
                                    @endif
                                </div>
                            @endif
                            @if($submission->reviewed_at)
                                <div class="text-muted mt-1" style="font-size: 0.8rem;">
                                    Reviewed {{ $submission->reviewed_at->diffForHumans() }}
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
                        @elseif($submission->reviewed_at && $submission->reviewed_at->diffInMinutes(now()) <= 5)
                            <div class="review-actions flex flex-col gap-2" style="flex: 0 0 auto;">
                                <form method="POST" action="{{ route('admin.reviews.undo') }}" class="mb-0">
                                    @csrf
                                    <input type="hidden" name="submission_id" value="{{ $submission->id }}">
                                    <button type="submit" class="btn btn-secondary btn-sm" title="Revert to pending ({{ 5 - (int)$submission->reviewed_at->diffInMinutes(now()) }}m left)">
                                        Undo
                                    </button>
                                </form>
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

                <div class="form-group mb-3">
                    <label class="form-label">Kid-facing quick reason (tap one):</label>
                    <div class="reject-templates">
                        <button type="button" class="reject-tpl" data-text="Photo was unclear or blurry — please retake and resubmit.">Photo unclear</button>
                        <button type="button" class="reject-tpl" data-text="Looks like one more step is needed — please finish up.">Needs one more step</button>
                        <button type="button" class="reject-tpl" data-text="Please redo this task and resubmit when it's complete.">Please redo</button>
                        <button type="button" class="reject-tpl" data-text="This doesn't match what was assigned — double-check and resubmit.">Wrong task</button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="reject-kid-note" class="form-label">Kid-facing note</label>
                    <textarea 
                        name="kid_note" 
                        id="reject-kid-note" 
                        class="form-input"
                        rows="3"
                        placeholder="Keep it neutral and clear..."
                    ></textarea>
                    <p class="form-hint">This note will be visible to the child. Keep it neutral and encouraging.</p>
                </div>

                <div class="form-group">
                    <label for="reject-admin-note" class="form-label">Internal note</label>
                    <textarea
                        name="admin_note"
                        id="reject-admin-note"
                        class="form-input"
                        rows="2"
                        placeholder="Optional note for caregiver/admin context only..."
                    ></textarea>
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

    <div id="payout-deny-modal" class="modal" style="display: none;">
        <div class="modal-backdrop" onclick="hidePayoutDenyModal()"></div>
        <div class="modal-content card">
            <h3 class="card-title">Deny Payout Request</h3>
            <p class="text-muted mb-3">
                Denying payout request from <strong id="payout-deny-kid-name"></strong>
            </p>

            <form method="POST" action="{{ route('admin.payouts.decide') }}">
                @csrf
                <input type="hidden" name="payout_id" id="payout-deny-id" value="">
                <input type="hidden" name="decision" value="denied">

                <div class="form-group">
                    <label for="payout-deny-note" class="form-label">Reason</label>
                    <textarea
                        name="note"
                        id="payout-deny-note"
                        class="form-input"
                        rows="3"
                        placeholder="Optional note for this payout request..."
                    ></textarea>
                </div>

                <div class="flex gap-3 justify-end">
                    <button type="button" class="btn btn-secondary" onclick="hidePayoutDenyModal()">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-attention">
                        Deny Request
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

        .review-content {
            align-items: flex-start;
        }

        .payout-review-card {
            border-left: 4px solid var(--secondary);
        }

        .payout-review-summary {
            flex: 0 0 150px;
            min-height: 150px;
            border-radius: var(--border-radius);
            background: color-mix(in srgb, var(--secondary) 10%, white);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .payout-review-icon {
            font-size: 3rem;
        }

        .payout-amounts {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
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

        .reject-templates {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .reject-tpl {
            padding: 0.35rem 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            background: var(--bg-card);
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.15s ease;
        }
        .reject-tpl:hover {
            border-color: var(--primary);
            background: var(--primary-light);
        }
        .reject-tpl.active {
            border-color: var(--attention);
            background: var(--attention-light);
        }

        @media (max-width: 820px) {
            .review-filter-form {
                display: grid;
                grid-template-columns: 1fr;
            }

            .review-filter-form .form-group {
                min-width: 0 !important;
            }

            .review-content {
                flex-direction: column;
            }

            .review-proof,
            .payout-review-summary {
                width: 100%;
                flex: none !important;
                min-height: 120px;
            }

            .review-proof img,
            .review-proof > div {
                width: 100% !important;
                max-width: none;
                height: min(60vw, 220px) !important;
            }

            .review-actions {
                width: 100%;
                flex-direction: row !important;
                flex-wrap: wrap;
            }

            .review-actions form,
            .review-actions .btn {
                flex: 1 1 12rem;
            }

            .review-actions .btn,
            .review-actions form .btn {
                width: 100%;
            }
        }

        @media (max-width: 520px) {
            .modal-content {
                width: calc(100% - 1.5rem);
            }

            .review-actions {
                flex-direction: column !important;
            }
        }
    </style>

    <script>
        function showRejectModal(submissionId, kidName) {
            document.getElementById('reject-submission-id').value = submissionId;
            document.getElementById('reject-kid-name').textContent = kidName;
            document.getElementById('reject-kid-note').value = '';
            document.getElementById('reject-admin-note').value = '';
            document.querySelectorAll('.reject-tpl').forEach(b => b.classList.remove('active'));
            document.getElementById('reject-modal').style.display = 'flex';
        }

        function hideRejectModal() {
            document.getElementById('reject-modal').style.display = 'none';
        }

        function showPayoutDenyModal(payoutId, kidName) {
            document.getElementById('payout-deny-id').value = payoutId;
            document.getElementById('payout-deny-kid-name').textContent = kidName;
            document.getElementById('payout-deny-note').value = '';
            document.getElementById('payout-deny-modal').style.display = 'flex';
        }

        function hidePayoutDenyModal() {
            document.getElementById('payout-deny-modal').style.display = 'none';
        }

        // Wire up reject template buttons
        document.querySelectorAll('.reject-tpl').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.reject-tpl').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                document.getElementById('reject-kid-note').value = this.dataset.text;
            });
        });

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideRejectModal();
                hidePayoutDenyModal();
            }
        });
    </script>
@endsection
