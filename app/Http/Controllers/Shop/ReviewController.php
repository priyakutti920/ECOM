<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Submit a customer review for a product.
     */
    public function store(Request $request, Product $product)
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return back()->withErrors(['review' => 'You must be logged in to submit a review.']);
        }

        $validated = $request->validate([
            'rating'  => ['required', 'integer', 'between:1,5'],
            'title'   => ['nullable', 'string', 'max:200'],
            'comment' => ['required', 'string', 'min:5', 'max:2000'],
        ], [
            'rating.required'  => 'Please select a star rating.',
            'rating.between'   => 'Rating must be between 1 and 5 stars.',
            'comment.required' => 'Please enter your review text.',
            'comment.min'      => 'Review must be at least 5 characters.',
        ]);

        // Duplicate prevention: check if customer already reviewed this product
        $existing = Review::where('product_id', $product->id)
            ->where('customer_id', $customer->id)
            ->first();

        if ($existing) {
            return back()->withErrors(['review' => 'You have already submitted a review for this product. Thank you!']);
        }

        // Verified purchase check
        $isVerifiedPurchase = Order::where('customer_id', $customer->id)
            ->where('payment_status', 'paid')
            ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
            ->exists();

        // Check if store auto-approves reviews
        $autoApprove = StoreSetting::getValue('auto_approve_reviews', '0') === '1';

        Review::create([
            'product_id'           => $product->id,
            'customer_id'          => $customer->id,
            'customer_name'        => $customer->name,
            'customer_email'       => $customer->email,
            'rating'               => $validated['rating'],
            'title'                => $validated['title'] ?? null,
            'comment'              => $validated['comment'],
            'is_approved'          => $autoApprove,
            'is_verified_purchase' => $isVerifiedPurchase,
        ]);

        $message = $autoApprove
            ? 'Thank you! Your review has been published.'
            : 'Thank you! Your review has been submitted and will appear once approved by our team.';

        return back()->with('success', $message);
    }
}
