@extends('layouts.admin')

@section('title', 'Chores & Rotation - Grounding Buddy')

@section('header-title', 'Chores & Rotation')

@section('content')
    {{-- Weekly Preview --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">This Week's Schedule</h3>
            <span class="text-muted">Week of {{ $today->startOfWeek()->format('M j') }}</span>
        </div>

        @if(empty($preview))
            <div class="alert alert-info">
                No rotation rule configured yet. Set up the rotation below to see the weekly schedule.
            </div>
        @else
            <div class="rotation-grid">
                @foreach($preview as $day)
                    <div class="rotation-day {{ $day['isToday'] ? 'today' : '' }}">
                        <div class="rotation-day-header">
                            <span class="day-name">{{ substr($day['dayName'], 0, 3) }}</span>
                            <span class="day-date">{{ $day['date']->format('M j') }}</span>
                            @if($day['isToday'])
                                <span class="badge badge-success">Today</span>
                            @endif
                        </div>
                        <div class="rotation-day-assignments">
                            @forelse($day['assignments'] as $assignment)
                                <div class="assignment-chip">
                                    <span class="assignment-kid">{{ $assignment['kid']->display_name }}</span>
                                    <span class="assignment-slot">{{ $assignment['slot']->title }}</span>
                                </div>
                            @empty
                                <span class="text-muted">No assignments</span>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="grid-2-col">
        {{-- Chore Slots Management --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Chore Slots</h3>
                <a href="{{ route('admin.rotation.slot.create') }}" class="btn btn-primary btn-sm">
                    + Add Chore
                </a>
            </div>

            @if($slots->isEmpty())
                <div class="text-center p-4">
                    <p class="text-muted">No chores defined yet.</p>
                </div>
            @else
                <form method="POST" action="{{ route('admin.rotation.slots.reorder') }}" id="slots-reorder-form">
                    @csrf
                    <div class="slot-list" id="slot-list">
                        @foreach($slots as $slot)
                            <div class="slot-row {{ !$slot->active ? 'inactive' : '' }}" data-id="{{ $slot->id }}">
                                <input type="hidden" name="order[]" value="{{ $slot->id }}">
                                <div class="slot-drag">⋮⋮</div>
                                <div class="slot-info">
                                    <span class="slot-title">{{ $slot->title }}</span>
                                    @if(!$slot->active)
                                        <span class="badge badge-neutral">Inactive</span>
                                    @endif
                                </div>
                                <div class="slot-actions">
                                    <a href="{{ route('admin.rotation.slot.edit', $slot) }}" class="action-btn" title="Edit">
                                        ✎
                                    </a>
                                    <form method="POST" action="{{ route('admin.rotation.slot.toggle', $slot) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="action-btn" title="{{ $slot->active ? 'Deactivate' : 'Activate' }}">
                                            {{ $slot->active ? '○' : '●' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="reorder-actions hidden" id="slots-reorder-actions">
                        <button type="submit" class="btn btn-primary btn-sm">Save Order</button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="window.location.reload()">Cancel</button>
                    </div>
                </form>
            @endif
        </div>

        {{-- Rotation Configuration --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Rotation Configuration</h3>
            </div>

            <form method="POST" action="{{ route('admin.rotation.rule.update') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label">Kids in Rotation</label>
                    <p class="form-hint mb-2">Check the kids who participate in the chore rotation.</p>
                    <div class="checkbox-list">
                        @php
                            $selectedKids = $rule ? json_decode($rule->kids_json, true) ?: [] : [];
                            // Handle legacy format (names) vs new format (IDs)
                            $firstKid = $selectedKids[0] ?? null;
                            $legacyFormat = $firstKid && !is_numeric($firstKid);
                        @endphp
                        @foreach($kids as $kid)
                            <label class="checkbox-item">
                                <input 
                                    type="checkbox" 
                                    name="kids[]" 
                                    value="{{ $kid->id }}"
                                    {{ ($legacyFormat ? in_array($kid->display_name, $selectedKids) : in_array($kid->id, $selectedKids)) ? 'checked' : '' }}
                                >
                                <span>{{ $kid->display_name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('kids')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Chores in Rotation</label>
                    <p class="form-hint mb-2">Check the chores to include in the rotation.</p>
                    <div class="checkbox-list">
                        @php
                            $selectedSlots = $rule ? json_decode($rule->slots_json, true) ?: [] : [];
                            // Handle legacy format (titles) vs new format (IDs)
                            $firstSlot = $selectedSlots[0] ?? null;
                            $legacySlotFormat = $firstSlot && !is_numeric($firstSlot);
                        @endphp
                        @foreach($slots->where('active', true) as $slot)
                            <label class="checkbox-item">
                                <input 
                                    type="checkbox" 
                                    name="slots[]" 
                                    value="{{ $slot->id }}"
                                    {{ ($legacySlotFormat ? in_array($slot->title, $selectedSlots) : in_array($slot->id, $selectedSlots)) ? 'checked' : '' }}
                                >
                                <span>{{ $slot->title }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('slots')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="anchor_monday" class="form-label">Anchor Monday</label>
                    <input 
                        type="date" 
                        id="anchor_monday" 
                        name="anchor_monday" 
                        class="form-input"
                        value="{{ $rule?->anchor_monday?->format('Y-m-d') ?? now()->startOfWeek()->format('Y-m-d') }}"
                        style="max-width: 200px;"
                    >
                    <p class="form-hint">
                        This is the reference point for calculating rotation. The rotation shifts each day and week from this date.
                    </p>
                    @error('anchor_monday')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    Save Rotation
                </button>
            </form>
        </div>
    </div>
@endsection

@push('head')
<style>
    .grid-2-col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--space-6);
    }
    
    @media (max-width: 1024px) {
        .grid-2-col {
            grid-template-columns: 1fr;
        }
    }
    
    /* Weekly rotation grid */
    .rotation-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: var(--space-3);
    }
    
    @media (max-width: 768px) {
        .rotation-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .rotation-day {
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        overflow: hidden;
    }
    
    .rotation-day.today {
        border-color: var(--primary);
        box-shadow: 0 0 0 2px var(--primary-light);
    }
    
    .rotation-day-header {
        background: var(--neutral-100);
        padding: var(--space-2) var(--space-3);
        display: flex;
        align-items: center;
        gap: var(--space-2);
        flex-wrap: wrap;
    }
    
    .rotation-day.today .rotation-day-header {
        background: var(--primary-light);
    }
    
    .day-name {
        font-weight: 600;
    }
    
    .day-date {
        color: var(--text-muted);
        font-size: 0.875rem;
    }
    
    .rotation-day-assignments {
        padding: var(--space-3);
        display: flex;
        flex-direction: column;
        gap: var(--space-2);
        min-height: 80px;
    }
    
    .assignment-chip {
        background: var(--secondary-light);
        border-radius: var(--border-radius);
        padding: var(--space-2);
        font-size: 0.875rem;
    }
    
    .assignment-kid {
        font-weight: 600;
        display: block;
    }
    
    .assignment-slot {
        color: var(--text-secondary);
        font-size: 0.75rem;
    }
    
    /* Slot list */
    .slot-list {
        display: flex;
        flex-direction: column;
        gap: var(--space-2);
    }
    
    .slot-row {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        padding: var(--space-3);
        background: var(--neutral-50);
        border-radius: var(--border-radius);
    }
    
    .slot-row.inactive {
        opacity: 0.6;
    }
    
    .slot-row.dragging {
        opacity: 0.5;
        background: var(--primary-light);
    }
    
    .slot-drag {
        cursor: grab;
        color: var(--text-muted);
        user-select: none;
    }
    
    .slot-info {
        flex: 1;
        display: flex;
        align-items: center;
        gap: var(--space-2);
    }
    
    .slot-title {
        font-weight: 500;
    }
    
    .slot-actions {
        display: flex;
        gap: var(--space-1);
    }
    
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: var(--border-radius);
        background: var(--neutral-100);
        border: none;
        cursor: pointer;
        color: var(--text-secondary);
        text-decoration: none;
        transition: all var(--transition-fast);
    }
    
    .action-btn:hover {
        background: var(--primary-light);
        color: var(--primary);
    }
    
    /* Checkbox list */
    .checkbox-list {
        display: flex;
        flex-direction: column;
        gap: var(--space-2);
    }
    
    .checkbox-item {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        padding: var(--space-2) var(--space-3);
        background: var(--neutral-50);
        border-radius: var(--border-radius);
        cursor: pointer;
    }
    
    .checkbox-item:hover {
        background: var(--neutral-100);
    }
    
    .checkbox-item input[type="checkbox"] {
        width: 20px;
        height: 20px;
        accent-color: var(--primary);
    }
    
    .btn-sm {
        min-height: 36px;
        padding: var(--space-2) var(--space-4);
        font-size: 0.875rem;
    }
    
    .reorder-actions {
        margin-top: var(--space-4);
        display: flex;
        gap: var(--space-2);
    }
    
    .reorder-actions.hidden {
        display: none;
    }
</style>
@endpush

@push('scripts')
<script>
    // Drag and drop for slot reordering
    document.addEventListener('DOMContentLoaded', function() {
        const list = document.getElementById('slot-list');
        const form = document.getElementById('slots-reorder-form');
        const actions = document.getElementById('slots-reorder-actions');
        
        if (!list) return;
        
        let draggedItem = null;
        let originalOrder = [];
        
        list.querySelectorAll('.slot-row').forEach(row => {
            originalOrder.push(row.dataset.id);
        });
        
        list.querySelectorAll('.slot-row').forEach(row => {
            const handle = row.querySelector('.slot-drag');
            
            handle.addEventListener('mousedown', function(e) {
                draggedItem = row;
                row.classList.add('dragging');
                
                document.addEventListener('mousemove', onMouseMove);
                document.addEventListener('mouseup', onMouseUp);
            });
        });
        
        function onMouseMove(e) {
            if (!draggedItem) return;
            
            const rows = [...list.querySelectorAll('.slot-row:not(.dragging)')];
            const afterElement = rows.find(row => {
                const rect = row.getBoundingClientRect();
                return e.clientY < rect.top + rect.height / 2;
            });
            
            if (afterElement) {
                list.insertBefore(draggedItem, afterElement);
            } else {
                list.appendChild(draggedItem);
            }
        }
        
        function onMouseUp() {
            if (!draggedItem) return;
            
            draggedItem.classList.remove('dragging');
            draggedItem = null;
            
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
            
            const newOrder = [];
            list.querySelectorAll('.slot-row').forEach(row => {
                newOrder.push(row.dataset.id);
            });
            
            const orderChanged = JSON.stringify(originalOrder) !== JSON.stringify(newOrder);
            actions.classList.toggle('hidden', !orderChanged);
            
            const inputs = form.querySelectorAll('input[name="order[]"]');
            inputs.forEach((input, i) => {
                input.value = newOrder[i];
            });
        }
    });
</script>
@endpush
