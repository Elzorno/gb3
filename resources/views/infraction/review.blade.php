@extends('layouts.admin')

@section('title', 'Consequence Review - Grounding Buddy')

@section('header-title', 'Consequence Review')

@section('content')
    @if(session('status'))
        <div class="alert alert-success mb-4">
            {{ session('status') }}
        </div>
    @endif

    {{-- Due Now --}}
    <div class="card mb-4" @if($dueNow->isNotEmpty()) style="border-left: 4px solid var(--attention);" @endif>
        <div class="card-header">
            <h3 class="card-title">Due Now</h3>
            @if($dueNow->isNotEmpty())
                <span class="badge badge-attention">{{ $dueNow->count() }} due</span>
            @endif
        </div>

        @forelse($dueNow as $e)
            <div class="review-event-item mb-4 p-4" style="background: var(--neutral-50); border-radius: var(--border-radius);">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <strong>{{ $e->kid?->display_name }}</strong>
                        <span style="color: var(--attention);">{{ $e->definition?->label }}</span>
                    </div>
                    <span class="text-muted text-sm">Review due {{ \Carbon\Carbon::parse($e->review_on)->format('M j') }}</span>
                </div>
                <div class="text-muted text-sm mb-3">
                    Applied {{ $e->ts->diffForHumans() }} &middot;
                    {{ $e->days_applied }} day{{ $e->days_applied > 1 ? 's' : '' }} &middot;
                    Strike {{ $e->strike_after }}
                </div>

                <form method="POST" action="{{ route('admin.infractions.review.decide') }}">
                    @csrf
                    <input type="hidden" name="event_id" value="{{ $e->id }}">

                    <div class="flex flex-wrap gap-4 mb-3">
                        <div class="form-group mb-0" style="min-width: 160px;">
                            <label class="form-label">What to do</label>
                            <select name="action" class="form-input">
                                <option value="review_only">Note only (keep locks)</option>
                                <option value="unlock">Unlock all</option>
                                <option value="shorten">Shorten remaining time</option>
                            </select>
                        </div>

                        <div class="form-group mb-0" style="min-width: 140px;">
                            <label class="form-label">Keep (minutes)</label>
                            <input type="number" name="keep_minutes" class="form-input" min="0" max="10080" value="240" placeholder="Only for shorten">
                        </div>

                        <div class="form-group mb-0" style="min-width: 200px;">
                            <label class="form-label">Note</label>
                            <input type="text" name="review_note" class="form-input" maxlength="400" placeholder="Optional review note">
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="reset_strike" value="1">
                            <span>Reset strike count</span>
                        </label>

                        <button type="submit" class="btn btn-primary">
                            Save Review
                        </button>
                    </div>
                </form>
            </div>
        @empty
            <p class="text-muted text-center p-4 mb-0">No reviews due right now.</p>
        @endforelse
    </div>

    {{-- Upcoming --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Upcoming (Next 7 Days)</h3>
        </div>

        @forelse($upcoming as $e)
            <div class="flex justify-between items-center p-3" style="border-bottom: 1px solid var(--border-color);">
                <div>
                    <strong>{{ $e->kid?->display_name }}</strong>
                    <span class="text-muted">&middot;</span>
                    <span>{{ $e->definition?->label }}</span>
                </div>
                <span class="text-muted text-sm">{{ \Carbon\Carbon::parse($e->review_on)->format('M j, Y') }}</span>
            </div>
        @empty
            <p class="text-muted text-center p-4 mb-0">No upcoming reviews.</p>
        @endforelse
    </div>
@endsection
