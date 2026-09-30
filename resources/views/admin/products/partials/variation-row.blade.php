{{-- Single variation row partial (used for edit mode) --}}
<?php $varIndex = $varIndex ?? 0; ?>
<div class="var-card" data-var-id="{{ $var->id }}">
    <div class="var-card-header" onclick="toggleVarBody(this)">
        <div style="display:flex; align-items:center; gap:10px; flex:1;">
            <i class="fas fa-chevron-down var-toggle-icon" style="font-size:11px; color:#888; transition:transform .2s;"></i>
            <span class="var-card-title">{{ $var->name }}</span>
        </div>
        <div class="var-card-actions">
            <button type="button" class="btn btn-xs" style="background:#c0392b; color:#fff; border:none; padding:3px 10px; border-radius:3px; font-size:11px;"
                onclick="removeVariation(this, event)">
                <i class="fas fa-trash"></i> Remove
            </button>
        </div>
    </div>
    <div class="var-card-body">
        <div class="var-grid">
            <div class="form-group var-full">
                <label>Variation Name <span style="color:#c0392b;">*</span></label>
                <input type="text" class="form-control var-name" placeholder="e.g. Red / Large" value="{{ $var->name }}">
            </div>
            <div class="form-group">
                <label>SKU</label>
                <input type="text" class="form-control var-sku" placeholder="SKU code" value="{{ $var->sku ?? '' }}">
            </div>
            <div class="form-group">
                <label>Price <span style="color:#c0392b;">*</span></label>
                <input type="number" class="form-control var-price" placeholder="0.00" step="0.01" min="0" value="{{ $var->price }}">
            </div>
            <div class="form-group">
                <label>Special Price</label>
                <input type="number" class="form-control var-special" placeholder="0.00" step="0.01" min="0"
                    value="{{ $var->special_price ?? '' }}">
            </div>
            <div class="form-group" style="grid-column:1 / -1;">
                <label>Special Price Period <span style="font-weight:400; color:#888;">(optional)</span></label>
                <div class="date-range">
                    <div>
                        <label style="font-size:12px; color:#666;">Start Date</label>
                        <input type="date" class="form-control var-special-start" value="{{ $var->special_price_start?->format('Y-m-d') ?? '' }}">
                    </div>
                    <div>
                        <label style="font-size:12px; color:#666;">End Date</label>
                        <input type="date" class="form-control var-special-end" value="{{ $var->special_price_end?->format('Y-m-d') ?? '' }}">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Stock Status</label>
                <select class="form-control var-stock-status">
                    <option value="in_stock" {{ $var->stock_status === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                    <option value="out_of_stock" {{ $var->stock_status === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>
            <div class="form-group">
                <label>Quantity</label>
                <input type="number" class="form-control var-qty" placeholder="0" min="0" value="{{ $var->qty }}">
            </div>
            <div class="form-group var-full">
                <label>Variation Images</label>
                <div class="img-gallery var-img-gallery">
                    @foreach($var->images as $vimg)
                        <div class="img-thumb {{ $vimg->is_primary ? 'primary' : '' }}" data-id="{{ $vimg->id }}">
                            <img src="{{ $vimg->url }}" alt="">
                            <div class="img-actions">
                                <button type="button" class="btn-img-action btn-img-primary" title="Set Primary" onclick="setPrimaryImg(this)"><i class="fas fa-star"></i></button>
                                <button type="button" class="btn-img-action btn-img-remove" title="Remove" onclick="removeImg(this)"><i class="fas fa-trash"></i></button>
                            </div>
                            <input type="hidden" name="var_{{ $var->id }}_images[]" value="{{ $vimg->id }}">
                        </div>
                    @endforeach
                    <div class="drop-zone var-drop-zone" title="Choose from Media & Files Library" onclick="openMediaPicker({ target: 'variation', varId: '{{ $var->id }}' })">
                        <i class="fas fa-images" style="font-size:18px; color:#3a7bd5;"></i>
                        <span style="font-weight:600; color:#3a7bd5;">Media</span>
                        <input type="file" accept="image/*" multiple class="var-file-input" style="display:none;">
                    </div>
                </div>
                <input type="hidden" class="var-deleted-images" value="">
            </div>
        </div>
    </div>
</div>