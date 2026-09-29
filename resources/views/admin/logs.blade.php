@extends('layouts.admin')

@section('title', 'User & System Logs')

@push('styles')
<style>
    .log-card {
        background: #fff;
        border: 1px solid #e7e7e7;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .log-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .log-table th, .log-table td { padding: 12px 16px; text-align: left; }
    .log-table thead th { background: #fafafa; border-bottom: 1px solid #eee; font-size: 11px; font-weight: 600; text-transform: uppercase; color: #666; }
    .log-table tbody tr { border-top: 1px solid #f2f2f2; }
    .event-badge {
        display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; background: #eef2f6; color: #333;
    }
</style>
@endpush

@section('content')
<div class="container-fluid" style="padding: 24px 20px;">

    <div style="margin-bottom: 20px;">
        <h2 style="margin:0; font-weight:700; font-size:22px; color:#222;">
            <i class="fas fa-clipboard-list" style="color:#3a7bd5; margin-right:8px;"></i> System &amp; Order Activity Logs
        </h2>
        <div style="font-size:13px; color:#777; margin-top:3px;">
            Audit trail of operational events, status updates, and staff actions.
        </div>
    </div>

    <div class="log-card">
        @if(empty($events))
            <div style="padding: 40px; text-align: center; color: #999;">
                <i class="fas fa-history" style="font-size: 36px; margin-bottom: 8px; display: block; color: #ddd;"></i>
                No activity logs recorded yet.
            </div>
        @else
            <table class="log-table">
                <thead>
                    <tr>
                        <th style="width:140px;">Timestamp</th>
                        <th style="width:130px;">Order Code</th>
                        <th style="width:130px;">Action / Event</th>
                        <th>Details</th>
                        <th style="width:140px;">Performed By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $e)
                        <tr>
                            <td style="color:#666; font-size:12px;">{{ $e['at'] }}</td>
                            <td>
                                <a href="{{ route('admin.orders.show', $e['order_code']) }}" style="font-weight:600; color:#3a7bd5; text-decoration:none;">
                                    {{ $e['order_code'] }}
                                </a>
                            </td>
                            <td>
                                <span class="event-badge">{{ str_replace('_', ' ', $e['event']) }}</span>
                            </td>
                            <td style="color:#444;">{{ $e['detail'] ?: '—' }}</td>
                            <td style="font-weight:500; color:#333;">
                                <i class="fas fa-user-circle" style="color:#aaa; margin-right:4px;"></i> {{ $e['by'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

</div>
@endsection
