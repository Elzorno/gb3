@extends('layouts.admin')

@section('title', 'Family - Grounding Buddy')

@section('header-title', 'Manage Family')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Kids</h3>
            <button type="button" class="btn btn-primary" onclick="alert('Add kid form coming soon')">
                Add Kid
            </button>
        </div>

        <p class="text-muted">
            This page will allow you to add, edit, and manage kid accounts including setting their PINs.
        </p>
        
        {{-- Placeholder for kid list/management --}}
        <div class="alert alert-info mt-4">
            Family management features are being implemented.
        </div>
    </div>
@endsection
