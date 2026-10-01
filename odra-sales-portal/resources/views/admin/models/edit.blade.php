@extends('layouts.admin')

@section('title', 'Edit ' . $model->code)
@section('header-title', 'Edit Model: ' . $model->code)

@section('content')
<div class="card" style="max-width: 800px;">
    <form action="{{ route('admin.models.update', $model) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Model Code</label>
            <input type="text" class="form-control" value="{{ $model->code }}" disabled>
        </div>

        <div class="form-group">
            <label>Category</label>
            <input type="text" name="category" class="form-control" value="{{ old('category', $model->category) }}" required>
        </div>

        <div class="form-group">
            <label>Description / Standard Specifications</label>
            <textarea name="description" class="form-control" rows="5" required>{{ old('description', $model->description) }}</textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin: 20px 0; background: #f8fafc; padding: 16px; border-radius: 6px; border: 1px solid var(--border);">
            <div class="form-group" style="margin:0;">
                <label>MSRP (Commercial USD)</label>
                <input type="number" name="msrp" class="form-control" value="{{ old('msrp', $currentPrice?->msrp) }}" required min="0" step="1">
            </div>
            <div class="form-group" style="margin:0;">
                <label>Sourcewell List Price</label>
                <input type="number" name="sw_list" class="form-control" value="{{ old('sw_list', $currentPrice?->sw_list) }}" required min="0" step="1">
            </div>
            <div class="form-group" style="margin:0;">
                <label>Sourcewell Net Price</label>
                <input type="number" name="sw_net" class="form-control" value="{{ old('sw_net', $currentPrice?->sw_net) }}" required min="0" step="1">
            </div>
        </div>

        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" {{ $model->is_active ? 'checked' : '' }}>
                <span>Active and available to Sales Representatives</span>
            </label>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <button type="submit" class="btn-primary">Save Changes &amp; Update Pricing</button>
            <a href="{{ route('admin.models.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
