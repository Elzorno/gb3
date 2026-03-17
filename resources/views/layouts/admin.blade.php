@extends('layouts.base')

@section('body-class', 'admin-layout')

@section('body')
<header class="header" style="background-color: var(--primary);">
    <div class="header-inner">
        <div class="flex items-center gap-4">
            <h1 class="header-title">
                @hasSection('header-title')
                    @yield('header-title')
                @else
                    {{ config('app.branding.family_name', 'Family') }} Dashboard
                @endif
            </h1>
        </div>
        
        {{-- Mobile hamburger button --}}
        <button 
            type="button" 
            class="menu-toggle" 
            id="menuToggle"
            aria-expanded="false"
            aria-controls="mainNav"
            aria-label="Toggle navigation menu"
        >
            <span class="menu-icon" aria-hidden="true">☰</span>
            <span class="menu-icon-close" aria-hidden="true" style="display: none;">✕</span>
        </button>
        
        <nav class="header-nav" id="mainNav" aria-label="Main navigation">
            <a href="{{ route('admin.dashboard') }}" class="header-link @if(request()->routeIs('admin.dashboard')) active @endif">
                Home
            </a>
            <a href="{{ route('admin.family') }}" class="header-link @if(request()->routeIs('admin.family*')) active @endif">
                Family
            </a>
            <a href="{{ route('admin.rotation') }}" class="header-link @if(request()->routeIs('admin.rotation*')) active @endif">
                Chores
            </a>
            <a href="{{ route('admin.infractions') }}" class="header-link @if(request()->routeIs('admin.infractions*')) active @endif">
                Consequence
            </a>
            <a href="{{ route('admin.reviews') }}" class="header-link @if(request()->routeIs('admin.reviews*')) active @endif">
                Reviews
            </a>
            <a href="{{ route('admin.definitions') }}" class="header-link @if(request()->routeIs('admin.definitions*')) active @endif">
                Rules
            </a>
            <a href="{{ route('admin.settings') }}" class="header-link @if(request()->routeIs('admin.settings*')) active @endif">
                Settings
            </a>
            <a href="{{ route('admin.logout') }}" class="header-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Logout
            </a>
        </nav>
    </div>
</header>

{{-- Mobile nav overlay --}}
<div class="nav-overlay" id="navOverlay" aria-hidden="true"></div>

<form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<main class="main" id="main-content" role="main">
    <div class="container">
        @hasSection('page-header')
            <div class="flex justify-between items-center mb-6">
                <h2 class="mt-0 mb-0">@yield('page-title')</h2>
                @yield('page-actions')
            </div>
        @endif
        
        @yield('content')
    </div>
</main>
@endsection

