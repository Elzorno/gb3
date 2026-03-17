@extends('layouts.admin')

@section('title', 'Family - Grounding Buddy')

@section('header-title', 'Manage Family')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="mt-0 mb-0">Kids</h2>
        <a href="{{ route('admin.family.create') }}" class="btn btn-primary">
            + Add Kid
        </a>
    </div>

    @if($kids->isEmpty())
        <div class="card text-center">
            <div style="padding: var(--space-8);">
                <div style="font-size: 3rem; margin-bottom: var(--space-4);">👨‍👩‍👧‍👦</div>
                <h3>No kids added yet</h3>
                <p class="text-muted mb-4">Add your first child to get started with Grounding Buddy.</p>
                <a href="{{ route('admin.family.create') }}" class="btn btn-primary btn-lg">
                    Add Your First Kid
                </a>
            </div>
        </div>
    @else
        <div class="card">
            <form method="POST" action="{{ route('admin.family.reorder') }}" id="reorder-form">
                @csrf
                <div class="kid-list" id="kid-list">
                    @foreach($kids as $kid)
                        @php
                            $priv = $privileges[$kid->id] ?? null;
                            $isLocked = $priv && ($priv->phone_locked || $priv->games_locked || $priv->other_locked);
                            $locks = [];
                            if ($priv) {
                                if ($priv->phone_locked) $locks[] = 'Phone';
                                if ($priv->games_locked) $locks[] = 'Games';
                                if ($priv->other_locked) $locks[] = 'Other';
                            }
                        @endphp
                        <div class="kid-row" data-id="{{ $kid->id }}">
                            <input type="hidden" name="order[]" value="{{ $kid->id }}">
                            <div class="kid-drag-handle" title="Drag to reorder">⋮⋮</div>
                            <div class="kid-avatar">{{ strtoupper(substr($kid->display_name, 0, 1)) }}</div>
                            <div class="kid-info">
                                <div class="kid-name">{{ $kid->display_name }}</div>
                                <div class="kid-status">
                                    @if($isLocked)
                                        <span class="badge badge-attention">{{ implode(', ', $locks) }} Locked</span>
                                    @else
                                        <span class="badge badge-success">Good Standing</span>
                                    @endif
                                </div>
                            </div>
                            <div class="kid-actions">
                                <a href="{{ route('admin.family.edit', $kid) }}" class="btn btn-secondary btn-sm">
                                    Edit
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="reorder-actions hidden" id="reorder-actions">
                    <button type="submit" class="btn btn-primary">Save Order</button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.reload()">Cancel</button>
                </div>
            </form>
        </div>

        <div class="card mt-6">
            <h3 class="card-title">Quick Stats</h3>
            <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));">
                <div class="stat-card">
                    <div class="stat-value">{{ $kids->count() }}</div>
                    <div class="stat-label">Total Kids</div>
                </div>
                <div class="stat-card">
                    @php
                        $lockedCount = 0;
                        foreach ($privileges as $p) {
                            if ($p->phone_locked || $p->games_locked || $p->other_locked) {
                                $lockedCount++;
                            }
                        }
                    @endphp
                    <div class="stat-value" style="color: {{ $lockedCount > 0 ? 'var(--attention)' : 'var(--success)' }}">
                        {{ $lockedCount }}
                    </div>
                    <div class="stat-label">On Consequence</div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('head')
<style>
    .kid-list {
        display: flex;
        flex-direction: column;
        gap: var(--space-2);
    }
    
    .kid-row {
        display: flex;
        align-items: center;
        gap: var(--space-4);
        padding: var(--space-4);
        background: var(--neutral-50);
        border-radius: var(--border-radius);
        transition: all var(--transition-fast);
    }
    
    .kid-row:hover {
        background: var(--neutral-100);
    }
    
    .kid-row.dragging {
        opacity: 0.5;
        background: var(--primary-light);
    }
    
    .kid-drag-handle {
        cursor: grab;
        color: var(--text-muted);
        font-size: 1.25rem;
        padding: var(--space-2);
        user-select: none;
    }
    
    .kid-drag-handle:active {
        cursor: grabbing;
    }
    
    .kid-avatar {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--secondary);
        color: white;
        border-radius: 50%;
        font-weight: 700;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    
    .kid-info {
        flex: 1;
        min-width: 0;
    }
    
    .kid-name {
        font-weight: 600;
        font-size: 1.125rem;
    }
    
    .kid-status {
        margin-top: var(--space-1);
    }
    
    .kid-actions {
        flex-shrink: 0;
    }
    
    .btn-sm {
        min-height: 36px;
        padding: var(--space-2) var(--space-4);
        font-size: 0.875rem;
    }
    
    .reorder-actions {
        margin-top: var(--space-4);
        padding-top: var(--space-4);
        border-top: 1px solid var(--border-color);
        display: flex;
        gap: var(--space-3);
    }
    
    .reorder-actions.hidden {
        display: none;
    }
</style>
@endpush

@push('scripts')
<script>
    // Simple drag-and-drop reordering
    document.addEventListener('DOMContentLoaded', function() {
        const list = document.getElementById('kid-list');
        const form = document.getElementById('reorder-form');
        const actions = document.getElementById('reorder-actions');
        let draggedItem = null;
        let originalOrder = [];
        
        // Store original order
        list.querySelectorAll('.kid-row').forEach(row => {
            originalOrder.push(row.dataset.id);
        });
        
        list.querySelectorAll('.kid-row').forEach(row => {
            const handle = row.querySelector('.kid-drag-handle');
            
            handle.addEventListener('mousedown', function(e) {
                draggedItem = row;
                row.classList.add('dragging');
                
                document.addEventListener('mousemove', onMouseMove);
                document.addEventListener('mouseup', onMouseUp);
            });
        });
        
        function onMouseMove(e) {
            if (!draggedItem) return;
            
            const rows = [...list.querySelectorAll('.kid-row:not(.dragging)')];
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
            
            // Check if order changed
            const newOrder = [];
            list.querySelectorAll('.kid-row').forEach(row => {
                newOrder.push(row.dataset.id);
            });
            
            const orderChanged = JSON.stringify(originalOrder) !== JSON.stringify(newOrder);
            actions.classList.toggle('hidden', !orderChanged);
            
            // Update hidden inputs
            const inputs = form.querySelectorAll('input[name="order[]"]');
            inputs.forEach((input, i) => {
                input.value = newOrder[i];
            });
        }
    });
</script>
@endpush
