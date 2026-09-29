@extends('layouts.shop')

@section('title', 'My Wishlist — ' . $storeName)
@section('description', 'Your saved products on ' . $storeName)

@section('content')
<div class="wishlist-page-wrap">
    <div class="wishlist-head">
        <h1 class="wishlist-title">
            <i class="fas fa-heart" style="color:#e91e63; margin-right:8px;"></i>
            My Wishlist
        </h1>
        <p class="wishlist-sub" id="wishlist-count-line">Loading…</p>
    </div>

    <div class="wishlist-toolbar">
        <button type="button" id="wl-clear" class="wl-link-btn wl-link-danger" style="display:none;">
            <i class="fas fa-trash"></i> Clear wishlist
        </button>
        <a href="{{ url('/shop') }}" class="wl-link-btn">
            <i class="fas fa-plus"></i> Continue shopping
        </a>
    </div>

    <div id="wl-loading" class="wl-loading">
        <i class="fas fa-spinner fa-spin"></i> Loading your wishlist…
    </div>

    <div id="wl-empty" class="wl-empty" style="display:none;">
        <i class="far fa-heart"></i>
        <h3>Your wishlist is empty</h3>
        <p>Save items you love by tapping the heart icon on any product.</p>
        <a href="{{ url('/shop') }}" class="wl-empty-btn">
            <i class="fas fa-shopping-bag"></i> Start shopping
        </a>
    </div>

    <div id="wl-grid" class="products-grid wishlist-grid" style="display:none;"></div>
</div>
@endsection

@push('styles')
<style>
.wishlist-page-wrap { max-width: 1400px; margin: 0 auto; padding: 16px 12px 40px; }

.wishlist-head { padding: 8px 4px 14px; }
.wishlist-title { font-size: 22px; font-weight: 700; color: var(--amazon-charcoal); margin: 0; line-height: 1.2; }
.wishlist-sub   { font-size: 13px; color: var(--medium-gray); margin: 4px 0 0; }

