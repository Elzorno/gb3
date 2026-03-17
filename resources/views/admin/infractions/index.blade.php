@extends('layouts.admin')

@section('title', 'Infractions - Grounding Buddy')

@section('header-title', 'Apply Consequence')

@section('content')
    @if(session('success'))
        <div class="alert alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Quick Apply Grid --}}
    <div class="card mb-6">
        <div class="card-header">
            <h3 class="card-title">Quick Apply</h3>
            <p class="text-muted mb-0">Select a kid, then tap the consequence to apply it immediately.</p>
        </div>

        <div class="quick-apply-section">
            {{-- Kid Selector --}}
            <div class="kid-selector mb-4">
                <label class="form-label">Who needs a consequence?</label>
                <div class="kid-buttons">
                    @foreach($kids as $kid)
                        <button 
                            type="button" 
                            class="kid-btn" 
                            data-kid-id="{{ $kid->id }}"
                            data-kid-name="{{ $kid->display_name }}"
                        >
                            <span class="kid-avatar">{{ strtoupper(substr($kid->display_name, 0, 1)) }}</span>
                            <span class="kid-name">{{ $kid->display_name }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Infraction Buttons (shown after kid selected) --}}
            <div class="infraction-section" id="infractionSection" style="display: none;">
                <div class="selected-kid-banner" id="selectedKidBanner">
                    Applying consequence to: <strong id="selectedKidName"></strong>
                    <button type="button" class="btn-change" onclick="clearKidSelection()">Change</button>
                </div>

                <label class="form-label">What happened?</label>
                <div class="infraction-grid">
                    @foreach($defs as $def)
                        <form method="POST" action="{{ route('admin.infractions.apply') }}" class="infraction-form">
                            @csrf
                            <input type="hidden" name="kid_id" class="kid-id-input" value="">
                            <input type="hidden" name="infraction_def_id" value="{{ $def->id }}">
                            <button type="submit" class="infraction-btn">
                                <span class="infraction-icon">
                                    @if($def->code === 'VAPING')
                                        🚭
                                    @elseif($def->code === 'VIOLENCE')
                                        ⚠️
                                    @elseif($def->code === 'DISRESPECT')
                                        💢
                                    @elseif($def->code === 'SKIP_CHORES')
                                        🚫
                                    @else
                                        ⚡
                                    @endif
                                </span>
                                <span class="infraction-label">{{ $def->label }}</span>
                                @if($def->days)
                                    <span class="infraction-consequence">{{ $def->days }} day{{ $def->days > 1 ? 's' : '' }}</span>
                                @endif
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        </div>
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
                        </div>
                        <div class="event-meta">
                            <span class="event-time">{{ $event->ts->diffForHumans() }}</span>
                            @if($event->days_applied)
                                <span class="event-days">{{ $event->days_applied }} day{{ $event->days_applied > 1 ? 's' : '' }}</span>
                            @endif
                            <span class="event-strike">Strike {{ $event->strike_before }} → {{ $event->strike_after }}</span>
                        </div>
                        @if($event->note)
                            <div class="event-note">{{ $event->note }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection

@push('head')
<style>
    /* Kid Selector */
    .kid-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .kid-btn {
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

    .kid-btn:hover {
        border-color: var(--primary);
        background: var(--primary-light);
    }

    .kid-btn.selected {
        border-color: var(--primary);
        background: var(--primary);
        color: white;
    }

    .kid-btn.selected .kid-avatar {
        background: rgba(255, 255, 255, 0.3);
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

    .kid-name {
        font-weight: 600;
    }

    /* Selected Kid Banner */
    .selected-kid-banner {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem 1rem;
        background: var(--primary-light);
        border: 1px solid var(--primary);
        border-radius: var(--border-radius);
        margin-bottom: 1rem;
        color: var(--primary-dark);
    }

    .btn-change {
        margin-left: auto;
        padding: 0.25rem 0.75rem;
        background: white;
        border: 1px solid var(--primary);
        border-radius: var(--border-radius);
        color: var(--primary);
        cursor: pointer;
        font-size: 0.875rem;
    }

    .btn-change:hover {
        background: var(--primary);
        color: white;
    }

    /* Infraction Grid */
    .infraction-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .infraction-form {
        display: contents;
    }

    .infraction-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        padding: 1.25rem 1rem;
        border: 2px solid var(--attention);
        border-radius: var(--border-radius-lg);
        background: var(--attention-light);
        cursor: pointer;
        transition: all 0.2s ease;
        min-height: 120px;
        width: 100%;
    }

    .infraction-btn:hover {
        background: var(--attention);
        color: white;
        transform: scale(1.02);
    }

    .infraction-btn:active {
        transform: scale(0.98);
    }

    .infraction-icon {
        font-size: 2rem;
    }

    .infraction-label {
        font-weight: 600;
        font-size: 1rem;
        text-align: center;
    }

    .infraction-consequence {
        font-size: 0.8rem;
        opacity: 0.8;
    }

    /* Event List */
    .event-list {
        display: flex;
        flex-direction: column;
    }

    .event-item {
        padding: 1rem;
        border-bottom: 1px solid var(--border-color);
    }

    .event-item:last-child {
        border-bottom: none;
    }

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
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const kidBtns = document.querySelectorAll('.kid-btn');
    const infractionSection = document.getElementById('infractionSection');
    const selectedKidName = document.getElementById('selectedKidName');
    const kidIdInputs = document.querySelectorAll('.kid-id-input');

    kidBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Deselect all
            kidBtns.forEach(b => b.classList.remove('selected'));
            
            // Select this one
            this.classList.add('selected');
            
            // Update hidden inputs
            const kidId = this.dataset.kidId;
            const kidName = this.dataset.kidName;
            
            kidIdInputs.forEach(input => input.value = kidId);
            selectedKidName.textContent = kidName;
            
            // Show infraction section
            infractionSection.style.display = 'block';
            
            // Scroll to infraction section
            infractionSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    // Confirm before submitting
    document.querySelectorAll('.infraction-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const kidName = selectedKidName.textContent;
            const infractionLabel = this.querySelector('.infraction-label').textContent;
            
            if (!confirm(`Apply "${infractionLabel}" to ${kidName}?`)) {
                e.preventDefault();
            }
        });
    });
});

function clearKidSelection() {
    document.querySelectorAll('.kid-btn').forEach(b => b.classList.remove('selected'));
    document.getElementById('infractionSection').style.display = 'none';
    document.querySelectorAll('.kid-id-input').forEach(input => input.value = '');
}
</script>
@endpush
