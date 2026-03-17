@extends('layouts.admin')

@section('title', 'Consequences - Grounding Buddy')

@section('header-title', 'Apply Consequence')

@section('content')
    @if(session('success'))
        <div class="alert alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Adult Script Prompt --}}
    <div class="card mb-4 script-prompt">
        <div class="card-header">
            <h3 class="card-title">Calm Response Guide</h3>
        </div>
        <ol class="script-steps">
            <li>State the rule that was broken, calmly and factually.</li>
            <li>State the consequence that follows.</li>
            <li>Do not argue or negotiate — the rule is the rule.</li>
            <li>Offer a repair path when possible.</li>
            <li>If things escalate, step away and revisit later.</li>
        </ol>
    </div>

    {{-- Structured Consequence Form --}}
    <div class="card mb-6">
        <div class="card-header">
            <h3 class="card-title">Record Consequence</h3>
        </div>

        <form method="POST" action="{{ route('admin.infractions.apply') }}" id="consequenceForm">
            @csrf

            {{-- Step 1: Who --}}
            <div class="form-group">
                <label class="form-label">Who?</label>
                <div class="kid-buttons">
                    @foreach($kids as $kid)
                        <label class="kid-radio">
                            <input type="radio" name="kid_id" value="{{ $kid->id }}" required>
                            <span class="kid-radio-display">
                                <span class="kid-avatar">{{ strtoupper(substr($kid->display_name, 0, 1)) }}</span>
                                <span class="kid-name">{{ $kid->display_name }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('kid_id')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Step 2: What type --}}
            <div class="form-group">
                <label class="form-label">Is this a safety concern?</label>
                <div class="lane-selector">
                    <label class="lane-option lane-ordinary">
                        <input type="radio" name="lane" value="ordinary" checked onchange="toggleLane()">
                        <span class="lane-display">
                            <strong>Ordinary behavior issue</strong>
                            <span class="text-muted">Rule-breaking, chore skipping, disrespect, etc.</span>
                        </span>
                    </label>
                    <label class="lane-option lane-safety">
                        <input type="radio" name="lane" value="safety" onchange="toggleLane()">
                        <span class="lane-display">
                            <strong>Safety / Crisis</strong>
                            <span class="text-muted">Aggression, self-harm risk, severe dysregulation, substance use</span>
                        </span>
                    </label>
                </div>
            </div>

            {{-- Safety notice (shown only for safety lane) --}}
            <div id="safetyNotice" style="display:none;" class="alert alert-attention mb-4">
                <strong>Safety first.</strong> Focus on de-escalation and documentation, not punishment.
                If someone is in immediate danger, call 911, a crisis line, or your child's care team.
            </div>

            {{-- Step 3: What happened --}}
            <div class="form-group">
                <label class="form-label">What happened?</label>
                <div class="infraction-selector">
                    @foreach($defs as $def)
                        <label class="infraction-radio">
                            <input type="radio" name="infraction_def_id" value="{{ $def->id }}" required>
                            <span class="infraction-radio-display">
                                <span class="infraction-label">{{ $def->label }}</span>
                                @if($def->days)
                                    <span class="infraction-detail">{{ $def->days }} day{{ $def->days > 1 ? 's' : '' }}</span>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('infraction_def_id')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Step 4: Factual note --}}
            <div class="form-group">
                <label for="note" class="form-label">Briefly describe what happened</label>
                <textarea name="note" id="note" class="form-input" rows="2" maxlength="300"
                    placeholder="Keep it factual and neutral..."></textarea>
                <p class="form-hint">This is recorded for reference. Keep it short and factual.</p>
            </div>

            {{-- Step 5: Repair path (ordinary lane only) --}}
            <div id="repairSection">
                <div class="form-group">
                    <label class="form-label">Repair / next step for the child</label>
                    <div class="repair-options">
                        <label class="repair-radio">
                            <input type="radio" name="repair" value="redo_task">
                            <span>Redo the task correctly</span>
                        </label>
                        <label class="repair-radio">
                            <input type="radio" name="repair" value="apology">
                            <span>Genuine apology or repair action</span>
                        </label>
                        <label class="repair-radio">
                            <input type="radio" name="repair" value="calm_recheck">
                            <span>Calm recheck later today</span>
                        </label>
                        <label class="repair-radio">
                            <input type="radio" name="repair" value="review_tomorrow" checked>
                            <span>Review at next scheduled check-in</span>
                        </label>
                        <label class="repair-radio">
                            <input type="radio" name="repair" value="none">
                            <span>No repair action needed</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-actions flex gap-3">
                <button type="submit" class="btn btn-primary" id="applyBtn">
                    Apply Consequence
                </button>
            </div>
        </form>
    </div>

    {{-- Recent Events --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Consequences</h3>
            <a href="{{ route('admin.infractions.review') }}" class="btn btn-secondary btn-sm">
                Review Queue
            </a>
        </div>

        @if($events->isEmpty())
            <p class="text-muted text-center p-4">No consequences applied yet.</p>
        @else
            <div class="event-list">
                @foreach($events as $event)
                    <div class="event-item">
                        <div class="event-main">
                            <strong>{{ $event->kid?->display_name }}</strong>
                            <span class="event-infraction">{{ $event->definition?->label }}</span>
                            @if(str_starts_with($event->note ?? '', '[SAFETY]'))
                                <span class="badge badge-safety">Safety</span>
                            @endif
                        </div>
                        <div class="event-meta">
                            <span class="event-time">{{ $event->ts->diffForHumans() }}</span>
                            @if($event->days_applied)
                                <span class="event-days">{{ $event->days_applied }} day{{ $event->days_applied > 1 ? 's' : '' }}</span>
                            @endif
                            <span class="event-strike">Strike {{ $event->strike_before }} → {{ $event->strike_after }}</span>
                        </div>
                        @if($event->note)
                            @php
                                $rawNote = $event->note;
                                $repairText = null;
                                if (preg_match('/\[Repair: (.+?)\]/', $rawNote, $m)) {
                                    $repairText = $m[1];
                                    $rawNote = trim(str_replace($m[0], '', $rawNote));
                                }
                                $rawNote = preg_replace('/^\[SAFETY\]\s*/', '', $rawNote);
                            @endphp
                            @if($rawNote)
                                <div class="event-note">{{ $rawNote }}</div>
                            @endif
                            @if($repairText)
                                <div class="event-repair">Repair: {{ $repairText }}</div>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection

@push('head')
<style>
    /* Script Prompt */
    .script-prompt {
        border-left: 4px solid var(--primary);
        background: var(--primary-light);
    }
    .script-steps {
        padding: 0.5rem 1rem 0.5rem 2rem;
        margin: 0;
    }
    .script-steps li {
        padding: 0.35rem 0;
        color: var(--text-secondary);
    }

    /* Kid Radio Buttons */
    .kid-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .kid-radio input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .kid-radio-display {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1.25rem;
        border: 2px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        background: var(--bg-card);
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 1rem;
    }
    .kid-radio-display:hover {
        border-color: var(--primary);
        background: var(--primary-light);
    }
    .kid-radio input:checked + .kid-radio-display {
        border-color: var(--primary);
        background: var(--primary);
        color: white;
    }
    .kid-radio input:checked + .kid-radio-display .kid-avatar {
        background: rgba(255,255,255,0.3);
    }
    .kid-avatar {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--neutral-200);
        border-radius: 50%;
        font-weight: 700;
        font-size: 0.9rem;
    }
    .kid-name { font-weight: 600; }

    /* Lane Selector */
    .lane-selector {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .lane-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .lane-display {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        padding: 0.75rem 1rem;
        border: 2px solid var(--border-color);
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .lane-ordinary input:checked + .lane-display {
        border-color: var(--primary);
        background: var(--primary-light);
    }
    .lane-safety input:checked + .lane-display {
        border-color: var(--attention);
        background: var(--attention-light);
    }

    /* Alert Attention */
    .alert-attention {
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: var(--border-radius);
        padding: 1rem;
        color: #664d03;
    }

    /* Infraction Selector */
    .infraction-selector {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 0.75rem;
    }
    .infraction-radio input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .infraction-radio-display {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        padding: 1rem;
        border: 2px solid var(--border-color);
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
    }
    .infraction-radio-display:hover {
        border-color: var(--attention);
        background: var(--attention-light);
    }
    .infraction-radio input:checked + .infraction-radio-display {
        border-color: var(--attention);
        background: var(--attention);
        color: white;
    }
    .infraction-label { font-weight: 600; }
    .infraction-detail {
        font-size: 0.8rem;
        opacity: 0.75;
    }

    /* Repair Options */
    .repair-options {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .repair-radio {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
    }

    /* Form elements */
    .form-group { padding: 1rem; border-bottom: 1px solid var(--border-color); }
    .form-group:last-child { border-bottom: none; }
    .form-label { display: block; font-weight: 600; margin-bottom: 0.5rem; }
    .form-input {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        font-size: 1rem;
        font-family: inherit;
    }
    .form-hint { font-size: 0.8rem; color: var(--text-muted); margin: 0.25rem 0 0; }
    .form-error { color: var(--danger); font-size: 0.875rem; margin-top: 0.25rem; }
    .form-actions { padding: 1rem; }

    /* Event List */
    .event-list {
        display: flex;
        flex-direction: column;
    }
    .event-item {
        padding: 1rem;
        border-bottom: 1px solid var(--border-color);
    }
    .event-item:last-child { border-bottom: none; }
    .event-main {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.25rem;
    }
    .event-infraction {
        color: var(--attention);
        font-weight: 500;
    }
    .event-meta {
        display: flex;
        gap: 1rem;
        font-size: 0.875rem;
        color: var(--text-muted);
    }
    .event-days {
        color: var(--attention-dark);
        font-weight: 500;
    }
    .event-note {
        margin-top: 0.5rem;
        padding: 0.5rem;
        background: var(--neutral-100);
        border-radius: var(--border-radius);
        font-size: 0.875rem;
        color: var(--text-secondary);
    }
    .event-repair {
        margin-top: 0.35rem;
        font-size: 0.8rem;
        color: var(--primary-dark);
        font-style: italic;
    }
    .badge-safety {
        display: inline-block;
        padding: 0.15rem 0.5rem;
        background: #dc3545;
        color: white;
        border-radius: 1rem;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
    }
</style>
@endpush

@push('scripts')
<script>
function toggleLane() {
    const isSafety = document.querySelector('input[name="lane"][value="safety"]').checked;
    document.getElementById('safetyNotice').style.display = isSafety ? 'block' : 'none';
    document.getElementById('repairSection').style.display = isSafety ? 'none' : 'block';
}

document.getElementById('consequenceForm').addEventListener('submit', function(e) {
    const kidEl = document.querySelector('input[name="kid_id"]:checked');
    const defEl = document.querySelector('input[name="infraction_def_id"]:checked');
    if (!kidEl || !defEl) return;

    const kidName = kidEl.closest('.kid-radio').querySelector('.kid-name').textContent;
    const defLabel = defEl.closest('.infraction-radio').querySelector('.infraction-label').textContent;

    if (!confirm('Apply "' + defLabel + '" to ' + kidName + '?')) {
        e.preventDefault();
    }
});
</script>
@endpush
