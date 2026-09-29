<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\StoreSetting;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['product', 'customer'])->orderByDesc('id');

        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->where('is_approved', false);
            } elseif ($request->status === 'approved') {
                $query->where('is_approved', true);
            }
        }

        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->rating);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('customer_name', 'like', "%{$s}%")
                  ->orWhere('title', 'like', "%{$s}%")
                  ->orWhere('comment', 'like', "%{$s}%")
                  ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$s}%"));
            });
        }

        $reviews = $query->paginate(20)->withQueryString();
        $autoApprove = StoreSetting::getValue('auto_approve_reviews', '0') === '1';

        $counts = [
            'total'    => Review::count(),
            'pending'  => Review::where('is_approved', false)->count(),
            'approved' => Review::where('is_approved', true)->count(),
        ];

        return view('admin.reviews.index', compact('reviews', 'autoApprove', 'counts'));
    }

    public function approve(Review $review)
    {
        $review->update(['is_approved' => true]);
        return back()->with('success', 'Review approved and published.');
    }

    public function reject(Review $review)
    {
        $review->update(['is_approved' => false]);
        return back()->with('success', 'Review set to pending / unapproved.');
    }

    public function destroy(Review $review)
    {
        $review->delete();
        return back()->with('success', 'Review deleted.');
    }

    public function toggleAutoApprove(Request $request)
    {
        $current = StoreSetting::getValue('auto_approve_reviews', '0') === '1';
        $new = !$current;
        StoreSetting::setValue('auto_approve_reviews', $new ? '1' : '0');

        return back()->with('success', 'Review auto-approval ' . ($new ? 'enabled' : 'disabled') . '.');
    }
}
