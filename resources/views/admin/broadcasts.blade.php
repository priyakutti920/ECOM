@extends('layouts.admin')

@section('title', 'Broadcasts & Announcements')

@push('styles')
<style>
    .bc-card {
        background: #fff;
        border: 1px solid #e7e7e7;
        border-radius: 8px;
        padding: 24px;
        max-width: 720px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; font-weight: 600; font-size: 13px; color: #333; margin-bottom: 6px; }
    .form-control {
        width: 100%; padding: 9px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; box-sizing: border-box;
    }
    .btn-save {
        background: #3a7bd5; color: #fff; border: none; padding: 9px 20px; border-radius: 6px; font-weight: 600; cursor: pointer;
    }
</style>
@endpush

@section('content')
<div class="container-fluid" style="padding: 24px 20px;">

    <div style="margin-bottom: 20px;">
        <h2 style="margin:0; font-weight:700; font-size:22px; color:#222;">
            <i class="fas fa-bullhorn" style="color:#3a7bd5; margin-right:8px;"></i> Broadcasts &amp; Announcements
        </h2>
        <div style="font-size:13px; color:#777; margin-top:3px;">
            Configure banner announcements shown to store shoppers.
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="border-radius:6px; max-width:720px;">
            <i class="fas fa-check-circle" style="margin-right:6px;"></i> {{ session('success') }}
        </div>
    @endif

    <div class="bc-card">
        <form method="POST" action="{{ route('admin.broadcasts.save') }}">
            @csrf
            <div class="form-group">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="broadcast_enabled" value="1" {{ $broadcastEnabled ? 'checked' : '' }} style="width:18px; height:18px;">
                    <span style="font-weight:700; font-size:14px; color:#222;">Enable Storefront Announcement Banner</span>
                </label>
            </div>

            <div class="form-group">
                <label>Announcement Message</label>
                <textarea name="broadcast_message" class="form-control" rows="3" placeholder="e.g. Free shipping on all orders over ₹499! Use coupon FESTIVE">{{ $broadcastMessage }}</textarea>
            </div>

            <div class="form-group">
                <label>Banner Type / Style</label>
                <select name="broadcast_type" class="form-control" style="max-width:300px;">
                    <option value="info"    {{ $broadcastType === 'info' ? 'selected' : '' }}>Info (Blue)</option>
                    <option value="success" {{ $broadcastType === 'success' ? 'selected' : '' }}>Promotion / Success (Green)</option>
                    <option value="warning" {{ $broadcastType === 'warning' ? 'selected' : '' }}>Notice / Warning (Amber)</option>
                </select>
            </div>

            <button type="submit" class="btn-save">
                <i class="fas fa-save" style="margin-right:6px;"></i> Save Broadcast
            </button>
        </form>
    </div>

</div>
@endsection
