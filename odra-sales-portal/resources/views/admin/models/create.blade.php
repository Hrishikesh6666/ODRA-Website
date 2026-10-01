@extends('layouts.admin')

@section('title', 'Add New Model')
@section('header-title', 'Create New Sweeper Model')

@section('content')
<div class="card" style="max-width: 800px;">
    <form action="{{ route('admin.models.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label>Model Code (e.g. MT4H, MS5H)</label>
            <input type="text" name="code" class="form-control" value="{{ old('code') }}" required placeholder="e.g. MS6H">
        </div>

        <div class="form-group">
            <label>Category</label>
            <input type="text" name="category" class="form-control" value="{{ old('category', 'MECHANICAL BROOM SWEEPER') }}" required>
        </div>

        <div class="form-group">
            <label>Description / Standard Specifications</label>
            <textarea name="description" class="form-control" rows="4" required placeholder="Enter sweeper standard specifications and standard features...">{{ old('description') }}</textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin: 20px 0; background: #f8fafc; padding: 16px; border-radius: 6px; border: 1px solid var(--border);">
            <div class="form-group" style="margin:0;">
                <label>MSRP (Commercial USD)</label>
                <input type="number" name="msrp" class="form-control" value="{{ old('msrp', 0) }}" required min="0" step="1">
            </div>
            <div class="form-group" style="margin:0;">
                <label>Sourcewell List Price</label>
                <input type="number" name="sw_list" class="form-control" value="{{ old('sw_list', 0) }}" required min="0" step="1">
            </div>
            <div class="form-group" style="margin:0;">
                <label>Sourcewell Net Price</label>
                <input type="number" name="sw_net" class="form-control" value="{{ old('sw_net', 0) }}" required min="0" step="1">
            </div>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <button type="submit" class="btn-primary">Create Model &amp; Pricing Master</button>
            <a href="{{ route('admin.models.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
