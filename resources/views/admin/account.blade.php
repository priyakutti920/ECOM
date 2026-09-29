@extends('layouts.admin')

@section('title', 'Admin Account')

@push('styles')
<style>
    .acc-card {
        background: #fff;
        border: 1px solid #e7e7e7;
        border-radius: 8px;
        padding: 24px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .acc-card-title {
        font-size: 16px;
        font-weight: 700;
        color: #222;
        margin: 0 0 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #eee;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .form-group { margin-bottom: 16px; }
    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #444;
        margin-bottom: 6px;
    }
    .form-control {
        width: 100%;
        max-width: 480px;
        padding: 9px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 13px;
        box-sizing: border-box;
    }
    .form-control:focus {
        border-color: #3a7bd5;
        outline: none;
        box-shadow: 0 0 0 2px rgba(58,123,213,0.15);
    }
    .btn-save {
        background: #3a7bd5;
        color: #fff;
        border: none;
        padding: 9px 20px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-save:hover { background: #2f6bc4; }
</style>
@endpush

@section('content')
<div class="container-fluid" style="max-width: 800px; padding: 24px 20px;">

    <h2 style="font-weight:700; font-size:22px; margin:0 0 20px; color:#222;">
        <i class="fas fa-user-circle" style="color:#3a7bd5; margin-right:8px;"></i> Account Settings
    </h2>

    @if(session('success'))
        <div class="alert alert-success" style="border-radius:6px;">
            <i class="fas fa-check-circle" style="margin-right:6px;"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="border-radius:6px;">
            <i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i> {{ session('error') }}
        </div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger" style="border-radius:6px;">
            <ul style="margin:0; padding-left:18px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="acc-card">
        <div class="acc-card-title">
            <i class="fas fa-id-badge" style="color:#3a7bd5;"></i> Profile Information
        </div>
        <form method="POST" action="{{ route('admin.account.update') }}">
            @csrf
            <div class="form-group">
                <label>Admin Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $admin->name) }}" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $admin->email) }}" required>
            </div>

            <div class="acc-card-title" style="margin-top:24px; padding-top:12px;">
                <i class="fas fa-key" style="color:#7b1fa2;"></i> Change Password (Optional)
            </div>

            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" class="form-control" placeholder="Leave empty if not changing">
            </div>

            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" class="form-control" placeholder="Minimum 6 characters">
            </div>

            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="new_password_confirmation" class="form-control" placeholder="Re-type new password">
            </div>

            <div style="margin-top:20px;">
                <button type="submit" class="btn-save">
                    <i class="fas fa-save" style="margin-right:6px;"></i> Save Changes
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
