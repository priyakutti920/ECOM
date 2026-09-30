@extends('layouts.admin')

@section('title', 'Inventory & Stock Management')

@push('styles')
<style>
    .inv-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; }
    .inv-header h2 { margin: 0; font-weight: 700; color: #111827; font-size: 22px; }

    /* KPI Cards */
    .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px; }
    .kpi-card { background: #fff; padding: 18px 20px; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px; }
    .kpi-icon { width: 48px; height: 48px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
    .kpi-icon.blue { background: #eff6ff; color: #2563eb; }
    .kpi-icon.amber { background: #fffbeb; color: #d97706; }
    .kpi-icon.red { background: #fef2f2; color: #dc2626; }
    .kpi-icon.green { background: #f0fdf4; color: #16a34a; }
    .kpi-info .val { font-size: 24px; font-weight: 700; color: #111827; line-height: 1.1; }
    .kpi-info .lbl { font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 3px; }

    /* Filter Tabs & Search Bar */
    .inv-toolbar { background: #fff; padding: 16px; border-radius: 8px; border: 1px solid #e5e7eb; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
    .inv-tabs { display: flex; gap: 6px; }
    .inv-tab { padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none; color: #4b5563; background: #f3f4f6; transition: all 0.15s; }
    .inv-tab:hover { background: #e5e7eb; color: #111827; }
    .inv-tab.active { background: #2563eb; color: #fff; }
    .inv-search { display: flex; gap: 8px; }
    .inv-search input { padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; width: 260px; outline: none; }
    .inv-search input:focus { border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,0.15); }
    .inv-search button { padding: 8px 16px; background: #374151; color: #fff; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; }

    /* Tables */
    .inv-card { background: #fff; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 24px; overflow: hidden; }
    .inv-card-head { padding: 14px 18px; border-bottom: 1px solid #e5e7eb; background: #f9fafb; font-weight: 700; font-size: 14px; color: #1f2937; display: flex; justify-content: space-between; align-items: center; }
    .inv-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .inv-table th { background: #fafafa; padding: 12px 16px; text-align: left; font-weight: 600; color: #4b5563; border-bottom: 1px solid #e5e7eb; font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.4px; }
    .inv-table td { padding: 12px 16px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; color: #1f2937; }
    .inv-table tr:last-child td { border-bottom: none; }
    .inv-table tr:hover td { background: #fbfcfd; }

    /* Badges */
    .badge-stock { display: inline-flex; align-items: center; gap: 5px; padding: 4px 9px; border-radius: 100px; font-size: 11.5px; font-weight: 700; }
    .badge-in-stock { background: #dcfce7; color: #15803d; }
    .badge-low-stock { background: #fef3c7; color: #b45309; }
    .badge-out-of-stock { background: #fee2e2; color: #b91c1c; }

    .movement-pill { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 10.5px; font-weight: 700; letter-spacing: 0.3px; }
    .movement-PURCHASE { background: #fee2e2; color: #991b1b; }
    .movement-RESTOCK { background: #dcfce7; color: #166534; }
    .movement-RETURN { background: #eff6ff; color: #1e40af; }
    .movement-CANCELLED_ORDER { background: #fef3c7; color: #92400e; }
    .movement-ADJUSTMENT { background: #f3e8ff; color: #6b21a8; }

    /* Modals */
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 10000; }
    .modal-overlay.open { display: flex; }
    .inv-modal { background: #fff; width: 100%; max-width: 480px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); overflow: hidden; }
    .inv-modal-head { padding: 16px 20px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; font-weight: 700; font-size: 15px; }
    .inv-modal-body { padding: 20px; display: flex; flex-direction: column; gap: 14px; }
    .inv-modal-foot { padding: 14px 20px; background: #f9fafb; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 8px; }
    .form-group label { display: block; font-size: 12.5px; font-weight: 600; color: #374151; margin-bottom: 5px; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; outline: none; }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,0.15); }
</style>
@endpush

@section('content')
<div class="inv-header">
    <div>
        <h2><i class="fas fa-warehouse" style="color:#2563eb; margin-right:8px;"></i> Inventory & Stock Management</h2>
        <div style="font-size:13px; color:#6b7280; margin-top:3px;">Monitor real-time warehouse stock levels, low inventory alerts, and stock ledger movements.</div>
    </div>
</div>

@if(session('success'))
    <div style="margin-bottom:16px; padding:12px 16px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px; color:#166534; font-size:13px; font-weight:600;">
        <i class="fas fa-check-circle" style="margin-right:6px;"></i> {{ session('success') }}
    </div>
@endif

<!-- KPI Metrics -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-icon blue"><i class="fas fa-boxes"></i></div>
        <div class="kpi-info">
            <div class="val">{{ number_format($totalProducts) }}</div>
            <div class="lbl">Total Products</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon amber"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="kpi-info">
            <div class="val">{{ number_format($lowStockCount) }}</div>
            <div class="lbl">Low Stock Alerts</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon red"><i class="fas fa-times-circle"></i></div>
        <div class="kpi-info">
            <div class="val">{{ number_format($outOfStockCount) }}</div>
            <div class="lbl">Out of Stock</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon green"><i class="fas fa-clipboard-list"></i></div>
        <div class="kpi-info">
            <div class="val">{{ number_format($recentMovements->count()) }}</div>
            <div class="lbl">Recent Logs</div>
        </div>
    </div>
</div>

<!-- Toolbar: Tabs & Search -->
<div class="inv-toolbar">
    <div class="inv-tabs">
        <a href="{{ route('admin.inventory.index', ['status' => 'all', 'q' => $search]) }}" class="inv-tab {{ $status === 'all' ? 'active' : '' }}">
            All Products
        </a>
        <a href="{{ route('admin.inventory.index', ['status' => 'low_stock', 'q' => $search]) }}" class="inv-tab {{ $status === 'low_stock' ? 'active' : '' }}">
            <i class="fas fa-exclamation-circle"></i> Low Stock ({{ $lowStockCount }})
        </a>
        <a href="{{ route('admin.inventory.index', ['status' => 'out_of_stock', 'q' => $search]) }}" class="inv-tab {{ $status === 'out_of_stock' ? 'active' : '' }}">
            <i class="fas fa-times-circle"></i> Out of Stock ({{ $outOfStockCount }})
        </a>
    </div>

    <form method="get" action="{{ route('admin.inventory.index') }}" class="inv-search">
        <input type="hidden" name="status" value="{{ $status }}">
        <input type="text" name="q" value="{{ $search }}" placeholder="Search by product name or SKU...">
        <button type="submit"><i class="fas fa-search"></i> Search</button>
        @if(!empty($search))
            <a href="{{ route('admin.inventory.index', ['status' => $status]) }}" class="btn" style="background:#e5e7eb; color:#374151; padding:8px 12px; border-radius:6px; text-decoration:none; font-size:13px;">Clear</a>
        @endif
    </form>
</div>

<!-- Products Inventory Table -->
<div class="inv-card">
    <div class="inv-card-head">
        <span><i class="fas fa-list"></i> Product Inventory Levels</span>
        <span style="font-size:12px; color:#6b7280; font-weight:normal;">Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }}</span>
    </div>
    <div style="overflow-x:auto;">
        <table class="inv-table">
            <thead>
                <tr>
                    <th style="width:50px;"></th>
                    <th>Product & SKU</th>
                    <th>Variations</th>
                    <th>Current Stock</th>
                    <th>Low Stock Alert At</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    @php
                        $qty = (int) $product->qty;
                        $thresh = (int) ($product->low_stock_threshold ?? 5);
                        $isLow = ($qty <= $thresh && $qty > 0);
                        $isOos = ($product->stock_status === 'out_of_stock' || $qty <= 0);
                    @endphp
                    <tr>
                        <td>
                            @if($product->image_url)
                                <img src="{{ $product->image_url }}" alt="" style="width:40px; height:40px; object-fit:cover; border-radius:4px; border:1px solid #e5e7eb;">
                            @else
                                <div style="width:40px; height:40px; background:#f3f4f6; border-radius:4px; display:flex; align-items:center; justify-content:center; color:#9ca3af;"><i class="fas fa-image"></i></div>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight:600; color:#111827;">{{ $product->name }}</div>
                            <div style="font-size:11.5px; color:#6b7280; margin-top:2px;">SKU: {{ $product->sku ?: 'SKU-' . $product->id }}</div>
                        </td>
                        <td>
                            @if($product->variations->isNotEmpty())
                                <div style="display:flex; flex-direction:column; gap:4px;">
                                    @foreach($product->variations as $var)
                                        @php
                                            $vQty = (int) $var->qty;
                                            $vThresh = (int) ($var->low_stock_threshold ?? 5);
                                            $vLow = ($vQty <= $vThresh && $vQty > 0);
                                            $vOos = ($var->stock_status === 'out_of_stock' || $vQty <= 0);
                                        @endphp
                                        <div style="font-size:12px; display:flex; align-items:center; justify-content:space-between; gap:8px; padding:3px 6px; background:#f9fafb; border-radius:4px; border:1px solid #f3f4f6;">
                                            <span><strong>{{ $var->name }}</strong> ({{ $var->sku ?: '—' }})</span>
                                            <span>
                                                <strong>{{ $vQty }}</strong> units
                                                @if($vOos)
                                                    <span style="color:#dc2626; font-weight:700;">(OOS)</span>
                                                @elseif($vLow)
                                                    <span style="color:#d97706; font-weight:700;">(Low)</span>
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span style="color:#9ca3af; font-size:12px; font-style:italic;">Standard (No variations)</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-size:15px; font-weight:700; color:#111827;">{{ $qty }}</span>
                            <span style="font-size:12px; color:#6b7280;">units</span>
                        </td>
                        <td>
                            <span style="font-size:13px; color:#6b7280;">≤ {{ $thresh }} units</span>
                        </td>
                        <td>
                            @if($isOos)
                                <span class="badge-stock badge-out-of-stock"><i class="fas fa-times-circle"></i> Out of Stock</span>
                            @elseif($isLow)
                                <span class="badge-stock badge-low-stock"><i class="fas fa-exclamation-triangle"></i> Low Stock</span>
                            @else
                                <span class="badge-stock badge-in-stock"><i class="fas fa-check-circle"></i> In Stock</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <button type="button" 
                                    onclick="openAdjustModal({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $qty }}, {{ json_encode($product->variations->map(fn($v) => ['id' => $v->id, 'name' => $v->name, 'qty' => (int)$v->qty])) }})"
                                    style="padding:6px 12px; background:#2563eb; color:#fff; font-size:12px; font-weight:600; border:none; border-radius:5px; cursor:pointer;">
                                <i class="fas fa-sliders-h"></i> Restock / Adjust
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:30px; color:#6b7280; font-style:italic;">
                            No products match your inventory filter or search criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
        <div style="padding:14px 18px; border-top:1px solid #e5e7eb;">
            {{ $products->links() }}
        </div>
    @endif
</div>

<!-- Stock Movements Audit Ledger -->
<div class="inv-card">
    <div class="inv-card-head">
        <span><i class="fas fa-history"></i> Stock Movements Audit Ledger (Last 25 Events)</span>
    </div>
    <div style="overflow-x:auto;">
        <table class="inv-table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Product / SKU</th>
                    <th>Event Type</th>
                    <th>Quantity Change</th>
                    <th>Stock (Before &rarr; After)</th>
                    <th>Reference</th>
                    <th>Reason / Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentMovements as $move)
                    <tr>
                        <td style="font-size:12px; color:#6b7280; white-space:nowrap;">
                            {{ $move->created_at ? $move->created_at->format('d M Y, h:i A') : '—' }}
                        </td>
                        <td>
                            <div style="font-weight:600; color:#111827;">{{ $move->product?->name ?: 'Product #' . $move->product_id }}</div>
                            @if($move->variation)
                                <div style="font-size:11.5px; color:#2563eb;">Variation: {{ $move->variation->name }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="movement-pill movement-{{ $move->type }}">{{ $move->type }}</span>
                        </td>
                        <td>
                            @if($move->quantity > 0)
                                <span style="color:#16a34a; font-weight:700; font-size:14px;">+{{ $move->quantity }}</span>
                            @else
                                <span style="color:#dc2626; font-weight:700; font-size:14px;">{{ $move->quantity }}</span>
                            @endif
                        </td>
                        <td>
                            <span style="color:#6b7280;">{{ $move->previous_qty }}</span>
                            <span style="margin: 0 4px;">&rarr;</span>
                            <strong style="color:#111827;">{{ $move->new_qty }}</strong>
                        </td>
                        <td style="font-size:12px;">
                            <span style="color:#4b5563; font-weight:600;">{{ ucfirst(str_replace('_', ' ', $move->reference_type ?: 'Manual')) }}</span>
                            @if($move->reference_id)
                                <span style="color:#6b7280;">#{{ $move->reference_id }}</span>
                            @endif
                        </td>
                        <td style="font-size:12px; color:#4b5563;">
                            {{ $move->reason ?: '—' }}
                            @if($move->user)
                                <span style="color:#9ca3af; font-size:11px;">(by {{ $move->user->name }})</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:24px; color:#9ca3af; font-style:italic;">
                            No stock movement records logged yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Stock Adjustment Modal -->
<div class="modal-overlay" id="adjustModalOverlay">
    <div class="inv-modal">
        <form method="post" action="{{ route('admin.inventory.adjust') }}">
            @csrf
            <div class="inv-modal-head">
                <span id="adjustModalTitle">Adjust Stock</span>
                <button type="button" onclick="closeAdjustModal()" style="background:none; border:none; font-size:18px; cursor:pointer; color:#9ca3af;">&times;</button>
            </div>
            <div class="inv-modal-body">
                <input type="hidden" name="product_id" id="modal_product_id">

                <div class="form-group">
                    <label>Product</label>
                    <input type="text" id="modal_product_name" readonly style="background:#f9fafb; font-weight:600;">
                </div>

                <div class="form-group" id="modal_variation_group" style="display:none;">
                    <label>Target Variation</label>
                    <select name="variation_id" id="modal_variation_id" onchange="onModalVariationChange()">
                        <option value="">Base Product Stock</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Adjustment Type</label>
                    <select name="type" id="modal_type">
                        <option value="RESTOCK">RESTOCK (Warehouse inbound / supplier shipment)</option>
                        <option value="ADJUSTMENT">ADJUSTMENT (Stock audit recount / discrepancy correction)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>New Total Stock Quantity</label>
                    <input type="number" name="new_qty" id="modal_new_qty" min="0" required placeholder="Enter updated inventory count">
                    <small style="color:#6b7280; font-size:11.5px; margin-top:3px; display:block;">Current count: <strong id="modal_current_qty">0</strong> units</small>
                </div>

                <div class="form-group">
                    <label>Reason / Audit Note</label>
                    <input type="text" name="reason" maxlength="255" placeholder="e.g. Received shipment PO-884, Cycle count audit">
                </div>
            </div>
            <div class="inv-modal-foot">
                <button type="button" onclick="closeAdjustModal()" style="padding:8px 16px; background:#f3f4f6; color:#4b5563; border:1px solid #d1d5db; border-radius:6px; font-weight:600; cursor:pointer;">Cancel</button>
                <button type="submit" style="padding:8px 18px; background:#2563eb; color:#fff; border:none; border-radius:6px; font-weight:600; cursor:pointer;">Save Stock Level</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let currentModalVariations = [];

    function openAdjustModal(productId, productName, currentQty, variations) {
        document.getElementById('modal_product_id').value = productId;
        document.getElementById('modal_product_name').value = productName;
        document.getElementById('modal_current_qty').textContent = currentQty;
        document.getElementById('modal_new_qty').value = currentQty;

        const varGroup = document.getElementById('modal_variation_group');
        const varSelect = document.getElementById('modal_variation_id');

        currentModalVariations = variations || [];
        varSelect.innerHTML = '<option value="">Base Product Stock (' + currentQty + ' units)</option>';

        if (currentModalVariations.length > 0) {
            currentModalVariations.forEach(v => {
                const opt = document.createElement('option');
                opt.value = v.id;
                opt.textContent = v.name + ' (' + v.qty + ' units)';
                varSelect.appendChild(opt);
            });
            varGroup.style.display = 'block';
        } else {
            varGroup.style.display = 'none';
        }

        document.getElementById('adjustModalOverlay').classList.add('open');
    }

    function onModalVariationChange() {
        const sel = document.getElementById('modal_variation_id');
        const varId = sel.value;
        if (!varId) {
            document.getElementById('modal_current_qty').textContent = document.getElementById('modal_new_qty').dataset.baseQty || '0';
            return;
        }
        const found = currentModalVariations.find(v => String(v.id) === String(varId));
        if (found) {
            document.getElementById('modal_current_qty').textContent = found.qty;
            document.getElementById('modal_new_qty').value = found.qty;
        }
    }

    function closeAdjustModal() {
        document.getElementById('adjustModalOverlay').classList.remove('open');
    }
</script>
@endpush
@endsection