.wishlist-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    background: #fff;
    border: 1px solid #e7e7e7;
    border-radius: 6px;
    margin-bottom: 14px;
    flex-wrap: wrap;
}
.wl-link-btn {
    background: none;
    border: 1px solid #d5d9d9;
    color: #333;
    padding: 7px 14px;
    border-radius: 100px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.18s;
}
.wl-link-btn:hover { background: #f7f7f7; border-color: #007185; color: #007185; }
.wl-link-danger { color: #c7511f; border-color: #f3c4b6; }
.wl-link-danger:hover { background: #fff5f2; border-color: #c7511f; color: #a04416; }

.wl-loading { text-align: center; padding: 50px 20px; color: var(--medium-gray); font-size: 14px; }
.wl-loading i { font-size: 28px; color: var(--amazon-orange); margin-right: 8px; }

.wl-empty {
    background: #fff;
    border: 1px solid #e7e7e7;
    border-radius: 6px;
    padding: 60px 20px;
    text-align: center;
}
.wl-empty i { font-size: 64px; color: #cbd5e1; display: block; margin-bottom: 14px; }
.wl-empty h3 { font-size: 18px; font-weight: 700; color: var(--amazon-charcoal); margin: 0 0 6px; }
.wl-empty p  { font-size: 14px; color: var(--medium-gray); margin: 0 0 16px; }
.wl-empty-btn {
    display: inline-block;
    background: var(--amazon-orange);
    color: var(--amazon-dark);
    padding: 9px 22px;
    border-radius: 100px;
    font-weight: 700;
    text-decoration: none;
    font-size: 13px;
}
.wl-empty-btn:hover { background: #fa8900; }

.wishlist-grid {
    background: #fff;
    border: 1px solid #e7e7e7;
    border-radius: 6px;
    padding: 14px 10px;
}

/* Move-to-cart callout on wishlist card */
.wl-card-actions { margin-top: 8px; display: flex; gap: 6px; }
.wl-card-actions button { flex: 1; padding: 7px 6px; font-size: 11px; font-weight: 700; border-radius: 100px; cursor: pointer; border: 1px solid; display: flex; align-items: center; justify-content: center; gap: 4px; transition: all 0.2s; }
.wl-add-cart { background: #ffd814; border-color: #fcd200; color: #0F1111; }
.wl-add-cart:hover { background: #f7ca00; }
.wl-remove   { background: #fff; border-color: #d5d9d9; color: #c7511f; }
.wl-remove:hover { background: #fff5f2; border-color: #c7511f; }

@media (max-width: 768px) {
    .wishlist-page-wrap { padding: 12px 8px 32px; }
    .wishlist-title { font-size: 18px; }
    .wishlist-grid { padding: 10px 6px; }
    .wl-empty { padding: 40px 16px; }
    .wl-empty i { font-size: 48px; }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    const loading = document.getElementById('wl-loading');
    const empty   = document.getElementById('wl-empty');
    const grid    = document.getElementById('wl-grid');
    const sub     = document.getElementById('wishlist-count-line');
    const clearBtn= document.getElementById('wl-clear');

    function escapeHtml(s) {
        return (s ?? '').toString().replace(/[&<>"']/g, c => (
            {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]
        ));
    }

    function renderCard(p) {
        const price = Number(p.price || 0);
        const original = Number(p.original_price || 0);
        const discount = (original > 0 && original > price)
            ? Math.round(((original - price) / original) * 100)
            : 0;
        const url = '/product/' + (p.slug || p.id);
        const inStock = p.in_stock !== false;

        return `
        <div class="product-card" data-id="${p.id}" onclick="window.location='${url}'">
            ${discount >= 5 ? `<span class="quick-badge">${discount}% off</span>` : ''}
            <button class="wishlist-btn active" data-product-id="${p.id}" onclick="event.stopPropagation(); wlRemove(${p.id}, this);" title="Remove from wishlist">
                <i class="fas fa-heart"></i>
            </button>
            <div class="product-image">
                ${p.image ? `<img src="${escapeHtml(p.image)}" alt="${escapeHtml(p.name)}" loading="lazy">` : '<i class="fas fa-image" style="color:#cbd5e1;font-size:32px;"></i>'}
            </div>
            <div class="product-info">
                <div class="product-name">${escapeHtml(p.name)}</div>
                <div class="product-price">
                    <span class="current-price">₹${price.toLocaleString('en-IN')}</span>
                    ${discount >= 5 ? `<span class="original-price">₹${original.toLocaleString('en-IN')}</span><span class="discount">(${discount}% off)</span>` : ''}
                </div>
                ${inStock
                    ? `<div class="prime-badge"><i class="fas fa-check-circle"></i> In Stock</div>
                       <div class="wl-card-actions">
                         <button class="wl-add-cart" onclick="event.stopPropagation(); wlMoveToCart(${p.id}, '${escapeHtml(p.name).replace(/'/g,"\\'")}', ${price}, '${escapeHtml(p.image || '').replace(/'/g,"\\'")}', '${escapeHtml(p.slug || p.id).replace(/'/g,"\\'")}');" title="Add to Cart">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                         </button>
                         <button class="wl-remove" onclick="event.stopPropagation(); wlRemove(${p.id});" title="Remove">
                            <i class="fas fa-times"></i> Remove
                         </button>
                       </div>`
                    : `<div class="prime-badge" style="color:#c7511f;"><i class="fas fa-times-circle"></i> Out of Stock</div>
                       <div class="wl-card-actions">
                         <button class="wl-remove" style="flex:1;" onclick="event.stopPropagation(); wlRemove(${p.id});" title="Remove">
                            <i class="fas fa-times"></i> Remove
                         </button>
                       </div>`
                }
            </div>
        </div>`;
    }

    async function load() {
        let ids = (typeof getWishlist === 'function' ? getWishlist() : []);

        // Sync with server-side database wishlist if logged in
        try {
            const authWlRes = await fetch('/api/wishlist/items', { credentials: 'same-origin' });
            if (authWlRes.ok) {
                const authData = await authWlRes.json();
                if (authData && Array.isArray(authData.ids) && authData.ids.length > 0) {
                    ids = Array.from(new Set([...ids.map(Number), ...authData.ids.map(Number)]));
                    if (typeof saveWishlist === 'function') {
                        saveWishlist(ids);
                    } else {
                        localStorage.setItem('store_wishlist', JSON.stringify(ids));
                    }
                }
            }
        } catch (err) {
            // fallback to local ids
        }

        loading.style.display = 'block';
        grid.style.display = 'none';
        empty.style.display = 'none';

        if (!ids || ids.length === 0) {
            loading.style.display = 'none';
            empty.style.display = 'block';
            sub.textContent = '0 items saved';
            clearBtn.style.display = 'none';
            return;
        }

        try {
            const res = await fetch('/api/cart/items', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ ids: ids.map(Number) })
            });
            const data = await res.json();
            const items = (data && data.success && Array.isArray(data.data)) ? data.data : [];

            loading.style.display = 'none';
            if (items.length === 0) {
                empty.style.display = 'block';
                sub.textContent = 'Saved items are no longer available';
                clearBtn.style.display = 'inline-flex';
                return;
            }

            sub.textContent = items.length + ' ' + (items.length === 1 ? 'item' : 'items') + ' saved';
            clearBtn.style.display = 'inline-flex';
            grid.innerHTML = items.map(renderCard).join('');
            grid.style.display = 'grid';
        } catch (e) {
            console.error(e);
            loading.style.display = 'none';
            empty.style.display = 'block';
            sub.textContent = 'Failed to load wishlist';
        }
    }

    window.wlRemove = function (id, btn) {
        if (typeof toggleWishlist === 'function') {
            // toggleWishlist already removes the id and updates the heart icon
            // but we don't want to show the toast here. Just call the storage logic.
            const list = getWishlist().filter(x => x !== id);
            if (typeof saveWishlist === 'function') {
                saveWishlist(list);
            } else {
                localStorage.setItem('store_wishlist', JSON.stringify(list));
                localStorage.setItem('nellai_wishlist', JSON.stringify(list));
            }
            if (btn) { btn.classList.remove('active'); btn.querySelector('i')?.classList.replace('fas','far'); }
        } else {
            const list = (typeof getWishlist === 'function' ? getWishlist() : JSON.parse(localStorage.getItem('store_wishlist') || localStorage.getItem('nellai_wishlist') || '[]')).filter(x => x !== id);
            if (typeof saveWishlist === 'function') {
                saveWishlist(list);
            } else {
                localStorage.setItem('store_wishlist', JSON.stringify(list));
                localStorage.setItem('nellai_wishlist', JSON.stringify(list));
            }
        }
        // Remove the card from view
        const card = grid.querySelector(`.product-card[data-id="${id}"]`);
        if (card) card.remove();
        if (grid.children.length === 0) {
            grid.style.display = 'none';
            empty.style.display = 'block';
            sub.textContent = '0 items saved';
            clearBtn.style.display = 'none';
        } else {
            sub.textContent = grid.children.length + ' ' + (grid.children.length === 1 ? 'item' : 'items') + ' saved';
        }
        if (typeof showToast === 'function') showToast('Removed from wishlist', 'info');
    };

    window.wlMoveToCart = function (id, name, price, image, slug) {
        if (typeof addToCart === 'function') {
            addToCart(id, 1, name, price, image, slug);
            if (typeof openCartSidebar === 'function') openCartSidebar();
        } else {
            // Fallback storage
            const list = JSON.parse(localStorage.getItem('store_cart') || localStorage.getItem('nellai_cart') || '[]');
            const existing = list.find(c => c.id === id);
            if (existing) existing.qty = (existing.qty || 1) + 1;
            else list.push({ id, qty: 1, name, price, image, slug });
            localStorage.setItem('store_cart', JSON.stringify(list));
            localStorage.setItem('nellai_cart', JSON.stringify(list));
        }
    };

    clearBtn.addEventListener('click', function () {
        if (!confirm('Clear all items from your wishlist?')) return;
        localStorage.setItem('store_wishlist', '[]');
        localStorage.setItem('nellai_wishlist', '[]');
        load();
        if (typeof showToast === 'function') showToast('Wishlist cleared', 'info');
    });

    document.addEventListener('DOMContentLoaded', load);
})();
</script>
@endpush