@push('head')
<style>
    /* Admin-specific styles */
    .admin-layout {
        --bg-header: var(--primary);
    }
    
    /* Sidebar for desktop (optional enhancement) */
    @media (min-width: 1024px) {
        .admin-layout .header-nav {
            gap: var(--space-4);
        }
    }
    
    /* Mobile hamburger menu */
    @media (max-width: 768px) {
        .admin-layout .header-nav {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            flex-direction: column;
            background-color: var(--primary-dark);
            padding: var(--space-2);
            box-shadow: var(--shadow-lg);
        }
        
        .admin-layout .header-nav.open {
            display: flex;
        }
        
        .admin-layout .header-link {
            padding: var(--space-3) var(--space-4);
        }
        
        .admin-layout .menu-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: var(--tap-target);
            height: var(--tap-target);
            color: white;
            background: none;
            border: none;
            cursor: pointer;
        }
    }
    
    /* Admin data tables */
    .admin-table {
        background: var(--bg-card);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-md);
        overflow: hidden;
    }
    
    .admin-table table {
        width: 100%;
        margin: 0;
    }
    
    .admin-table th {
        background-color: var(--neutral-100);
    }
    
    .admin-table tr:hover {
        background-color: var(--neutral-50);
    }
    
    /* Action buttons in tables */
    .action-buttons {
        display: flex;
        gap: var(--space-2);
    }
    
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: var(--border-radius);
        color: var(--text-secondary);
        background-color: var(--neutral-100);
        border: none;
        cursor: pointer;
        transition: all var(--transition-fast);
    }
    
    .action-btn:hover {
        background-color: var(--primary-light);
        color: var(--primary);
    }
    
    /* Quick stats cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--space-4);
        margin-bottom: var(--space-6);
    }
    
    .stat-card {
        background: var(--bg-card);
        padding: var(--space-5);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-sm);
        text-align: center;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary);
        line-height: 1;
    }
    
    .stat-label {
        margin-top: var(--space-2);
        font-size: 0.875rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    /* Mobile navigation overlay */
    .nav-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 99;
    }
    
    .nav-overlay.open {
        display: block;
    }
    
    /* Menu toggle button - only visible on mobile */
    .menu-toggle {
        display: none;
    }
    
    @media (max-width: 768px) {
        .menu-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: var(--tap-target);
            height: var(--tap-target);
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1.5rem;
            transition: background-color var(--transition-fast);
        }
        
        .menu-toggle:hover,
        .menu-toggle:focus {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .menu-toggle[aria-expanded="true"] .menu-icon {
            display: none;
        }
        
        .menu-toggle[aria-expanded="true"] .menu-icon-close {
            display: inline !important;
        }
    }
    
    /* Scroll lock when menu is open */
    body.menu-open {
        overflow: hidden;
    }
</style>
@endpush

@push('scripts')
<script>
    // Accessible mobile menu
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.getElementById('menuToggle');
        const headerNav = document.getElementById('mainNav');
        const navOverlay = document.getElementById('navOverlay');
        const body = document.body;
        
        if (!menuToggle || !headerNav) return;
        
        // Get all focusable elements in the nav
        const getFocusableElements = () => {
            return headerNav.querySelectorAll('a, button');
        };
        
        const openMenu = () => {
            menuToggle.setAttribute('aria-expanded', 'true');
            headerNav.classList.add('open');
            navOverlay.classList.add('open');
            body.classList.add('menu-open');
            
            // Focus first nav item
            const focusable = getFocusableElements();
            if (focusable.length > 0) {
                focusable[0].focus();
            }
        };
        
        const closeMenu = () => {
            menuToggle.setAttribute('aria-expanded', 'false');
            headerNav.classList.remove('open');
            navOverlay.classList.remove('open');
            body.classList.remove('menu-open');
            menuToggle.focus();
        };
        
        const isMenuOpen = () => {
            return menuToggle.getAttribute('aria-expanded') === 'true';
        };
        
        // Toggle button click
        menuToggle.addEventListener('click', function() {
            if (isMenuOpen()) {
                closeMenu();
            } else {
                openMenu();
            }
        });
        
        // Click overlay to close
        if (navOverlay) {
            navOverlay.addEventListener('click', closeMenu);
        }
        
        // Escape key to close
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isMenuOpen()) {
                closeMenu();
            }
        });
        
        // Focus trap within open menu
        headerNav.addEventListener('keydown', function(e) {
            if (!isMenuOpen() || e.key !== 'Tab') return;
            
            const focusable = getFocusableElements();
            const firstFocusable = focusable[0];
            const lastFocusable = focusable[focusable.length - 1];
            
            if (e.shiftKey) {
                // Shift+Tab: if on first element, go to last
                if (document.activeElement === firstFocusable) {
                    e.preventDefault();
                    lastFocusable.focus();
                }
            } else {
                // Tab: if on last element, go to first
                if (document.activeElement === lastFocusable) {
                    e.preventDefault();
                    firstFocusable.focus();
                }
            }
        });
        
        // Close menu when clicking a nav link (for same-page navigation)
        headerNav.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function() {
                closeMenu();
            });
        });
    });
</script>
@endpush
