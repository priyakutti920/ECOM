{{--
 Reusable add/edit address form.
 Vars:
 - $address     CustomerAddress|null  (null = create)
 - $showCancel  bool (default false)  show a Cancel button that hides the wrap on the account page
 --}}
@php
 $isEdit = !empty($address);
 $action = $isEdit ? route('shop.addresses.update', $address) : route('shop.addresses.store');
@endphp

<form method="POST" action="{{ $action }}" class="address-form">
 @csrf
 @if($isEdit) @method('PUT') @endif

 <div class="form-grid">
 <div class="form-field">
 <label>Full Name <span class="req">*</span></label>
 <input type="text" name="full_name" maxlength="120" required
 value="{{ old('full_name', $address->full_name ?? '') }}">
 @error('full_name') <span class="err">{{ $message }}</span> @enderror
 </div>
 <div class="form-field">
 <label>Mobile Number <span class="req">*</span></label>
 <input type="tel" name="mobile_primary" maxlength="10" pattern="[0-9]{10}" required
 value="{{ old('mobile_primary', $address->mobile_primary ?? '') }}">
 @error('mobile_primary') <span class="err">{{ $message }}</span> @enderror
 </div>
 <div class="form-field">
 <label>Alternate Number</label>
 <input type="tel" name="mobile_alternate" maxlength="10" pattern="[0-9]{10}"
 value="{{ old('mobile_alternate', $address->mobile_alternate ?? '') }}">
 @error('mobile_alternate') <span class="err">{{ $message }}</span> @enderror
 </div>
 <div class="form-field">
 <label>Address Type <span class="req">*</span></label>
 <select name="type" required>
 @php $t = old('type', $address->type ?? 'home'); @endphp
 <option value="home" {{ $t === 'home' ? 'selected' : '' }}>Home</option>
 <option value="work" {{ $t === 'work' ? 'selected' : '' }}>Work</option>
 </select>
 </div>
 <div class="form-field" style="grid-column: 1 / -1;">
 <label>Address Line 1 <span class="req">*</span></label>
 <input type="text" name="address_line_1" maxlength="255" required
 placeholder="House no., building, street, area"
 value="{{ old('address_line_1', $address->address_line_1 ?? '') }}">
 @error('address_line_1') <span class="err">{{ $message }}</span> @enderror
 </div>
 <div class="form-field" style="grid-column: 1 / -1;">
 <label>Address Line 2</label>
 <input type="text" name="address_line_2" maxlength="255"
 placeholder="Landmark, locality"
 value="{{ old('address_line_2', $address->address_line_2 ?? '') }}">
 @error('address_line_2') <span class="err">{{ $message }}</span> @enderror
 </div>
 <div class="form-field">
 <label>City <span class="req">*</span></label>
 <input type="text" name="city" maxlength="120" required
 value="{{ old('city', $address->city ?? '') }}">
 @error('city') <span class="err">{{ $message }}</span> @enderror
 </div>
 <div class="form-field">
 <label>State <span class="req">*</span></label>
 <input type="text" name="state" maxlength="120" required
 value="{{ old('state', $address->state ?? '') }}">
 @error('state') <span class="err">{{ $message }}</span> @enderror
 </div>
 <div class="form-field">
 <label>PIN code <span class="req">*</span></label>
 <input type="text" name="pincode" maxlength="6" pattern="[0-9]{6}" required
 value="{{ old('pincode', $address->pincode ?? '') }}">
 @error('pincode') <span class="err">{{ $message }}</span> @enderror
 </div>
 <div class="form-field" style="justify-content: flex-end;">
 <label style="display:flex; align-items:center; gap:8px; font-weight:500; text-transform:none; letter-spacing:0;">
 <input type="checkbox" name="is_default" value="1"
 {{ old('is_default', $address->is_default ?? false) ? 'checked' : '' }}>
 Make this my default address
 </label>
 </div>
 </div>

 <div style="display: flex; gap: 10px; margin-top: 6px;">
 <button type="submit" class="btn-save">
 <i class="fas fa-save"></i> {{ $isEdit ? 'Update address' : 'Save address' }}
 </button>
 @if(!empty($showCancel))
 <button type="button" class="btn-add" onclick="toggleAddAddress()" style="background:#f0f2f2; border-color:#d5d9d9; color:#555;">
 Cancel
 </button>
 @endif
 </div>
</form>
