@extends('layouts.admin')

@section('title', 'Dashboard - Grounding Buddy')

@section('header-title', $familyName . ' Dashboard')

@section('content')
    {{-- Quick Stats --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $kids->count() }}</div>
            <div class="stat-label">Kids</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value" style="color: {{ $pendingReviews > 0 ? 'var(--attention)' : 'var(--success)' }}">
                {{ $pendingReviews }}
            </div>
            <div class="stat-label">Pending Reviews</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value">{{ $todaySubmissions }}</div>
            <div class="stat-label">Today's Activity</div>
        </div>
        
        @if($kidsNeedingAttention > 0)
        <div class="stat-card" style="background-color: var(--attention-light); border: 1px solid var(--attention);">
            <div class="stat-value" style="color: var(--attention-dark);">{{ $kidsNeedingAttention }}</div>
            <div class="stat-label">Needs Attention</div>
        </div>
        @endif
    </div>

    {{-- Kids Overview --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Kids</h3>
            <a href="{{ route('admin.family') }}" class="btn btn-secondary">Manage Family</a>
        </div>

        @if($kids->isEmpty())
            <div class="text-center p-6">
                <p class="text-muted mb-4">No kids have been added yet.</p>
                <a href="{{ route('admin.family') }}" class="btn btn-primary">Add Your First Kid</a>
            </div>
        @else
            <div class="admin-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kids as $kid)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <span class="kid-avatar-small">{{ strtoupper(substr($kid->name, 0, 1)) }}</span>
                                        <span>{{ $kid->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($kid->is_grounded ?? false)
                                        <span class="badge badge-attention">On Consequence</span>
                                    @else
                                        <span class="badge badge-success">Good Standing</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.family') }}?kid={{ $kid->id }}" class="btn btn-secondary" style="padding: var(--space-2) var(--space-4);">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Pending Reviews --}}
    @if($pendingReviews > 0)
    <div class="card" style="border-left: 4px solid var(--attention);">
        <div class="card-header">
            <h3 class="card-title">Pending Reviews</h3>
            <a href="{{ route('admin.reviews') }}" class="btn btn-attention">Review All</a>
        </div>
        <p class="text-muted mb-0">
            There {{ $pendingReviews === 1 ? 'is' : 'are' }} {{ $pendingReviews }} 
            submission{{ $pendingReviews === 1 ? '' : 's' }} waiting for your review.
        </p>
    </div>
    @endif

    {{-- Quick Actions --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Quick Actions</h3>
        </div>
        <div class="flex gap-4" style="flex-wrap: wrap;">
            <a href="{{ route('admin.reviews') }}" class="btn btn-outline">
                Review Submissions
            </a>
            <a href="{{ route('admin.definitions') }}" class="btn btn-outline">
                Edit Rules & Bonuses
            </a>
            <a href="{{ route('admin.settings') }}" class="btn btn-outline">
                App Settings
            </a>
        </div>
    </div>
@endsection

@push('head')
<style>
    .kid-avatar-small {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        background: var(--secondary);
        color: white;
        border-radius: 50%;
        font-weight: 600;
        font-size: 0.875rem;
    }
</style>
@endpush
