@extends('layouts.admin')

@section('title', 'Footer Settings')

@push('styles')
<style>
.footer-manager-wrap {
    max-width: 1200px;
    margin: 0 auto;
    padding: 10px 0 40px;
}
.panel-footer-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    margin-bottom: 24px;
    overflow: hidden;
}
.panel-footer-head {
    padding: 14px 20px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.panel-footer-head h3 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 8px;
}
.panel-footer-body {
    padding: 20px;
}
.link-row {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 8px;
}
.link-row input {
    font-size: 13px;
}
.link-row .btn-remove-link {
    background: #fee2e2;
    color: #ef4444;
    border: 1px solid #fca5a5;
    border-radius: 4px;
    padding: 6px 10px;
    cursor: pointer;
    transition: all 0.2s;
}
.link-row .btn-remove-link:hover {
    background: #ef4444;
    color: #fff;
}
.footer-preview-box {
    background: #0f172a;
    border-radius: 8px;
    padding: 24px;
    color: #cbd5e1;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    margin-bottom: 24px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
}
.footer-preview-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    border-bottom: 1px solid #334155;
    padding-bottom: 20px;
}
@media (max-width: 768px) {
    .footer-preview-grid { grid-template-columns: 1fr 1fr; }
}
.footer-preview-col h4 {
    color: #ffffff;
    font-size: 14px;
    font-weight: 700;
    margin: 0 0 12px;
}
.footer-preview-col ul {
    list-style: none;
    padding: 0;
    margin: 0;
    font-size: 12.5px;
    line-height: 1.8;
}
.footer-preview-col ul li a {
    color: #94a3b8;
    text-decoration: none;
}
.footer-preview-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 14px;
    font-size: 12px;
    color: #64748b;
    flex-wrap: wrap;
    gap: 10px;
}
.footer-preview-bottom a {
    color: #38bdf8;
    text-decoration: none;
}
</style>
@endpush

