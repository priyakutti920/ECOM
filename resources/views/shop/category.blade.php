@extends('layouts.shop')

@section('title', $category->name . ' — ' . $storeName)
@section('description', 'Shop ' . $category->name . ' at ' . $storeName . '. Great prices, fast delivery.')

@section('content')
<div style="max-width:1400px; margin: 0 auto; padding: 16px;">

 <!-- Breadcrumb -->
 <div style="font-size:13px; color: var(--medium-gray); margin-bottom: 16px; padding: 10px 0;">
 <a href="{{ url('/') }}" style="color: var(--amazon-blue); text-decoration:none;">Home</a>
 <i class="fas fa-chevron-right" style="font-size:9px; margin: 0 4px; color:#cbd5e1;"></i>
 <span>{{ $category->name }}</span>
 </div>

 <!-- Header -->
 <div style="background: #fff; padding: 16px 20px; border-radius: 4px; margin-bottom: 16px; border: 1px solid #e7e7e7; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap: 12px;">
 <div>
 <h1 style="font-size: 24px; font-weight: 700; color: var(--amazon-charcoal); margin:0 0 4px;">{{ $category->name }}</h1>
 <p style="color: var(--medium-gray); font-size: 13px; margin:0;">{{ $products->total() }} {{ Str::plural('product', $products->total()) }}</p>
 </div>
 <form method="GET" style="display:flex; gap: 8px; align-items:center;">
 <label style="font-size: 13px; color: var(--medium-gray);">Sort by:</label>
 <select name="sort" onchange="this.form.submit()" style="padding:8px 12px; border:1px solid #d5d9d9; border-radius: 4px; font-size: 13px; cursor:pointer; background:#fff;">
 <option value="latest" {{ $sort == 'latest' ? 'selected' : '' }}>Latest</option>
 <option value="price_low" {{ $sort == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
 <option value="price_high" {{ $sort == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
 <option value="name" {{ $sort == 'name' ? 'selected' : '' }}>Name</option>
 </select>
 </form>
 </div>

 <!-- Subcategories -->
 @if($subcategories->count() > 0)
 <div style="background:#fff; padding:12px 16px; border-radius:4px; margin-bottom:16px; border:1px solid #e7e7e7;">
 <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
 <div style="font-size: 13px; font-weight: 600; color: var(--amazon-charcoal);">Subcategories:</div>
 @if(!empty($isRoot) && $products->count() > 0)
 <span style="font-size: 11px; color: var(--medium-gray);">Showing all products including sub-categories</span>
 @endif
 </div>
 <div style="display:flex; flex-wrap:wrap; gap: 8px;">
 @foreach($subcategories as $sub)
 <a href="{{ url('/category/' . ($sub->slug ?: $sub->id)) }}" style="padding: 6px 12px; background: var(--light-gray); border-radius: 18px; font-size: 12px; color: var(--amazon-charcoal); text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='var(--amazon-orange)'; this.style.color='#fff';" onmouseout="this.style.background='var(--light-gray)'; this.style.color='var(--amazon-charcoal)';">{{ $sub->name }}</a>
 @endforeach
 </div>
 </div>
 @endif

 <!-- Products -->
 @if($products->count() > 0)
 <div style="background:#fff; padding: 12px; border-radius: 4px; border: 1px solid #e7e7e7;">
 <div class="products-grid" style="padding: 8px 0;">
 @foreach($products as $product)
 @include('shop.partials.product-card', ['product' => $product])
 @endforeach
 </div>
 </div>

 <!-- Pagination -->
 <div style="margin-top: 20px; display:flex; justify-content:center;">
 {{ $products->links('pagination::simple-default') }}
 </div>
 @else
 <div style="background:#fff; padding:60px 20px; border-radius:4px; border:1px solid #e7e7e7; text-align:center;">
 <i class="fas fa-box-open" style="font-size: 64px; color: #cbd5e1; margin-bottom: 16px; display:block;"></i>
 <h3 style="font-size: 18px; font-weight: 700; color: var(--amazon-charcoal); margin-bottom: 8px;">No products in this category yet</h3>
 <p style="color: var(--medium-gray); font-size: 14px;">Check back soon for new arrivals!</p>
 <a href="{{ url('/') }}" style="display: inline-block; margin-top: 16px; padding: 10px 24px; background: var(--amazon-orange); color: var(--amazon-dark); border-radius: 18px; font-weight: 600; text-decoration: none;">Browse All Products</a>
 </div>
 @endif

</div>
@endsection
