@extends('layouts.admin')

@section('title', 'Rules & Bonuses - Grounding Buddy')

@section('header-title', 'Rules & Bonuses')

@section('content')
    {{-- Chores/Rules Tab --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Chores & Rules</h3>
            <button type="button" class="btn btn-primary" onclick="alert('Add rule form coming soon')">
                Add Rule
            </button>
        </div>

        <p class="text-muted">
            Define the daily rules and chores that kids need to complete. Each rule can have a point value.
        </p>
        
        <div class="alert alert-info mt-4">
            Rule definitions are being implemented.
        </div>
    </div>

    {{-- Bonus Definitions Tab --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Bonus Opportunities</h3>
            <button type="button" class="btn btn-primary" onclick="alert('Add bonus form coming soon')">
                Add Bonus
            </button>
        </div>

        <p class="text-muted">
            Define bonus opportunities that kids can complete for extra points.
        </p>
        
        <div class="alert alert-info mt-4">
            Bonus definitions are being implemented.
        </div>
    </div>

    {{-- Consequence Definitions Tab --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Consequence Types</h3>
            <button type="button" class="btn btn-primary" onclick="alert('Add consequence form coming soon')">
                Add Type
            </button>
        </div>

        <p class="text-muted">
            Define the types of consequences and their point values.
        </p>
        
        <div class="alert alert-info mt-4">
            Consequence definitions are being implemented.
        </div>
    </div>
@endsection
