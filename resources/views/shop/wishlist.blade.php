@extends('layouts.shop')

@section('title', 'My Wishlist — ' . $storeName)
@section('description', 'Your saved favorite items on ' . $storeName)

@section('content')
<div class="wishlist-page-wrap">
    <div class="wishlist-head">
        <div>
            <h1 class="wishlist-title">
                <i class="fas fa-heart" style="color: #f43f5e; margin-right: 8px;"></i>
                My Wishlist
            </h1>
            <p class="wishlist-sub" id="wishlist-count-line">Loading your saved items…</p>
        </div>
        <div class="wishlist-actions-bar">
            <button type="button" id="wl-clear" class="wl-link-btn wl-link-danger" style="display:none;">
                <i class="fas fa-trash-alt"></i> Clear All
            </button>
            <a href="{{ url('/products') }}" class="wl-link-btn">
                <i class="fas fa-arrow-left"></i> Continue Shopping
            </a>
        </div>
    </div>

    <!-- Loading State -->
    <div id="wl-loading" class="wl-loading">
        <i class="fas fa-spinner fa-spin"></i> Loading your wishlist items…
    </div>

    <!-- Empty State -->
    <div id="wl-empty" class="wl-empty" style="display:none;">
        <div class="wl-empty-icon"><i class="far fa-heart"></i></div>
        <h3>Your wishlist is empty</h3>
        <p>Explore thousands of products and save your favorites by tapping the heart icon!</p>
        <a href="{{ url('/products') }}" class="wl-empty-btn">
            <i class="fas fa-shopping-bag"></i> Discover Products
        </a>
    </div>

    <!-- Wishlist Grid -->
    <div id="wl-grid" class="products-grid wishlist-grid" style="display:none;"></div>
</div>
@endsection

@push('styles')
<style>
.wishlist-page-wrap { max-width: 1400px; margin: 20px auto 60px; padding: 0 16px; }
.wishlist-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 24px;
}
.wishlist-title {
    font-family: var(--font-heading);
    font-size: 26px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
    display: flex;
    align-items: center;
}
.wishlist-sub { font-size: 14px; color: var(--medium-gray); margin: 4px 0 0; }
.wishlist-actions-bar { display: flex; align-items: center; gap: 10px; }

.wl-link-btn {
    background: #ffffff;
    border: 1.5px solid var(--border);
    color: #334155;
    padding: 8px 18px;
    border-radius: var(--radius-pill);
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: var(--transition);
}
.wl-link-btn:hover { background: #f8fafc; border-color: #94a3b8; color: #0f172a; }
.wl-link-danger { color: #ef4444; border-color: #fecaca; }
.wl-link-danger:hover { background: #fee2e2; border-color: #ef4444; color: #b91c1c; }

.wl-loading { text-align: center; padding: 60px 20px; color: var(--medium-gray); font-size: 15px; }
.wl-loading i { font-size: 32px; color: var(--brand-accent); margin-right: 10px; }

.wl-empty {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 70px 20px;
    text-align: center;
    box-shadow: var(--shadow-card);
}
.wl-empty-icon {
    width: 80px;
    height: 80px;
    background: #fff1f2;
    color: #f43f5e;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 34px;
    margin-bottom: 16px;
}
.wl-empty h3 { font-family: var(--font-heading); font-size: 20px; font-weight: 800; color: #0f172a; margin: 0 0 8px; }
.wl-empty p  { font-size: 14px; color: var(--medium-gray); margin: 0 0 20px; }
.wl-empty-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--brand-accent-gradient);
    color: #ffffff;
    padding: 11px 26px;
    border-radius: var(--radius-pill);
    font-weight: 700;
    text-decoration: none;
    font-size: 14px;
    box-shadow: 0 4px 14px rgba(249, 115, 22, 0.3);
    transition: var(--transition);
}
.wl-empty-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(249, 115, 22, 0.4); }

.wishlist-grid {
    background: transparent;
    padding: 0;
}

/* Card Actions */
.wl-card-actions { margin-top: 10px; display: grid; grid-template-columns: 1fr auto; gap: 8px; }
.wl-add-cart {
    background: var(--brand-accent-gradient);
    border: none;
    color: #ffffff;
    padding: 8px 12px;
    border-radius: var(--radius-pill);
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: var(--transition);
    box-shadow: 0 2px 8px rgba(249, 115, 22, 0.25);
}
.wl-add-cart:hover { box-shadow: 0 4px 12px rgba(249, 115, 22, 0.35); transform: translateY(-1px); }
.wl-remove {
    background: #f1f5f9;
    border: 1px solid var(--border);
    color: #ef4444;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
}
.wl-remove:hover { background: #fee2e2; border-color: #ef4444; }

@media (max-width: 768px) {
    .wishlist-page-wrap { padding: 12px 10px 40px; }
    .wishlist-title { font-size: 20px; }
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
            ${discount >= 5 ? `<span class="quick-badge"><i class="fas fa-arrow-down"></i> ${discount}% OFF</span>` : ''}
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
                    ${discount >= 5 ? `<span class="original-price">₹${original.toLocaleString('en-IN')}</span>` : ''}
                </div>
                ${inStock
                    ? `<div class="in-stock-tag"><i class="fas fa-check-circle"></i> In Stock</div>
                       <div class="wl-card-actions">
                         <button type="button" class="wl-add-cart" onclick="event.stopPropagation(); wlMoveToCart(${p.id}, '${escapeHtml(p.name).replace(/'/g,"\\'")}', ${price}, '${escapeHtml(p.image || '').replace(/'/g,"\\'")}', '${escapeHtml(p.slug || p.id).replace(/'/g,"\\'")}');" title="Add to Cart">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                         </button>
                         <button type="button" class="wl-remove" onclick="event.stopPropagation(); wlRemove(${p.id});" title="Remove">
                            <i class="fas fa-trash-alt"></i>
                         </button>
                       </div>`
                    : `<div style="color:#ef4444; font-size:12px; font-weight:700;"><i class="fas fa-times-circle"></i> Out of Stock</div>
                       <div class="wl-card-actions">
                         <button type="button" class="wl-remove" style="grid-column: 1 / -1; width:100%; border-radius:100px; height:auto; padding:8px;" onclick="event.stopPropagation(); wlRemove(${p.id});" title="Remove">
                            <i class="fas fa-trash-alt"></i> Remove from Wishlist
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
        } catch (err) {}

        loading.style.display = 'block';
        grid.style.display = 'none';
        empty.style.display = 'none';

        if (typeof updateWishlistCount === 'function') updateWishlistCount();

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
                sub.textContent = 'Saved items are currently unavailable';
                clearBtn.style.display = 'inline-flex';
                return;
            }

            sub.textContent = items.length + ' ' + (items.length === 1 ? 'item' : 'items') + ' saved in your wishlist';
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
        let list = (typeof getWishlist === 'function' ? getWishlist() : []).filter(x => x !== id);
        localStorage.setItem('store_wishlist', JSON.stringify(list));
        localStorage.setItem('nellai_wishlist', JSON.stringify(list));

        if (typeof updateWishlistCount === 'function') updateWishlistCount();

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
        }
    };

    clearBtn.addEventListener('click', function () {
        if (!confirm('Clear all items from your wishlist?')) return;
        localStorage.setItem('store_wishlist', '[]');
        localStorage.setItem('nellai_wishlist', '[]');
        if (typeof updateWishlistCount === 'function') updateWishlistCount();
        load();
        if (typeof showToast === 'function') showToast('Wishlist cleared', 'info');
    });

    document.addEventListener('DOMContentLoaded', load);
})();
</script>
@endpush
