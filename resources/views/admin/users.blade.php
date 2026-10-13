@extends('layouts.admin')

@section('title', 'Users & Accounts')

@push('styles')
<style>
/* Page Header */
.user-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 20px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--admin-card-border, #e2e8f0);
}
.user-page-title {
    margin: 0 0 4px;
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 10px;
}
.user-page-subtitle {
    margin: 0;
    color: #64748b;
    font-size: 13px;
}

/* KPI Cards Grid */
.user-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 14px;
    margin-bottom: 20px;
}
.user-kpi-card {
    background: #ffffff;
    border: 1px solid var(--admin-card-border, #e2e8f0);
    border-radius: 8px;
    padding: 14px 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.user-kpi-icon {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.user-kpi-icon.blue { background: #e0f2fe; color: #0284c7; }
.user-kpi-icon.green { background: #dcfce7; color: #16a34a; }
.user-kpi-icon.purple { background: #f3e8ff; color: #9333ea; }
.user-kpi-icon.amber { background: #fef3c7; color: #d97706; }
.user-kpi-num {
    font-size: 19px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
}
.user-kpi-label {
    font-size: 11.5px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-top: 2px;
}

/* Filter & Tabs Toolbar */
.user-filter-bar {
    background: #ffffff;
    border: 1px solid var(--admin-card-border, #e2e8f0);
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.user-tabs {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.user-tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    text-decoration: none !important;
    transition: all 0.15s ease;
}
.user-tab-btn:hover {
    background: #f1f5f9;
    color: #1e293b;
}
.user-tab-btn.active {
    background: #2563eb;
    color: #ffffff;
    border-color: #2563eb;
}
.user-tab-btn .badge {
    font-size: 11px;
    padding: 2px 7px;
    border-radius: 10px;
    background: rgba(0,0,0,0.08);
    color: inherit;
}
.user-tab-btn.active .badge {
    background: rgba(255,255,255,0.25);
    color: #ffffff;
}

.user-search-form {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.user-search-input {
    min-width: 260px;
    height: 36px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    padding: 6px 12px;
    font-size: 13px;
}
.user-search-input:focus {
    border-color: #2563eb;
    outline: none;
    box-shadow: 0 0 0 2px rgba(37,99,235,0.15);
}

/* Table Card */
.user-table-card {
    background: #ffffff;
    border: 1px solid var(--admin-card-border, #e2e8f0);
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    overflow: visible;
    margin-bottom: 24px;
}
.user-data-table {
    width: 100%;
    min-width: 860px;
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
}
.user-data-table th {
    background: #f8fafc;
    font-size: 12px;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    padding: 12px 16px;
    border-bottom: 1px solid #e2e8f0;
    white-space: nowrap;
}
.user-data-table td {
    padding: 13px 16px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
    color: #334155;
    vertical-align: middle;
}
.user-data-table tbody tr:hover td {
    background: #f8fafc;
}
.user-data-table tbody tr:last-child td {
    border-bottom: none;
}

/* User Avatar & Info */
.user-profile-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}
.user-avatar-badge {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 700;
    flex-shrink: 0;
    box-shadow: 0 2px 4px rgba(37,99,235,0.2);
}
.user-avatar-badge.admin {
    background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
    box-shadow: 0 2px 4px rgba(109,40,217,0.2);
}
.user-name-title {
    font-weight: 700;
    color: #0f172a;
    font-size: 13.5px;
    line-height: 1.3;
}
.user-id-sub {
    font-size: 11.5px;
    color: #94a3b8;
}

/* Role Badges */
.role-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11.5px;
    font-weight: 700;
    white-space: nowrap;
}
.role-badge.admin {
    background: #f3e8ff;
    color: #7e22ce;
    border: 1px solid #e9d5ff;
}
.role-badge.customer {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
}

/* Contact Cell */
.user-contact-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
}
.user-contact-item i {
    font-size: 12px;
    color: #94a3b8;
    width: 14px;
}

/* Empty State */
.user-empty-state {
    padding: 48px 20px;
    text-align: center;
}
.user-empty-icon {
    font-size: 42px;
    color: #cbd5e1;
    margin-bottom: 12px;
}
.user-empty-title {
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 6px;
}
.user-empty-sub {
    font-size: 13px;
    color: #64748b;
    margin: 0 0 16px;
}

@media (max-width: 768px) {
    .user-filter-bar { flex-direction: column; align-items: stretch; gap: 12px; max-width: 100%; }
    .user-tabs { width: 100%; max-width: 100%; min-width: 0; overflow-x: auto; white-space: nowrap; -webkit-overflow-scrolling: touch; touch-action: pan-x pan-y; overscroll-behavior-x: contain; flex-wrap: nowrap; padding-bottom: 4px; }
    .user-tab-btn { flex-shrink: 0; }
    .user-search-form { width: 100%; }
    .user-search-input { width: 100% !important; min-width: 0 !important; flex: 1; }
}
@media (max-width: 480px) {
    .user-kpi-grid { grid-template-columns: 1fr; gap: 10px; }
    .user-kpi-card { padding: 12px 14px; }
}

/* Edit Access Action Button */
.btn-edit-user-access {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    border-radius: 6px;
    font-size: 11.5px;
    font-weight: 600;
    color: #0284c7;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    padding: 4px 10px;
    transition: all 0.15s ease;
    cursor: pointer;
    text-decoration: none !important;
}
.btn-edit-user-access:hover {
    background: #0284c7;
    color: #ffffff !important;
    border-color: #0284c7;
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);
}
.btn-edit-user-access:hover i {
    color: #ffffff !important;
}
</style>
@endpush

@section('content')
<div style="max-width: 1320px; margin: 0 auto;">

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible" style="border-radius:6px; margin-bottom:18px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" style="border-radius:6px; margin-bottom:18px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible" style="border-radius:6px; margin-bottom:18px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul style="margin:0; padding-left:18px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="user-page-header">
        <div>
            <h1 class="user-page-title">
                <i class="fas fa-users text-primary"></i> Users &amp; Accounts
            </h1>
            <p class="user-page-subtitle">
                Manage registered store customers, administrators, and monitor order activity.
            </p>
        </div>
    </div>

    {{-- KPI Cards Grid --}}
    <div class="user-kpi-grid">
        <div class="user-kpi-card">
            <div class="user-kpi-icon blue"><i class="fas fa-users"></i></div>
            <div>
                <div class="user-kpi-num">{{ number_format($totalUsersCount ?? $users->total()) }}</div>
                <div class="user-kpi-label">Total Accounts</div>
            </div>
        </div>
        <div class="user-kpi-card">
            <div class="user-kpi-icon green"><i class="fas fa-user-tag"></i></div>
            <div>
                <div class="user-kpi-num">{{ number_format($customersCount ?? 0) }}</div>
                <div class="user-kpi-label">Customers</div>
            </div>
        </div>
        <div class="user-kpi-card">
            <div class="user-kpi-icon purple"><i class="fas fa-shield-alt"></i></div>
            <div>
                <div class="user-kpi-num">{{ number_format($adminsCount ?? 0) }}</div>
                <div class="user-kpi-label">Administrators</div>
            </div>
        </div>
        <div class="user-kpi-card">
            <div class="user-kpi-icon amber"><i class="fas fa-shopping-bag"></i></div>
            <div>
                @php $sumOrders = $users->sum('orders_count'); @endphp
                <div class="user-kpi-num">{{ number_format($sumOrders) }}</div>
                <div class="user-kpi-label">Orders (This Page)</div>
            </div>
        </div>
    </div>

    {{-- Filter & Search Toolbar --}}
    <div class="user-filter-bar">
        {{-- Role Filter Tabs --}}
        <div class="user-tabs">
            <a href="{{ route('admin.users.index', array_merge(request()->except('page', 'role'), ['role' => 'all'])) }}"
               class="user-tab-btn {{ ($currentRole ?? 'all') === 'all' ? 'active' : '' }}">
                <i class="fas fa-layer-group"></i> All Users
                <span class="badge">{{ number_format($totalUsersCount ?? $users->total()) }}</span>
            </a>
            <a href="{{ route('admin.users.index', array_merge(request()->except('page', 'role'), ['role' => 'customers'])) }}"
               class="user-tab-btn {{ ($currentRole ?? '') === 'customers' ? 'active' : '' }}">
                <i class="fas fa-user"></i> Customers
                <span class="badge">{{ number_format($customersCount ?? 0) }}</span>
            </a>
            <a href="{{ route('admin.users.index', array_merge(request()->except('page', 'role'), ['role' => 'admins'])) }}"
               class="user-tab-btn {{ ($currentRole ?? '') === 'admins' ? 'active' : '' }}">
                <i class="fas fa-shield-alt"></i> Administrators
                <span class="badge">{{ number_format($adminsCount ?? 0) }}</span>
            </a>
        </div>

        {{-- Search Input Form --}}
        <form method="GET" action="{{ route('admin.users.index') }}" class="user-search-form">
            <input type="hidden" name="role" value="{{ $currentRole ?? 'all' }}">
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search by name, email, mobile…" class="form-control user-search-input">
            <button type="submit" class="btn btn-primary btn-sm" style="height:36px; padding:0 14px;">
                <i class="fas fa-search"></i> Search
            </button>
            @if(!empty($filters['q']))
                <a href="{{ route('admin.users.index', ['role' => $currentRole ?? 'all']) }}" class="btn btn-default btn-sm" style="height:36px; line-height:22px;" title="Clear Search">
                    <i class="fas fa-times"></i> Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Users Data Table Card --}}
    <div class="user-table-card">
        <div class="table-responsive" style="margin-bottom:0; border:none; border-radius:0;">
            <div class="mobile-scroll-hint">
                <i class="fas fa-arrows-alt-h"></i> Scroll right to view contact information, orders count, total spent, and join date &rarr;
            </div>
            <table class="user-data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>User Profile</th>
                        <th style="width: 140px;">Role</th>
                        <th>Contact Information</th>
                        <th style="width: 100px; text-align:center;">Orders</th>
                        <th style="width: 110px; text-align:center;">Paid Orders</th>
                        <th style="width: 130px; text-align:right;">Total Spent</th>
                        <th style="width: 130px;">Joined</th>
                        <th style="width: 120px; text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($users as $u)
                    @php
                        $isAdmin = !empty($u->is_admin);
                        $initials = strtoupper(mb_substr($u->name ?? ($u->email ?? 'U'), 0, 1));
                    @endphp
                    <tr>
                        <td style="color:#94a3b8; font-weight:600;">#{{ $u->id }}</td>
                        <td>
                            <div class="user-profile-cell">
                                <div class="user-avatar-badge {{ $isAdmin ? 'admin' : '' }}">
                                    {{ $initials }}
                                </div>
                                <div>
                                    <div class="user-name-title">{{ $u->name ?: 'Unnamed User' }}</div>
                                    <div class="user-id-sub">Registered user</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($isAdmin)
                                <span class="role-badge admin">
                                    <i class="fas fa-shield-alt"></i> Administrator
                                </span>
                            @else
                                <span class="role-badge customer">
                                    <i class="fas fa-user"></i> Customer
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="user-contact-item">
                                <i class="fas fa-envelope"></i>
                                <span>{{ $u->email ?: 'No email' }}</span>
                            </div>
                            @if(!empty($u->mobile))
                                <div class="user-contact-item" style="margin-top: 3px;">
                                    <i class="fas fa-phone-alt"></i>
                                    <span>{{ $u->mobile }}</span>
                                </div>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if(($u->orders_count ?? 0) > 0)
                                <span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:700; padding:4px 9px;">
                                    {{ $u->orders_count }}
                                </span>
                            @else
                                <span style="color:#94a3b8;">0</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if(($u->paid_orders_count ?? 0) > 0)
                                <span class="badge" style="background:#dcfce7; color:#15803d; font-weight:700; padding:4px 9px;">
                                    {{ $u->paid_orders_count }}
                                </span>
                            @else
                                <span style="color:#94a3b8;">0</span>
                            @endif
                        </td>
                        <td style="text-align: right; font-weight: 700; color: {{ ($u->total_spent ?? 0) > 0 ? '#0f172a' : '#94a3b8' }};">
                            ₹{{ number_format($u->total_spent ?? 0, 0) }}
                        </td>
                        <td>
                            <span style="font-size:12px; color:#64748b;">
                                {{ $u->created_at?->format('d M Y') ?? '—' }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <button type="button" class="btn-edit-user-access"
                                    data-id="{{ $u->id }}"
                                    data-name="{{ $u->name }}"
                                    data-email="{{ $u->email }}"
                                    data-mobile="{{ $u->mobile }}"
                                    data-is-admin="{{ $isAdmin ? '1' : '0' }}"
                                    data-is-current="{{ $u->id === auth()->id() ? '1' : '0' }}"
                                    title="Edit Access & Profile">
                                <i class="fas fa-user-edit text-primary"></i> Edit Access
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="user-empty-state">
                                <div class="user-empty-icon">
                                    <i class="fas fa-user-slash"></i>
                                </div>
                                <h4 class="user-empty-title">No Users Found</h4>
                                <p class="user-empty-sub">
                                    @if(!empty($filters['q']))
                                        No users match your search query "{{ $filters['q'] }}".
                                    @elseif(($currentRole ?? 'all') !== 'all')
                                        No accounts found in the "{{ ucfirst($currentRole) }}" category.
                                    @else
                                        No user accounts registered yet.
                                    @endif
                                </p>
                                <a href="{{ route('admin.users.index') }}" class="btn btn-default btn-sm">
                                    <i class="fas fa-redo"></i> Reset Filters
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div style="padding: 12px 16px; border-top: 1px solid #e2e8f0; display:flex; justify-content:flex-end;">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>

{{-- Edit User Access & Profile Modal --}}
<div class="modal fade" id="editUserAccessModal" tabindex="-1" role="dialog" aria-labelledby="editUserAccessModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            <form id="editUserAccessForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; border-radius: 8px 8px 0 0; padding: 14px 18px;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size: 24px; margin-top: -2px;">&times;</button>
                    <h4 class="modal-title" id="editUserAccessModalLabel" style="font-size: 16px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-user-shield text-primary"></i> Edit User Access &amp; Profile
                    </h4>
                </div>
                <div class="modal-body" style="padding: 18px 20px;">
                    <div id="modalUserAlert" class="alert alert-danger" style="display: none; font-size: 13px;"></div>

                    <div class="form-group">
                        <label for="edit_user_name" style="font-size: 13px; font-weight: 600; color: #334155;">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_user_name" name="name" required style="border-radius: 6px; height: 38px;">
                    </div>

                    <div class="form-group">
                        <label for="edit_user_email" style="font-size: 13px; font-weight: 600; color: #334155;">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="edit_user_email" name="email" required style="border-radius: 6px; height: 38px;">
                    </div>

                    <div class="form-group">
                        <label for="edit_user_mobile" style="font-size: 13px; font-weight: 600; color: #334155;">Mobile Number</label>
                        <input type="text" class="form-control" id="edit_user_mobile" name="mobile" placeholder="10-digit mobile number" style="border-radius: 6px; height: 38px;">
                    </div>

                    {{-- Administrator Role / Access Toggle --}}
                    <div class="form-group" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; margin-top: 14px;">
                        <label style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 6px; display: block;">
                            <i class="fas fa-shield-alt" style="color: #9333ea;"></i> Administrator Access
                        </label>
                        <div class="checkbox" style="margin: 0;">
                            <label style="font-size: 13px; color: #334155; font-weight: 500; cursor: pointer;">
                                <input type="checkbox" id="edit_user_is_admin" name="is_admin" value="1" style="margin-top: 3px;">
                                Grant full administrator privileges (access to Admin Panel)
                            </label>
                        </div>
                        <div id="selfAdminWarning" style="display: none; margin-top: 6px; font-size: 12px; color: #b45309;">
                            <i class="fas fa-info-circle"></i> You are currently signed in with this account. Administrator access cannot be removed from yourself.
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0; margin-top: 14px;">
                        <label for="edit_user_password" style="font-size: 13px; font-weight: 600; color: #334155;">New Password <span class="text-muted" style="font-weight: 400; font-size: 12px;">(optional — leave blank to keep unchanged)</span></label>
                        <input type="password" class="form-control" id="edit_user_password" name="password" minlength="6" placeholder="Enter new password (min. 6 characters)" style="border-radius: 6px; height: 38px;">
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; border-radius: 0 0 8px 8px; padding: 12px 18px;">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal" style="height: 36px; padding: 0 16px;">Cancel</button>
                    <button type="submit" id="saveUserAccessBtn" class="btn btn-primary btn-sm" style="height: 36px; padding: 0 18px;">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    $(document).on('click', '.btn-edit-user-access', function() {
        var btn = $(this);
        var id = btn.data('id');
        var name = btn.data('name');
        var email = btn.data('email');
        var mobile = btn.data('mobile') || '';
        var isAdmin = String(btn.data('is-admin')) === '1';
        var isCurrent = String(btn.data('is-current')) === '1';

        $('#editUserAccessForm').attr('action', '{{ url("admin/users") }}/' + id);
        $('#edit_user_name').val(name);
        $('#edit_user_email').val(email);
        $('#edit_user_mobile').val(mobile);
        $('#edit_user_password').val('');
        $('#modalUserAlert').hide().text('');

        var adminCheck = $('#edit_user_is_admin');
        adminCheck.prop('checked', isAdmin);

        if (isCurrent) {
            adminCheck.prop('disabled', true);
            $('#selfAdminWarning').show();
            if (!$('#selfAdminHidden').length) {
                $('#editUserAccessForm').append('<input type="hidden" id="selfAdminHidden" name="is_admin" value="1">');
            }
        } else {
            adminCheck.prop('disabled', false);
            $('#selfAdminWarning').hide();
            $('#selfAdminHidden').remove();
        }

        $('#editUserAccessModal').modal('show');
    });

    $('#editUserAccessForm').on('submit', function(e) {
        var $form = $(this);
        var $btn = $('#saveUserAccessBtn');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
    });
})();
</script>
@endpush
@endsection