@section('content')
<div class="footer-manager-wrap">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px; flex-wrap:wrap; gap: 12px;">
        <div>
            <h2 style="margin:0 0 4px; font-weight:700; font-size:22px; color:#0f172a;">
                <i class="fas fa-shoe-prints" style="color:#10b981; margin-right:8px;"></i> Storefront Footer Settings
            </h2>
            <p style="margin:0; font-size:13px; color:#64748b;">
                Connect, manage, and customize the 4 columns, contact information, menu links, and copyright text stored in the database.
            </p>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="{{ url('/') }}" target="_blank" class="btn btn-default">
                <i class="fas fa-external-link-alt"></i> View Storefront
            </a>
            <form action="{{ route('admin.appearance.footer.reset') }}" method="POST" onsubmit="return confirm('Reset all footer settings to default?');" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-undo"></i> Reset Defaults
                </button>
            </form>
            <button type="submit" form="footer-form" class="btn btn-primary" style="font-weight:600; padding: 7px 22px;">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <!-- Live Preview -->
    <div class="panel-footer-box">
        <div class="panel-footer-head">
            <h3><i class="fas fa-eye" style="color:#10b981;"></i> Live Footer Preview</h3>
            <span class="badge" style="background:#10b981;">Database Connected</span>
        </div>
        <div class="panel-footer-body" style="padding-bottom:12px;">
            <div class="footer-preview-box">
                <div class="footer-preview-grid">
                    <!-- Preview Col 1 -->
                    <div class="footer-preview-col" id="prevCol1">
                        <h4 id="prevCol1Title">{{ $footer['col1_title'] }}</h4>
                        <ul>
                            <li id="prevCol1Phone"><i class="fas fa-phone-alt"></i> {{ $footer['col1_phone'] }}</li>
                            <li id="prevCol1Email"><i class="fas fa-envelope"></i> {{ $footer['col1_email'] }}</li>
                            <li id="prevCol1Address"><i class="fas fa-map-marker-alt"></i> {{ $footer['col1_address'] }}</li>
                        </ul>
                    </div>

                    <!-- Preview Col 2 -->
                    <div class="footer-preview-col" id="prevCol2">
                        <h4 id="prevCol2Title">{{ $footer['col2_title'] }}</h4>
                        <ul id="prevCol2List">
                            @foreach($footer['col2_links'] as $l)
                                <li><i class="fas fa-chevron-right" style="font-size:10px;"></i> {{ $l['title'] }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Preview Col 3 -->
                    <div class="footer-preview-col" id="prevCol3">
                        <h4 id="prevCol3Title">{{ $footer['col3_title'] }}</h4>
                        <ul id="prevCol3List">
                            @foreach($footer['col3_links'] as $l)
                                <li><i class="fas fa-chevron-right" style="font-size:10px;"></i> {{ $l['title'] }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Preview Col 4 -->
                    <div class="footer-preview-col" id="prevCol4">
                        <h4 id="prevCol4Title">{{ $footer['col4_title'] }}</h4>
                        <ul id="prevCol4List">
                            @foreach($footer['col4_links'] as $l)
                                <li><i class="fas fa-chevron-right" style="font-size:10px;"></i> {{ $l['title'] }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="footer-preview-bottom">
                    <div id="prevCopyright">
                        {{ str_replace(['{store_name}', '{year}'], [\App\Models\StoreSetting::getStoreName(), date('Y')], $footer['copyright_text']) }}
                    </div>
                    <div id="prevBadges">
                        <span id="prevSafeText" style="margin-right:8px;">{{ $footer['safe_checkout_text'] }}</span>
                        <i class="fab fa-cc-visa" style="font-size:20px; color:#38bdf8;"></i>
                        <i class="fab fa-cc-mastercard" style="font-size:20px; color:#f43f5e; margin-left:4px;"></i>
                        <i class="fas fa-shield-alt" style="font-size:18px; color:#10b981; margin-left:4px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Settings Form -->
    <form id="footer-form" method="POST" action="{{ route('admin.appearance.footer.update') }}">
        @csrf

        <div class="row">
            <!-- Left Column: Col 1 (Contact) & Col 2 (Account) -->
            <div class="col-md-6">
                <!-- Col 1: Contact Us -->
                <div class="panel-footer-box">
                    <div class="panel-footer-head">
                        <h3><i class="fas fa-address-book" style="color:#3b82f6;"></i> Column 1: Contact Details</h3>
                    </div>
                    <div class="panel-footer-body">
                        <div class="form-group">
                            <label>Column Heading</label>
                            <input type="text" name="col1_title" id="inpCol1Title" class="form-control" value="{{ $footer['col1_title'] }}" required>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="col1_phone" id="inpCol1Phone" class="form-control" value="{{ $footer['col1_phone'] }}">
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="col1_email" id="inpCol1Email" class="form-control" value="{{ $footer['col1_email'] }}">
                        </div>
                        <div class="form-group">
                            <label>Physical Address</label>
                            <input type="text" name="col1_address" id="inpCol1Address" class="form-control" value="{{ $footer['col1_address'] }}">
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>WhatsApp Number</label>
                                    <input type="text" name="col1_whatsapp" class="form-control" value="{{ $footer['col1_whatsapp'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Instagram URL</label>
                                    <input type="text" name="col1_instagram" class="form-control" value="{{ $footer['col1_instagram'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="col1_show_social" value="1" {{ !empty($footer['col1_show_social']) ? 'checked' : '' }}>
                                Show Social Media Icons under Contact Details
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Col 2: My Account -->
                <div class="panel-footer-box">
                    <div class="panel-footer-head">
                        <h3><i class="fas fa-user-circle" style="color:#8b5cf6;"></i> Column 2: Account Links</h3>
                        <label style="font-weight:normal; margin:0; cursor:pointer;">
                            <input type="checkbox" name="col2_enabled" value="1" {{ !empty($footer['col2_enabled']) ? 'checked' : '' }}> Enable Column
                        </label>
                    </div>
                    <div class="panel-footer-body">
                        <div class="form-group">
                            <label>Column Heading</label>
                            <input type="text" name="col2_title" class="form-control" value="{{ $footer['col2_title'] }}">
                        </div>
                        <label>Menu Links</label>
                        <div id="col2LinksContainer">
                            @foreach($footer['col2_links'] as $link)
                                <div class="link-row">
                                    <input type="text" name="col2_link_titles[]" class="form-control" value="{{ $link['title'] }}" placeholder="Link Label" style="width:40%;">
                                    <input type="text" name="col2_link_urls[]" class="form-control" value="{{ $link['url'] }}" placeholder="/route-path" style="flex:1;">
                                    <button type="button" class="btn-remove-link" onclick="this.closest('.link-row').remove();"><i class="fas fa-trash"></i></button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-xs btn-default" style="margin-top:6px;" onclick="addLinkRow('col2')">
                            <i class="fas fa-plus"></i> Add Link
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Column: Col 3 (Info) & Col 4 (Service) & Bottom Bar -->
            <div class="col-md-6">
                <!-- Col 3: Information -->
                <div class="panel-footer-box">
                    <div class="panel-footer-head">
                        <h3><i class="fas fa-info-circle" style="color:#0ea5e9;"></i> Column 3: Information Links</h3>
                        <label style="font-weight:normal; margin:0; cursor:pointer;">
                            <input type="checkbox" name="col3_enabled" value="1" {{ !empty($footer['col3_enabled']) ? 'checked' : '' }}> Enable Column
                        </label>
                    </div>
                    <div class="panel-footer-body">
                        <div class="form-group">
                            <label>Column Heading</label>
                            <input type="text" name="col3_title" class="form-control" value="{{ $footer['col3_title'] }}">
                        </div>
                        <label>Menu Links</label>
                        <div id="col3LinksContainer">
                            @foreach($footer['col3_links'] as $link)
                                <div class="link-row">
                                    <input type="text" name="col3_link_titles[]" class="form-control" value="{{ $link['title'] }}" placeholder="Link Label" style="width:40%;">
                                    <input type="text" name="col3_link_urls[]" class="form-control" value="{{ $link['url'] }}" placeholder="/route-path" style="flex:1;">
                                    <button type="button" class="btn-remove-link" onclick="this.closest('.link-row').remove();"><i class="fas fa-trash"></i></button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-xs btn-default" style="margin-top:6px;" onclick="addLinkRow('col3')">
                            <i class="fas fa-plus"></i> Add Link
                        </button>
                    </div>
                </div>

                <!-- Col 4: Customer Service -->
                <div class="panel-footer-box">
                    <div class="panel-footer-head">
                        <h3><i class="fas fa-headset" style="color:#f59e0b;"></i> Column 4: Customer Service</h3>
                        <label style="font-weight:normal; margin:0; cursor:pointer;">
                            <input type="checkbox" name="col4_enabled" value="1" {{ !empty($footer['col4_enabled']) ? 'checked' : '' }}> Enable Column
                        </label>
                    </div>
                    <div class="panel-footer-body">
                        <div class="form-group">
                            <label>Column Heading</label>
                            <input type="text" name="col4_title" class="form-control" value="{{ $footer['col4_title'] }}">
                        </div>
                        <label>Menu Links</label>
                        <div id="col4LinksContainer">
                            @foreach($footer['col4_links'] as $link)
                                <div class="link-row">
                                    <input type="text" name="col4_link_titles[]" class="form-control" value="{{ $link['title'] }}" placeholder="Link Label" style="width:40%;">
                                    <input type="text" name="col4_link_urls[]" class="form-control" value="{{ $link['url'] }}" placeholder="/route-path" style="flex:1;">
                                    <button type="button" class="btn-remove-link" onclick="this.closest('.link-row').remove();"><i class="fas fa-trash"></i></button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-xs btn-default" style="margin-top:6px;" onclick="addLinkRow('col4')">
                            <i class="fas fa-plus"></i> Add Link
                        </button>
                    </div>
                </div>

                <!-- Bottom Bar -->
                <div class="panel-footer-box">
                    <div class="panel-footer-head">
                        <h3><i class="fas fa-shield-alt" style="color:#10b981;"></i> Bottom Bar &amp; Badges</h3>
                    </div>
                    <div class="panel-footer-body">
                        <div class="form-group">
                            <label>Copyright Text</label>
                            <input type="text" name="copyright_text" class="form-control" value="{{ $footer['copyright_text'] }}">
                            <span class="help-block" style="font-size:11px; margin-bottom:0;">Supported variables: <code>{store_name}</code>, <code>{year}</code></span>
                        </div>
                        <div class="form-group">
                            <label>Safe Checkout Label</label>
                            <input type="text" name="safe_checkout_text" class="form-control" value="{{ $footer['safe_checkout_text'] }}">
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="show_payment_badges" value="1" {{ !empty($footer['show_payment_badges']) ? 'checked' : '' }}>
                                Display Guaranteed Safe Checkout &amp; SSL Badges
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function addLinkRow(col) {
    const container = document.getElementById(col + 'LinksContainer');
    const row = document.createElement('div');
    row.className = 'link-row';
    row.innerHTML = `
        <input type="text" name="${col}_link_titles[]" class="form-control" placeholder="Link Label" style="width:40%;">
        <input type="text" name="${col}_link_urls[]" class="form-control" placeholder="/route-path" style="flex:1;">
        <button type="button" class="btn-remove-link" onclick="this.closest('.link-row').remove();"><i class="fas fa-trash"></i></button>
    `;
    container.appendChild(row);
}
</script>
@endpush
@endsection
