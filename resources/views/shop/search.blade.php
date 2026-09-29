@extends('layouts.shop')

@section('title', 'Search: ' . $q . ' — ' . $storeName)

@section('content')
<div style="max-width:1400px; margin: 0 auto; padding: 16px;">

 <div style="background:#fff; padding: 16px 20px; border-radius: 4px; margin-bottom: 16px; border: 1px solid #e7e7e7;">
 <h1 style="font-size: 20px; font-weight: 700; color: var(--amazon-charcoal); margin:0 0 4px;">
 @if($q !== '')
 Search results for "{{ $q }}"
 @else
 Search our store
 @endif
 </h1>
 @if($q !== '')
 <p style="color: var(--medium-gray); font-size: 13px; margin:0;">{{ $products->total() }} {{ Str::plural('result', $products->total()) }} found</p>
 @else
 <p style="color: var(--medium-gray); font-size: 13px; margin:0;">Use the search bar above to find products.</p>
 @endif
 </div>

 @if($q !== '' && $products->count() > 0)
 <div style="background:#fff; padding: 12px; border-radius: 4px; border: 1px solid #e7e7e7;">
 <div class="products-grid" style="padding: 8px 0;">
 @foreach($products as $product)
 @include('shop.partials.product-card', ['product' => $product])
 @endforeach
 </div>
 </div>
 <div style="margin-top: 20px; display:flex; justify-content:center;">
 {{ $products->links('pagination::simple-default') }}
 </div>
 @elseif($q !== '')
 <div style="background:#fff; padding:60px 20px; border-radius:4px; border:1px solid #e7e7e7; text-align:center;">
 <i class="fas fa-search" style="font-size: 64px; color: #cbd5e1; margin-bottom: 16px; display:block;"></i>
 <h3 style="font-size: 18px; font-weight: 700; color: var(--amazon-charcoal); margin-bottom: 8px;">No results for "{{ $q }}"</h3>
 <p style="color: var(--medium-gray); font-size: 14px;">Try different keywords or browse our categories.</p>
 <a href="{{ url('/') }}" style="display: inline-block; margin-top: 16px; padding: 10px 24px; background: var(--amazon-orange); color: var(--amazon-dark); border-radius: 18px; font-weight: 600; text-decoration: none;">Browse All Products</a>
 </div>
 @endif

</div>
@endsection
