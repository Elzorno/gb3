<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Grounding Buddy')</title>
    
    {{-- Inline critical CSS for fast first paint --}}
    <style>
        :root {
            /* Trauma-informed color palette - calm, non-threatening */
            --primary: #4A90A4;          /* Calm blue - trust, stability */
            --primary-dark: #3A7A94;     /* Darker blue for hover states */
            --primary-light: #E8F4F8;    /* Light blue for backgrounds */
            
            --secondary: #6B8E7B;        /* Soft sage green - growth, calm */
            --secondary-dark: #5A7D6A;
            --secondary-light: #E8F0EB;
            
            --attention: #E8A756;        /* Soft orange - NOT red (trauma-informed) */
            --attention-dark: #D69545;
            --attention-light: #FDF5E8;
            
            --success: #7CB586;          /* Soft green - positive reinforcement */
            --success-dark: #6AA576;
            --success-light: #E8F5EA;
            
            --neutral-50: #FAFBFC;
            --neutral-100: #F4F6F8;
            --neutral-200: #E4E8EC;
            --neutral-300: #CDD4DB;
            --neutral-400: #9BA6B2;
            --neutral-500: #6B7A8A;
            --neutral-600: #4A5568;
            --neutral-700: #2D3748;
            --neutral-800: #1A202C;
            
            --text-primary: var(--neutral-700);
            --text-secondary: var(--neutral-500);
            --text-muted: var(--neutral-400);
            
            --bg-page: var(--neutral-50);
            --bg-card: #FFFFFF;
            --bg-header: var(--primary);
            
            --border-color: var(--neutral-200);
            --border-radius: 8px;
            --border-radius-lg: 12px;
            
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            
            --transition-fast: 150ms ease;
            --transition-normal: 250ms ease;
            
            /* Spacing scale */
            --space-1: 0.25rem;
            --space-2: 0.5rem;
            --space-3: 0.75rem;
            --space-4: 1rem;
            --space-5: 1.25rem;
            --space-6: 1.5rem;
            --space-8: 2rem;
            --space-10: 2.5rem;
            --space-12: 3rem;
            
            /* Touch-friendly sizing */
            --tap-target: 44px;
            --input-height: 48px;
            
            /* Safe area insets for iOS */
            --safe-area-top: env(safe-area-inset-top, 0px);
            --safe-area-bottom: env(safe-area-inset-bottom, 0px);
            --safe-area-left: env(safe-area-inset-left, 0px);
            --safe-area-right: env(safe-area-inset-right, 0px);
        }
        
        *, *::before, *::after {
            box-sizing: border-box;
        }
        
        html {
            font-size: 16px;
            -webkit-text-size-adjust: 100%;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 1rem;
            line-height: 1.5;
            color: var(--text-primary);
            background-color: var(--bg-page);
            min-height: 100vh;
            padding-bottom: var(--safe-area-bottom);
        }
        
        /* Typography */
        h1, h2, h3, h4, h5, h6 {
            margin: 0 0 var(--space-4);
            font-weight: 600;
            line-height: 1.25;
            color: var(--text-primary);
        }
        
        h1 { font-size: 1.75rem; }
        h2 { font-size: 1.5rem; }
        h3 { font-size: 1.25rem; }
        h4 { font-size: 1.125rem; }
        
        p { margin: 0 0 var(--space-4); }
        
        a {
            color: var(--primary);
            text-decoration: none;
            transition: color var(--transition-fast);
        }
        
        a:hover, a:focus {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        /* Layout containers */
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 var(--space-4);
        }
        
        .container-narrow {
            max-width: 600px;
        }
        
        /* Cards - primary content containers */
        .card {
            background: var(--bg-card);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            padding: var(--space-6);
            margin-bottom: var(--space-6);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--space-4);
            padding-bottom: var(--space-4);
            border-bottom: 1px solid var(--border-color);
        }
        
        .card-title {
            margin: 0;
            font-size: 1.25rem;
        }
        
        /* Buttons - large touch targets */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-2);
            min-height: var(--tap-target);
            padding: var(--space-3) var(--space-6);
            font-size: 1rem;
            font-weight: 500;
            line-height: 1.25;
            text-decoration: none;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all var(--transition-fast);
            -webkit-tap-highlight-color: transparent;
        }
        
        .btn:focus {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            color: white;
            text-decoration: none;
        }
        
        .btn-secondary {
            background-color: var(--neutral-200);
            color: var(--text-primary);
        }
        
        .btn-secondary:hover {
            background-color: var(--neutral-300);
            text-decoration: none;
        }
        
        .btn-success {
            background-color: var(--success);
            color: white;
        }
        
        .btn-success:hover {
            background-color: var(--success-dark);
            color: white;
            text-decoration: none;
        }
        
        .btn-attention {
            background-color: var(--attention);
            color: white;
        }
        
        .btn-attention:hover {
            background-color: var(--attention-dark);
            color: white;
            text-decoration: none;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }
        
        .btn-outline:hover {
            background-color: var(--primary);
            color: white;
            text-decoration: none;
        }
        
        .btn-lg {
            min-height: 56px;
            padding: var(--space-4) var(--space-8);
            font-size: 1.125rem;
        }
        
        .btn-block {
            width: 100%;
        }
        
        /* Forms */
        .form-group {
            margin-bottom: var(--space-5);
        }
        
        .form-label {
            display: block;
            margin-bottom: var(--space-2);
            font-weight: 500;
            color: var(--text-primary);
        }
        
        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            height: var(--input-height);
            padding: var(--space-3) var(--space-4);
            font-size: 1rem;
            line-height: 1.5;
            color: var(--text-primary);
            background-color: var(--bg-card);
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
            -webkit-appearance: none;
            appearance: none;
        }
        
        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }
        
        .form-textarea {
            height: auto;
            min-height: 100px;
            resize: vertical;
        }
        
        .form-hint {
            margin-top: var(--space-2);
            font-size: 0.875rem;
            color: var(--text-muted);
        }
        
        .form-error {
            margin-top: var(--space-2);
            font-size: 0.875rem;
            color: var(--attention);
        }
        
        /* PIN input for kids */
        .pin-input {
            display: flex;
            gap: var(--space-3);
            justify-content: center;
        }
        
        .pin-digit {
            width: 48px;
            height: 64px;
            font-size: 1.5rem;
            text-align: center;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            -webkit-appearance: none;
        }
        
        .pin-digit:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }
        
        /* Alerts and messages */
        .alert {
            padding: var(--space-4);
            border-radius: var(--border-radius);
            margin-bottom: var(--space-4);
            font-weight: 500;
        }
        
        .alert-success {
            background-color: var(--success-light);
            color: var(--success-dark);
            border: 1px solid var(--success);
        }
        
        .alert-attention {
            background-color: var(--attention-light);
            color: var(--attention-dark);
            border: 1px solid var(--attention);
        }
        
        .alert-info {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            border: 1px solid var(--primary);
        }
        
        /* Badges and status indicators */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: var(--space-1) var(--space-3);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            border-radius: 999px;
        }
        
        .badge-success {
            background-color: var(--success-light);
            color: var(--success-dark);
        }
        
        .badge-attention {
            background-color: var(--attention-light);
            color: var(--attention-dark);
        }
        
        .badge-neutral {
            background-color: var(--neutral-200);
            color: var(--neutral-600);
        }
        
        /* Progress indicators - positive reinforcement focus */
        .progress {
            height: 12px;
            background-color: var(--neutral-200);
            border-radius: 999px;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--success), var(--primary));
            border-radius: 999px;
            transition: width var(--transition-normal);
        }
        
        /* Tables */
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: var(--space-3) var(--space-4);
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        .table th {
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }
        
        /* Utility classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-muted { color: var(--text-muted); }
        .text-success { color: var(--success-dark); }
        .text-attention { color: var(--attention-dark); }
        
        .mt-0 { margin-top: 0; }
        .mt-4 { margin-top: var(--space-4); }
        .mt-6 { margin-top: var(--space-6); }
        .mb-0 { margin-bottom: 0; }
        .mb-4 { margin-bottom: var(--space-4); }
        .mb-6 { margin-bottom: var(--space-6); }
        
        .p-4 { padding: var(--space-4); }
        .p-6 { padding: var(--space-6); }
        
        .hidden { display: none !important; }
        
        .flex { display: flex; }
        .flex-col { flex-direction: column; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-2 { gap: var(--space-2); }
        .gap-4 { gap: var(--space-4); }
        
        /* Sticky header */
        .header {
            position: sticky;
            top: 0;
            z-index: 100;
            background-color: var(--bg-header);
            color: white;
            padding: var(--space-4);
            padding-top: calc(var(--space-4) + var(--safe-area-top));
            box-shadow: var(--shadow-md);
        }
        
        .header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .header-nav {
            display: flex;
            gap: var(--space-2);
        }
        
        .header-link {
            color: rgba(255, 255, 255, 0.9);
            padding: var(--space-2) var(--space-3);
            border-radius: var(--border-radius);
            transition: background-color var(--transition-fast);
        }
        
        .header-link:hover,
        .header-link:focus {
            background-color: rgba(255, 255, 255, 0.15);
            color: white;
            text-decoration: none;
        }
        
        /* Main content area */
        .main {
            padding: var(--space-6) var(--space-4);
            padding-bottom: calc(var(--space-6) + var(--safe-area-bottom));
        }
        
        /* Bottom navigation for mobile */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            display: flex;
            background-color: var(--bg-card);
            border-top: 1px solid var(--border-color);
            padding-bottom: var(--safe-area-bottom);
            z-index: 100;
        }
        
        .bottom-nav-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: var(--space-2) var(--space-1);
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.75rem;
            transition: color var(--transition-fast);
            min-height: var(--tap-target);
        }
        
        .bottom-nav-item:hover,
        .bottom-nav-item.active {
            color: var(--primary);
            text-decoration: none;
        }
        
        .bottom-nav-icon {
            font-size: 1.25rem;
            margin-bottom: var(--space-1);
        }
        
        /* Loading spinner */
        .spinner {
            width: 24px;
            height: 24px;
            border: 3px solid var(--neutral-200);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Responsive adjustments */
        @media (max-width: 640px) {
            h1 { font-size: 1.5rem; }
            h2 { font-size: 1.25rem; }
            h3 { font-size: 1.125rem; }
            
            .card {
                padding: var(--space-4);
                margin-left: calc(-1 * var(--space-4));
                margin-right: calc(-1 * var(--space-4));
                border-radius: 0;
            }
            
            /* When using bottom nav, add padding to main content */
            .has-bottom-nav .main {
                padding-bottom: calc(70px + var(--safe-area-bottom) + var(--space-6));
            }
        }
        
        /* Print styles */
        @media print {
            .header, .bottom-nav { display: none; }
            .card { box-shadow: none; border: 1px solid var(--border-color); }
        }
        
        /* Accessibility: Skip link */
        .skip-link {
            position: absolute;
            top: -100px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--primary);
            color: white;
            padding: var(--space-3) var(--space-6);
            border-radius: var(--border-radius);
            z-index: 10000;
            font-weight: 600;
            text-decoration: none;
        }
        
        .skip-link:focus {
            top: var(--space-4);
            outline: 3px solid var(--neutral-800);
            outline-offset: 2px;
        }
        
        /* Accessibility: Focus visible improvements */
        :focus-visible {
            outline: 3px solid var(--primary);
            outline-offset: 2px;
        }
        
        /* Remove focus outline for mouse users */
        :focus:not(:focus-visible) {
            outline: none;
        }
        
        /* Ensure links have focus visible */
        a:focus-visible,
        button:focus-visible,
        input:focus-visible,
        select:focus-visible,
        textarea:focus-visible {
            outline: 3px solid var(--primary);
            outline-offset: 2px;
        }
        
        /* Reduced motion preference */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.001ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.001ms !important;
            }
        }
        
        /* High contrast mode support */
        @media (prefers-contrast: more) {
            .card {
                border: 2px solid var(--neutral-700);
            }
            
            .btn {
                border: 2px solid currentColor;
            }
            
            .bottom-nav-item.active {
                border-top: 3px solid var(--primary);
            }
        }
        
        /* Screen reader only content */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
    </style>
    
    @stack('head')
</head>
<body class="@yield('body-class')">
    {{-- Skip link for keyboard navigation --}}
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    @yield('body')
    
    {{-- Flash messages --}}
    @if (session('success'))
        <div class="alert alert-success" role="alert" style="position: fixed; top: var(--space-4); right: var(--space-4); left: var(--space-4); max-width: 500px; margin: 0 auto; z-index: 1000;">
            {{ session('success') }}
        </div>
    @endif
    
    @if (session('error'))
        <div class="alert alert-attention" role="alert" style="position: fixed; top: var(--space-4); right: var(--space-4); left: var(--space-4); max-width: 500px; margin: 0 auto; z-index: 1000;">
            {{ session('error') }}
        </div>
    @endif
    
    @stack('scripts')
    
    <script>
        // Auto-dismiss flash messages
        document.querySelectorAll('.alert[role="alert"]').forEach(function(alert) {
            setTimeout(function() {
                alert.style.transition = 'opacity 0.3s ease';
                alert.style.opacity = '0';
                setTimeout(function() { alert.remove(); }, 300);
            }, 4000);
        });
        
        // CSRF token for AJAX requests
        window.CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
    </script>
</body>
</html>
