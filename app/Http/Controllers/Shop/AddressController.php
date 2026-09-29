<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    /**
     * Validation rules shared by store/update.
     */
    private function rules(): array
    {
        return [
            'full_name'        => 'required|string|max:120',
            'mobile_primary'   => 'required|string|regex:/^[0-9]{10}$/',
            'mobile_alternate' => 'nullable|string|regex:/^[0-9]{10}$/',
            'address_line_1'   => 'required|string|max:255',
            'address_line_2'   => 'nullable|string|max:255',
            'city'             => 'required|string|max:120',
            'state'            => 'required|string|max:120',
            'pincode'          => 'required|string|regex:/^[0-9]{6}$/',
            'type'             => 'required|in:home,work',
            'is_default'       => 'sometimes|boolean',
        ];
    }

    /**
     * Validation custom messages.
     */
    private function messages(): array
    {
        return [
            'mobile_primary.regex'   => 'Enter a valid 10-digit mobile number.',
            'mobile_alternate.regex' => 'Enter a valid 10-digit alternate number.',
            'pincode.regex'          => 'Enter a valid 6-digit PIN code.',
        ];
    }

    /**
     * Save a new address for the logged-in customer.
     */
    public function store(Request $request)
    {
        $data = $request->validate($this->rules(), $this->messages());

        $customerId = Auth::guard('customer')->id();
        $makeDefault = (bool) ($data['is_default'] ?? false);

        $address = DB::transaction(function () use ($data, $customerId, $makeDefault) {
            // First address auto-becomes default
            $isFirst = !CustomerAddress::forCustomer($customerId)->exists();
            if ($makeDefault || $isFirst) {
                CustomerAddress::forCustomer($customerId)->update(['is_default' => false]);
            }

            return CustomerAddress::create(array_merge($data, [
                'customer_id' => $customerId,
                'is_default'  => $makeDefault || $isFirst,
            ]));
        });

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'address' => $address]);
        }

        return back()->with('success', 'Address saved.');
    }

    /**
     * Update an existing address (must belong to the current customer).
     */
    public function update(Request $request, CustomerAddress $address)
    {
        $this->ensureOwnership($address);

        $data = $request->validate($this->rules(), $this->messages());

        $makeDefault = (bool) ($data['is_default'] ?? false);
        $customerId = Auth::guard('customer')->id();

        DB::transaction(function () use ($address, $data, $makeDefault, $customerId) {
            if ($makeDefault) {
                CustomerAddress::forCustomer($customerId)
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
                $data['is_default'] = true;
            }
            $address->update($data);
        });

        return back()->with('success', 'Address updated.');
    }

    /**
     * Delete an address.
     */
    public function destroy(CustomerAddress $address)
    {
        $this->ensureOwnership($address);

        $wasDefault = $address->is_default;
        $customerId = $address->customer_id;
        $address->delete();

        // If the default was removed, promote the most recently created address.
        if ($wasDefault) {
            $next = CustomerAddress::forCustomer($customerId)->orderByDesc('id')->first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        return back()->with('success', 'Address removed.');
    }

    /**
     * Mark this address as the customer's default.
     */
    public function setDefault(CustomerAddress $address)
    {
        $this->ensureOwnership($address);

        DB::transaction(function () use ($address) {
            CustomerAddress::forCustomer($address->customer_id)->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return back()->with('success', 'Default address updated.');
    }

    /**
     * 403 if the address does not belong to the currently logged-in customer.
     */
    private function ensureOwnership(CustomerAddress $address): void
    {
        abort_if($address->customer_id !== Auth::guard('customer')->id(), 403);
    }
}
