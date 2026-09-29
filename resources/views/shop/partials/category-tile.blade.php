{{--
  Category round-icon tile.
  Usage: @include('shop.partials.category-tile', ['cat' => $cat, 'isActive' => false])

  - If $cat->image_url is set, render the image inside a circle.
  - Otherwise render a sensible FontAwesome icon inside a colored circle.
  - Category name is shown below the circle.
--}}
@php
    use Illuminate\Support\Str;

    // Map a few common category names -> meaningful icons.
    $iconMap = [
        'electronics'    => 'fa-tv',
        'mobile'         => 'fa-mobile-screen',
        'mobiles'        => 'fa-mobile-screen',
        'phones'         => 'fa-mobile-screen',
        'fashion'        => 'fa-shirt',
        'clothing'       => 'fa-shirt',
        'apparel'        => 'fa-shirt',
        'shoes'          => 'fa-shoe-prints',
        'footwear'       => 'fa-shoe-prints',
        'beauty'         => 'fa-spray-can-sparkles',
        'cosmetics'      => 'fa-spray-can-sparkles',
        'home'           => 'fa-house',
        'kitchen'        => 'fa-utensils',
        'grocery'        => 'fa-basket-shopping',
        'groceries'      => 'fa-basket-shopping',
        'toys'           => 'fa-puzzle-piece',
        'books'          => 'fa-book',
        'sports'         => 'fa-dumbbell',
        'fitness'        => 'fa-dumbbell',
        'accessories'    => 'fa-headphones',
        'audio'          => 'fa-headphones',
        'jewellery'      => 'fa-gem',
        'jewelry'        => 'fa-gem',
        'watches'        => 'fa-clock',
        'bags'           => 'fa-bag-shopping',
        'health'         => 'fa-heart-pulse',
        'baby'           => 'fa-baby',
        'pets'           => 'fa-paw',
        'automotive'     => 'fa-car',
        'garden'         => 'fa-seedling',
        'music'          => 'fa-music',
        'gaming'         => 'fa-gamepad',
        'laptops'        => 'fa-laptop',
        'computer'       => 'fa-desktop',
        'cameras'        => 'fa-camera',
    ];

    $hasImage = !empty($cat->image_url);
    $nameKey  = Str::of($cat->name ?? '')->lower()->trim()->toString();
    $icon     = $iconMap[$nameKey] ?? 'fa-tag';

    // Pick a soft background color per category (deterministic by id so it's stable).
    $palette = [
        ['#FFE0B2', '#E65100'], // orange
        ['#E1F5FE', '#0277BD'], // blue
        ['#F8BBD0', '#AD1457'], // pink
        ['#C8E6C9', '#2E7D32'], // green
        ['#D1C4E9', '#4527A0'], // purple
        ['#FFECB3', '#FF6F00'], // amber
        ['#B2EBF2', '#006064'], // teal
        ['#FFCCBC', '#BF360C'], // deep orange
        ['#DCEDC8', '#33691E'], // light green
        ['#CFD8DC', '#37474F'], // blue grey
    ];
    $paletteIdx = (int) ($cat->id ?? 0) % count($palette);
    [$bg, $fg]  = $palette[$paletteIdx];

    $href = $href ?? url('/shop?category=' . ($cat->id ?? ''));
@endphp

<a href="{{ $href }}" class="cat-tile {{ !empty($isActive) ? 'is-active' : '' }}" title="{{ $cat->name ?? 'Category' }}">
    <div class="cat-tile-circle" @if(!$hasImage) style="background:{{ $bg }}; color:{{ $fg }};" @endif>
        @if($hasImage)
            <img src="{{ $cat->image_url }}" alt="{{ $cat->name }}" loading="lazy">
        @else
            <i class="fas {{ $icon }}"></i>
        @endif
    </div>
    <div class="cat-tile-name">{{ $cat->name ?? 'Category' }}</div>
</a>
