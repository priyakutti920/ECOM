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

        if ($request->filled('category')) {
            $query->category($request->category);
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
            'total'            => Review::count(),
            'pending'          => Review::where('is_approved', false)->count(),
            'approved'         => Review::where('is_approved', true)->count(),
            'quality'          => Review::category('quality')->count(),
            'customer_service' => Review::category('customer_service')->count(),
            'cart'             => Review::category('cart')->count(),
        ];

        return view('admin.reviews.index', compact('reviews', 'autoApprove', 'counts'));
    }

    public function update(Request $request, Review $review)
    {
        $validated = $request->validate([
            'category'    => ['required', 'string', 'in:quality,customer_service,cart,Quality Review,Customer Service Review,Cart Review'],
            'rating'      => ['required', 'integer', 'between:1,5'],
            'title'       => ['nullable', 'string', 'max:180'],
            'comment'     => ['required', 'string', 'min:3', 'max:2000'],
            'is_approved' => ['nullable', 'boolean'],
        ]);

        $catKey = Review::normalizeCategoryKey($validated['category']);
        $formattedTitle = Review::formatTitleWithCategory($validated['title'] ?? '', $catKey);

        $data = [
            'rating'  => $validated['rating'],
            'title'   => $formattedTitle,
            'comment' => $validated['comment'],
        ];

        if ($request->has('is_approved')) {
            $data['is_approved'] = (bool) $request->is_approved;
        }

        $review->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'success'        => true,
                'message'        => 'Review updated successfully.',
                'review_id'      => $review->id,
                'category'       => $review->category,
                'category_label' => $review->category_label,
                'display_title'  => $review->display_title,
            ]);
        }

        return back()->with('success', 'Review updated successfully.');
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

    /**
     * Perform bulk actions on selected reviews.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
            'action' => 'required|string|in:approve,reject,delete',
        ]);

        $ids = $request->input('ids', []);
        $action = $request->input('action');
        $affected = 0;

        if ($action === 'approve') {
            $affected = Review::whereIn('id', $ids)->update(['is_approved' => true]);
            $msg = "{$affected} review(s) approved and published.";
        } elseif ($action === 'reject') {
            $affected = Review::whereIn('id', $ids)->update(['is_approved' => false]);
            $msg = "{$affected} review(s) set to pending / unapproved.";
        } elseif ($action === 'delete') {
            $reviews = Review::whereIn('id', $ids)->get();
            foreach ($reviews as $r) {
                $r->delete();
                $affected++;
            }
            $msg = "{$affected} review(s) deleted.";
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'affected' => $affected,
            ]);
        }

        return back()->with('success', $msg);
    }
}
