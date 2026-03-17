@extends('layouts.base')

@section('body-class', 'kid-layout has-bottom-nav')

@section('body')
<header class="header" style="background-color: var(--secondary);">
    <div class="header-inner">
        <div class="flex items-center gap-4">
            @if(session('kid_name'))
                <span class="header-kid-avatar" aria-hidden="true">
                    {{ strtoupper(substr(session('kid_name'), 0, 1)) }}
                </span>
            @endif
            <h1 class="header-title">
                @hasSection('header-title')
                    @yield('header-title')
                @else
                    Hi, {{ session('kid_name', 'there') }}!
                @endif
            </h1>
        </div>
        
        <a href="{{ route('app.logout') }}" class="header-link" onclick="event.preventDefault(); document.getElementById('kid-logout-form').submit();">
            Switch Kid
        </a>
    </div>
</header>

<form id="kid-logout-form" action="{{ route('app.logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<main class="main" id="main-content" role="main">
    <div class="container container-narrow">
        @yield('content')
    </div>
</main>

{{-- Bottom navigation for kids - big, easy-to-tap targets --}}
<nav class="bottom-nav" aria-label="Main navigation">
    <a href="{{ route('app.today') }}" class="bottom-nav-item @if(request()->routeIs('app.today')) active @endif">
        <span class="bottom-nav-icon">📋</span>
        <span>Today</span>
    </a>
    <a href="{{ route('app.rules') }}" class="bottom-nav-item @if(request()->routeIs('app.rules')) active @endif">
        <span class="bottom-nav-icon">✓</span>
        <span>Rules</span>
    </a>
    <a href="{{ route('app.bonuses') }}" class="bottom-nav-item @if(request()->routeIs('app.bonuses')) active @endif">
        <span class="bottom-nav-icon">⭐</span>
        <span>Bonuses</span>
    </a>
    <a href="{{ route('app.history') }}" class="bottom-nav-item @if(request()->routeIs('app.history')) active @endif">
        <span class="bottom-nav-icon">📊</span>
        <span>History</span>
    </a>
</nav>
@endsection

@push('head')
<style>
    /* Kid-specific styles - larger, friendlier, more colorful */
    .kid-layout {
        --bg-header: var(--secondary);
    }
    
    /* Kid avatar initial */
    .header-kid-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        background-color: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        font-weight: 700;
        font-size: 1rem;
    }
    
    /* Larger, friendlier buttons for kids */
    .kid-layout .btn {
        min-height: 56px;
        font-size: 1.125rem;
    }
    
    /* Today's checklist styling */
    .checklist {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .checklist-item {
        display: flex;
        align-items: center;
        gap: var(--space-4);
        padding: var(--space-4);
        background: var(--bg-card);
        border-radius: var(--border-radius-lg);
        margin-bottom: var(--space-3);
        box-shadow: var(--shadow-sm);
        transition: transform var(--transition-fast), box-shadow var(--transition-fast);
    }
    
    .checklist-item:active {
        transform: scale(0.98);
    }
    
    .checklist-checkbox {
        flex-shrink: 0;
        width: 32px;
        height: 32px;
        border: 3px solid var(--border-color);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all var(--transition-fast);
    }
    
    .checklist-item.completed .checklist-checkbox {
        background-color: var(--success);
        border-color: var(--success);
        color: white;
    }
    
    .checklist-item.completed .checklist-checkbox::after {
        content: '✓';
        font-size: 1.25rem;
        font-weight: 700;
    }
    
    .checklist-content {
        flex: 1;
    }
    
    .checklist-title {
        font-weight: 600;
        font-size: 1.125rem;
        color: var(--text-primary);
    }
    
    .checklist-item.completed .checklist-title {
        text-decoration: line-through;
        color: var(--text-muted);
    }
    
    .checklist-meta {
        font-size: 0.875rem;
        color: var(--text-muted);
        margin-top: var(--space-1);
    }
    
    /* Big action button for submitting proof */
    .big-action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 80px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        border: none;
        border-radius: var(--border-radius-lg);
        font-size: 1.25rem;
        font-weight: 600;
        cursor: pointer;
        box-shadow: var(--shadow-md);
        transition: transform var(--transition-fast), box-shadow var(--transition-fast);
    }
    
    .big-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }
    
    .big-action-btn:active {
        transform: translateY(0);
    }
    
    .big-action-btn-sub {
        font-size: 0.875rem;
        font-weight: 400;
        opacity: 0.9;
        margin-top: var(--space-1);
    }
    
    /* Progress ring for daily completion */
    .progress-ring {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin: var(--space-6) auto;
    }
    
    .progress-ring-bg {
        fill: none;
        stroke: var(--neutral-200);
        stroke-width: 12;
    }
    
    .progress-ring-progress {
        fill: none;
        stroke: var(--success);
        stroke-width: 12;
        stroke-linecap: round;
        transition: stroke-dashoffset 0.5s ease;
    }
    
    .progress-ring-text {
        font-size: 2rem;
        font-weight: 700;
        fill: var(--text-primary);
    }
    
    .progress-ring-label {
        font-size: 0.875rem;
        fill: var(--text-muted);
    }
    
    /* Bonus cards */
    .bonus-card {
        background: linear-gradient(135deg, var(--attention-light), white);
        border: 2px solid var(--attention);
        border-radius: var(--border-radius-lg);
        padding: var(--space-5);
        text-align: center;
        margin-bottom: var(--space-4);
    }
    
    .bonus-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--attention-dark);
        margin-bottom: var(--space-2);
    }
    
    .bonus-points {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--attention);
    }
    
    .bonus-claimed {
        opacity: 0.6;
        border-style: dashed;
    }
    
    /* Encouragement messages */
    .encouragement {
        text-align: center;
        padding: var(--space-6);
        font-size: 1.125rem;
        color: var(--text-secondary);
    }
    
    .encouragement-emoji {
        font-size: 3rem;
        display: block;
        margin-bottom: var(--space-3);
    }
    
    /* History ledger */
    .ledger-entry {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: var(--space-3) var(--space-4);
        border-bottom: 1px solid var(--border-color);
    }
    
    .ledger-entry:last-child {
        border-bottom: none;
    }
    
    .ledger-date {
        font-size: 0.875rem;
        color: var(--text-muted);
    }
    
    .ledger-amount {
        font-weight: 700;
        font-size: 1.25rem;
    }
    
    .ledger-amount.positive {
        color: var(--success);
    }
    
    .ledger-amount.negative {
        color: var(--attention);
    }
    
    /* Status messages shown on today page */
    .status-banner {
        background-color: var(--attention-light);
        border-left: 4px solid var(--attention);
        padding: var(--space-4);
        border-radius: 0 var(--border-radius) var(--border-radius) 0;
        margin-bottom: var(--space-4);
    }
    
    .status-banner-grounded {
        background-color: var(--attention-light);
        border-color: var(--attention);
    }
    
    .status-banner-title {
        font-weight: 600;
        color: var(--attention-dark);
        margin-bottom: var(--space-1);
    }
    
    .status-banner-text {
        font-size: 0.875rem;
        color: var(--text-secondary);
        margin: 0;
    }
</style>
@endpush

@push('scripts')
<script>
    // Kid-specific interactions
    document.addEventListener('DOMContentLoaded', function() {
        // Animate checklist items on completion
        document.querySelectorAll('.checklist-item').forEach(function(item) {
            item.addEventListener('click', function() {
                if (!this.classList.contains('completed')) {
                    this.classList.add('completing');
                    // Let parent form/handler deal with actual completion
                }
            });
        });
    });
</script>
@endpush
