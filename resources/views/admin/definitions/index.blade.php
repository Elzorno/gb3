@extends('layouts.admin')

@section('title', 'Definitions - Grounding Buddy')

@section('header-title', 'Definitions')

@section('header-subtitle')
    Manage bonus tasks and infraction rules
@endsection

@section('content')
    {{-- Tab navigation --}}
    <div class="tabs mb-4">
        <button class="tab-btn active" onclick="showTab('bonuses')">
            Bonus Tasks
            <span class="badge">{{ $bonuses->count() }}</span>
        </button>
        <button class="tab-btn" onclick="showTab('infractions')">
            Infractions
            <span class="badge">{{ $infractions->count() }}</span>
        </button>
    </div>

    {{-- Bonuses Tab --}}
    <div id="tab-bonuses" class="tab-content">
        <div class="flex justify-between items-center mb-4">
            <p class="text-muted mb-0">
                Bonus tasks kids can do for extra rewards like screen time or money.
            </p>
            <a href="{{ route('admin.definitions.bonus.create') }}" class="btn btn-primary">
                + Add Bonus
            </a>
        </div>

        @if($bonuses->isEmpty())
            <div class="card text-center py-6">
                <p class="text-muted mb-3">No bonus definitions yet.</p>
                <a href="{{ route('admin.definitions.bonus.create') }}" class="btn btn-primary">
                    Create First Bonus
                </a>
            </div>
        @else
            <div class="definition-list" id="bonus-list">
                @foreach($bonuses as $bonus)
                    <div class="card definition-card {{ !$bonus->active ? 'inactive' : '' }}" 
                         data-id="{{ $bonus->id }}">
                        <div class="definition-drag-handle">⋮⋮</div>
                        
                        <div class="definition-content flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h4 class="card-title mb-0">{{ $bonus->title }}</h4>
                                @if(!$bonus->active)
                                    <span class="badge badge-muted">Inactive</span>
                                @endif
                            </div>
                            
                            <div class="definition-rewards flex gap-3 text-sm text-muted">
                                @if($bonus->reward_cents > 0)
                                    <span>💵 ${{ number_format($bonus->reward_cents / 100, 2) }}</span>
                                @endif
                                @if($bonus->reward_phone_min > 0)
                                    <span>📱 +{{ $bonus->reward_phone_min }}min</span>
                                @endif
                                @if($bonus->reward_games_min > 0)
                                    <span>🎮 +{{ $bonus->reward_games_min }}min</span>
                                @endif
                                @if($bonus->reward_cents == 0 && $bonus->reward_phone_min == 0 && $bonus->reward_games_min == 0)
                                    <span class="text-muted">No rewards configured</span>
                                @endif
                                <span class="ms-auto">Max: {{ $bonus->max_per_week }}/week</span>
                            </div>
                        </div>

                        <div class="definition-actions flex gap-2">
                            <form method="POST" action="{{ route('admin.definitions.bonus.toggle', $bonus) }}" class="mb-0">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $bonus->active ? 'btn-secondary' : 'btn-success' }}">
                                    {{ $bonus->active ? 'Disable' : 'Enable' }}
                                </button>
                            </form>
                            <a href="{{ route('admin.definitions.bonus.edit', $bonus) }}" class="btn btn-secondary btn-sm">
                                Edit
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Infractions Tab --}}
    <div id="tab-infractions" class="tab-content" style="display: none;">
        <div class="flex justify-between items-center mb-4">
            <p class="text-muted mb-0">
                Rule violations that result in grounding or other consequences.
            </p>
            <a href="{{ route('admin.definitions.infraction.create') }}" class="btn btn-primary">
                + Add Infraction
            </a>
        </div>

        @if($infractions->isEmpty())
            <div class="card text-center py-6">
                <p class="text-muted mb-3">No infraction definitions yet.</p>
                <a href="{{ route('admin.definitions.infraction.create') }}" class="btn btn-primary">
                    Create First Infraction
                </a>
            </div>
        @else
            <div class="definition-list" id="infraction-list">
                @foreach($infractions as $infraction)
                    <div class="card definition-card {{ !$infraction->active ? 'inactive' : '' }}" 
                         data-id="{{ $infraction->id }}">
                        <div class="definition-drag-handle">⋮⋮</div>
                        
                        <div class="definition-content flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h4 class="card-title mb-0">{{ $infraction->label }}</h4>
                                <code class="text-muted text-sm">{{ $infraction->code }}</code>
                                @if(!$infraction->active)
                                    <span class="badge badge-muted">Inactive</span>
                                @endif
                            </div>
                            
                            <div class="definition-consequence text-sm text-muted">
                                @if($infraction->mode === 'add')
                                    <span class="badge badge-attention-light">+{{ $infraction->days }} days</span>
                                    Adds to existing grounding
                                @else
                                    <span class="badge badge-attention-light">{{ $infraction->days }} days</span>
                                    Sets grounding duration
                                @endif
                            </div>
                        </div>

                        <div class="definition-actions flex gap-2">
                            <form method="POST" action="{{ route('admin.definitions.infraction.toggle', $infraction) }}" class="mb-0">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $infraction->active ? 'btn-secondary' : 'btn-success' }}">
                                    {{ $infraction->active ? 'Disable' : 'Enable' }}
                                </button>
                            </form>
                            <a href="{{ route('admin.definitions.infraction.edit', $infraction) }}" class="btn btn-secondary btn-sm">
                                Edit
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <style>
        .tabs {
            display: flex;
            gap: 0.5rem;
            border-bottom: 2px solid var(--gray-200);
            padding-bottom: 0;
        }

        .tab-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            background: none;
            font-weight: 500;
            color: var(--gray-600);
            cursor: pointer;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            transition: color 0.2s, border-color 0.2s;
        }

        .tab-btn:hover {
            color: var(--primary);
        }

        .tab-btn.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-btn .badge {
            margin-left: 0.5rem;
            font-size: 0.75rem;
        }

        .definition-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .definition-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: opacity 0.2s, box-shadow 0.2s;
        }

        .definition-card.inactive {
            opacity: 0.6;
        }

        .definition-card:hover {
            box-shadow: var(--shadow-md);
        }

        .definition-drag-handle {
            cursor: grab;
            padding: 0.5rem;
            color: var(--gray-400);
            font-weight: bold;
            user-select: none;
        }

        .definition-drag-handle:active {
            cursor: grabbing;
        }

        .definition-content {
            min-width: 0;
        }

        .definition-rewards,
        .definition-consequence {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        .badge-muted {
            background: var(--gray-200);
            color: var(--gray-600);
        }

        .badge-attention-light {
            background: color-mix(in srgb, var(--attention) 15%, white);
            color: var(--attention-dark);
        }

        code {
            padding: 0.125rem 0.375rem;
            background: var(--gray-100);
            border-radius: 0.25rem;
        }
    </style>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.style.display = 'none';
            });
            
            // Deactivate all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById('tab-' + tabName).style.display = 'block';
            
            // Activate clicked button
            event.currentTarget.classList.add('active');
        }

        // Simple drag-and-drop reorder (bonus list)
        function initDragReorder(listId, reorderUrl) {
            const list = document.getElementById(listId);
            if (!list) return;

            let draggedItem = null;

            list.querySelectorAll('.definition-card').forEach(item => {
                const handle = item.querySelector('.definition-drag-handle');
                
                handle.addEventListener('mousedown', () => {
                    item.draggable = true;
                });

                item.addEventListener('dragstart', (e) => {
                    draggedItem = item;
                    item.style.opacity = '0.5';
                });

                item.addEventListener('dragend', () => {
                    item.style.opacity = '';
                    item.draggable = false;
                    draggedItem = null;
                    
                    // Submit new order
                    const order = Array.from(list.querySelectorAll('.definition-card'))
                        .map(card => card.dataset.id);
                    
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = reorderUrl;
                    form.innerHTML = `
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        ${order.map((id, i) => `<input type="hidden" name="order[]" value="${id}">`).join('')}
                    `;
                    document.body.appendChild(form);
                    form.submit();
                });

                item.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    const afterElement = getDragAfterElement(list, e.clientY);
                    if (afterElement == null) {
                        list.appendChild(draggedItem);
                    } else {
                        list.insertBefore(draggedItem, afterElement);
                    }
                });
            });
        }

        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('.definition-card:not([style*="opacity: 0.5"])')];
            
            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        // Initialize drag-and-drop
        initDragReorder('bonus-list', '{{ route("admin.definitions.bonuses.reorder") }}');
        initDragReorder('infraction-list', '{{ route("admin.definitions.infractions.reorder") }}');
    </script>
@endsection
