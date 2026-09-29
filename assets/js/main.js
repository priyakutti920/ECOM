/**
 * Nool & Crop - Main Interactive Application Logic
 * Pure Vanilla JavaScript
 */

let activeProducts = [...NOOL_PRODUCTS];
let currentFilter = 'all';
let currentView = 'grid';
let activeQuickViewProduct = null;
let selectedQVColor = null;
let selectedQVSize = 'M';
let qvQuantity = 1;

document.addEventListener('DOMContentLoaded', () => {
  initHeader();
  initSearch();
  initFilterTabs();
  initSortAndView();
  renderProducts();
  updateHeaderBadges();
  initCartDrawer();
  initQuickViewModal();
  initAuthModal();
  initMobileBottomNav();
  initNewsletter();
  initWhatsAppFloat();
});

/* ==========================================================================
   1. HEADER & STICKY NAVIGATION
   ========================================================================== */
function initHeader() {
  const header = document.querySelector('.header-wrap');
  if (header) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 120) {
        header.classList.add('is-sticky');
      } else {
        header.classList.remove('is-sticky');
      }
    });
  }

  // Mobile menu hamburger
  const hamburger = document.querySelector('.sidebar-menu-icon');
  if (hamburger) {
    hamburger.addEventListener('click', () => {
      const categoryMenu = document.querySelector('.category-nav-dropdown');
      if (categoryMenu) {
        categoryMenu.classList.toggle('is-open');
      }
      showToast('Viewing categories menu', 'info', '<i class="las la-bars"></i>');
    });
  }
}

/* ==========================================================================
   2. SEARCH & AUTOCOMPLETE SUGGESTIONS
   ========================================================================== */
function initSearch() {
  const searchInput = document.querySelector('.search-input');
  const categorySelect = document.querySelector('.search-category-select');
  const suggestionsBox = document.querySelector('.search-suggestions-dropdown');
  const searchForm = document.querySelector('.header-search-form');

  if (!searchInput || !suggestionsBox) return;

  searchInput.addEventListener('input', (e) => {
    const query = e.target.value.trim().toLowerCase();
    if (query.length < 2) {
      suggestionsBox.classList.remove('is-active');
      return;
    }

    const matches = NOOL_PRODUCTS.filter(p => 
      p.name.toLowerCase().includes(query) || 
      p.categoryName.toLowerCase().includes(query) ||
      p.colors.some(c => c.name.toLowerCase().includes(query))
    );

    if (matches.length > 0) {
      suggestionsBox.innerHTML = `
        <div class="suggestion-header">Product Suggestions (${matches.length})</div>
        ${matches.slice(0, 5).map(p => `
          <div class="suggestion-item" data-id="${p.id}">
            <img src="${p.colors[0].front}" alt="${p.name}">
            <div class="suggestion-info">
              <div class="suggestion-title">${p.name}</div>
              <div class="suggestion-price">₹${p.price.toFixed(2)}</div>
            </div>
          </div>
        `).join('')}
      `;
      suggestionsBox.classList.add('is-active');

      // Click on suggestion
      suggestionsBox.querySelectorAll('.suggestion-item').forEach(item => {
        item.addEventListener('click', () => {
          const id = parseInt(item.getAttribute('data-id'));
          const prod = NOOL_PRODUCTS.find(p => p.id === id);
          if (prod) {
            openQuickView(prod);
          }
          suggestionsBox.classList.remove('is-active');
        });
      });
    } else {
      suggestionsBox.innerHTML = `
        <div class="suggestion-header">No results found for "${query}"</div>
      `;
      suggestionsBox.classList.add('is-active');
    }
  });

  // Close suggestions when clicking outside
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.header-search-container')) {
      suggestionsBox.classList.remove('is-active');
    }
  });

  // Handle Search Submission
  if (searchForm) {
    searchForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const query = searchInput.value.trim().toLowerCase();
      const selectedCategory = categorySelect ? categorySelect.value : 'all';

      activeProducts = NOOL_PRODUCTS.filter(p => {
        const matchesQuery = !query || 
          p.name.toLowerCase().includes(query) || 
          p.categoryName.toLowerCase().includes(query) ||
          p.colors.some(c => c.name.toLowerCase().includes(query));

        const matchesCat = selectedCategory === 'all' || p.categoryName.toLowerCase().includes(selectedCategory);
        return matchesQuery && matchesCat;
      });

      renderProducts();
      suggestionsBox.classList.remove('is-active');
      showToast(`Found ${activeProducts.length} product(s)`, 'info', '<i class="las la-search"></i>');
      
      // Scroll smoothly to products
      const grid = document.getElementById('products-grid');
      if (grid) {
        grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  }

  // Quick keyword tag clicks
  document.querySelectorAll('.keyword-tag').forEach(tag => {
    tag.addEventListener('click', () => {
      const keyword = tag.textContent.trim();
      searchInput.value = keyword;
      searchForm.dispatchEvent(new Event('submit'));
    });
  });
}

/* ==========================================================================
   3. FILTER TABS & SORTING
   ========================================================================== */
function initFilterTabs() {
  const tabButtons = document.querySelectorAll('.filter-tab-btn');
  tabButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      tabButtons.forEach(b => b.classList.remove('is-active'));
      btn.classList.add('is-active');

      currentFilter = btn.getAttribute('data-filter');
      applyFilters();
    });
  });
}

function applyFilters() {
  if (currentFilter === 'all') {
    activeProducts = [...NOOL_PRODUCTS];
  } else if (currentFilter === 'bestseller') {
    activeProducts = NOOL_PRODUCTS.filter(p => p.isBestSeller);
  } else if (currentFilter === 'new') {
    activeProducts = NOOL_PRODUCTS.filter(p => p.isNew);
  } else if (currentFilter === 'purple') {
    activeProducts = NOOL_PRODUCTS.filter(p => p.colors.some(c => c.name.toLowerCase() === 'purple'));
  } else if (currentFilter === 'navy') {
    activeProducts = NOOL_PRODUCTS.filter(p => p.colors.some(c => c.name.toLowerCase().includes('navy')));
  } else if (currentFilter === 'orange') {
    activeProducts = NOOL_PRODUCTS.filter(p => p.colors.some(c => c.name.toLowerCase().includes('orange')));
  }
  renderProducts();
}

function initSortAndView() {
  const sortSelect = document.getElementById('sort-select');
  if (sortSelect) {
    sortSelect.addEventListener('change', (e) => {
      const val = e.target.value;
      if (val === 'price-low') {
        activeProducts.sort((a, b) => a.price - b.price);
      } else if (val === 'price-high') {
        activeProducts.sort((a, b) => b.price - a.price);
      } else if (val === 'rating') {
        activeProducts.sort((a, b) => b.rating - a.rating);
      } else {
        // default / featured
        applyFilters();
        return;
      }
      renderProducts();
    });
  }

  // View toggle (Grid / List)
  const viewBtns = document.querySelectorAll('.view-toggle-btn');
  const gridContainer = document.getElementById('products-grid');
  viewBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      viewBtns.forEach(b => b.classList.remove('is-active'));
      btn.classList.add('is-active');
      currentView = btn.getAttribute('data-view');
      if (gridContainer) {
        if (currentView === 'list') {
          gridContainer.classList.add('list-view');
        } else {
          gridContainer.classList.remove('list-view');
        }
      }
    });
  });
}

/* ==========================================================================
   4. RENDER PRODUCT CARDS (Dual Image Hover, Swatches, Quick Actions)
   ========================================================================== */
function renderProducts() {
  const grid = document.getElementById('products-grid');
  if (!grid) return;

  if (activeProducts.length === 0) {
    grid.innerHTML = `
      <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: #fff; border-radius: 12px; border: 1px solid var(--color-border);">
        <i class="las la-tshirt" style="font-size: 3.5rem; color: #cbd5e1; margin-bottom: 12px; display: inline-block;"></i>
        <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 6px;">No products match your selection</h3>
        <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 20px;">Try picking a different color filter or search keyword.</p>
        <button onclick="resetFilters()" style="background: var(--color-primary); color: #fff; padding: 9px 22px; border-radius: 6px; font-weight: 500;">View All Products</button>
      </div>
    `;
    return;
  }

  const wishlist = getStoredWishlist();
  const compare = getStoredCompare();

  grid.innerHTML = activeProducts.map(p => {
    const currentColorObj = p.colors.find(c => c.name === p.currentColor) || p.colors[0];
    const isWishlisted = wishlist.includes(p.id);
    const isCompared = compare.includes(p.id);

    return `
      <div class="product-card" id="product-card-${p.id}" data-id="${p.id}">
        <!-- Media Container with front & back alternate photo -->
        <div class="product-card-media" onclick="handleCardClick(${p.id})">
          <img 
            src="${currentColorObj.front}" 
            alt="${p.name} - Front View" 
            class="product-card-img img-primary" 
            id="card-img-primary-${p.id}"
            loading="lazy"
          >
          <img 
            src="${currentColorObj.back}" 
            alt="${p.name} - Back View" 
            class="product-card-img img-secondary" 
            id="card-img-secondary-${p.id}"
            loading="lazy"
          >

          <!-- Badges -->
          <div class="product-badges">
            ${p.isNew ? '<span class="badge-tag badge-new">New</span>' : ''}
            <span class="badge-tag badge-sale">-${p.discountPercent}%</span>
          </div>

          <!-- Floating Action Buttons -->
          <div class="product-card-actions" onclick="event.stopPropagation()">
            <button class="action-icon-btn" title="Quick View" onclick="openQuickViewById(${p.id})">
              <i class="las la-eye"></i>
            </button>
            <button class="action-icon-btn ${isWishlisted ? 'is-active' : ''}" title="Add to Wishlist" onclick="toggleWishlist(${p.id})">
              <i class="${isWishlisted ? 'las la-heart' : 'lar la-heart'}"></i>
            </button>
            <button class="action-icon-btn ${isCompared ? 'is-active' : ''}" title="Compare" onclick="toggleCompare(${p.id})">
              <i class="las la-sync-alt"></i>
            </button>
          </div>
        </div>

        <!-- Body Content -->
        <div class="product-card-body">
          <!-- Color Swatches -->
          <div class="product-swatches" onclick="event.stopPropagation()">
            ${p.colors.map(c => `
              <div 
                class="swatch-circle ${c.name === p.currentColor ? 'is-active' : ''}" 
                style="background-color: ${c.hex};" 
                title="${c.name}"
                onclick="changeProductColor(${p.id}, '${c.name}')"
              ></div>
            `).join('')}
          </div>

          <div class="product-category-tag">${p.categoryName}</div>
          <h3 class="product-title" title="${p.name}">
            <a href="javascript:void(0)" onclick="openQuickViewById(${p.id})">${p.name}</a>
          </h3>

          <div class="product-rating">
            <div class="star-icons">
              ${generateStarRatingHTML(p.rating)}
            </div>
            <span class="rating-count">(${p.reviewsCount})</span>
          </div>

          <!-- Size Selection Chips -->
          <div class="size-chips-row" onclick="event.stopPropagation()">
            ${p.sizes.map(size => `
              <span 
                class="size-chip ${size === (p.currentSize || 'M') ? 'is-active' : ''}"
                onclick="changeProductSize(${p.id}, '${size}')"
              >${size}</span>
            `).join('')}
          </div>

          <!-- Price Row -->
          <div class="product-price-row">
            <span class="current-price">₹${p.price.toFixed(2)}</span>
            <span class="original-price">₹${p.mrp.toFixed(2)}</span>
            <span class="discount-percentage">Save 64%</span>
          </div>

          <!-- Add to Cart CTA -->
          <button class="btn-add-to-cart" onclick="quickAddToCart(${p.id})">
            <i class="las la-shopping-bag"></i>
            Add to Cart
          </button>
        </div>
      </div>
    `;
  }).join('');
}

function handleCardClick(productId) {
  openQuickViewById(productId);
}

function generateStarRatingHTML(rating) {
  let html = '';
  const fullStars = Math.floor(rating);
  for (let i = 0; i < 5; i++) {
    if (i < fullStars) {
      html += '<i class="las la-star"></i>';
    } else {
      html += '<i class="las la-star-half-alt"></i>';
    }
  }
  return html;
}

function resetFilters() {
  currentFilter = 'all';
  const tabButtons = document.querySelectorAll('.filter-tab-btn');
  tabButtons.forEach(b => {
    if (b.getAttribute('data-filter') === 'all') {
      b.classList.add('is-active');
    } else {
      b.classList.remove('is-active');
    }
  });
  activeProducts = [...NOOL_PRODUCTS];
  renderProducts();
}

/* ==========================================================================
   5. COLOR SWATCHES & SIZE INTERACTION ON CARDS
   ========================================================================== */
function changeProductColor(productId, colorName) {
  const prod = NOOL_PRODUCTS.find(p => p.id === productId);
  if (!prod) return;

  prod.currentColor = colorName;
  const colorObj = prod.colors.find(c => c.name === colorName);
  if (!colorObj) return;

  const primaryImg = document.getElementById(`card-img-primary-${productId}`);
  const secondaryImg = document.getElementById(`card-img-secondary-${productId}`);
  if (primaryImg) primaryImg.src = colorObj.front;
  if (secondaryImg) secondaryImg.src = colorObj.back;

  // Update active state on card swatches
  const card = document.getElementById(`product-card-${productId}`);
  if (card) {
    const swatches = card.querySelectorAll('.swatch-circle');
    swatches.forEach(s => {
      if (s.getAttribute('title') === colorName) {
        s.classList.add('is-active');
      } else {
        s.classList.remove('is-active');
      }
    });
  }

  showToast(`${prod.name.slice(0, 22)}... switched to ${colorName}`, 'info', '<i class="las la-palette"></i>');
}

function changeProductSize(productId, size) {
  const prod = NOOL_PRODUCTS.find(p => p.id === productId);
  if (!prod) return;

  prod.currentSize = size;
  const card = document.getElementById(`product-card-${productId}`);
  if (card) {
    const chips = card.querySelectorAll('.size-chip');
    chips.forEach(c => {
      if (c.textContent.trim() === size) {
        c.classList.add('is-active');
      } else {
        c.classList.remove('is-active');
      }
    });
  }
}

/* ==========================================================================
   6. QUICK VIEW MODAL
   ========================================================================== */
function initQuickViewModal() {
  const modal = document.getElementById('quick-view-modal');
  const closeBtn = document.getElementById('qv-close-btn');

  if (closeBtn && modal) {
    closeBtn.addEventListener('click', closeQuickView);
  }

  if (modal) {
    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        closeQuickView();
      }
    });
  }

  // Escape key to close
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeQuickView();
      closeCartDrawer();
      closeAuthModal();
    }
  });
}

function openQuickViewById(id) {
  const prod = NOOL_PRODUCTS.find(p => p.id === id);
  if (prod) {
    openQuickView(prod);
  }
}

function openQuickView(prod) {
  activeQuickViewProduct = prod;
  selectedQVColor = prod.currentColor || prod.colors[0].name;
  selectedQVSize = prod.currentSize || 'M';
  qvQuantity = 1;

  const modal = document.getElementById('quick-view-modal');
  const colorObj = prod.colors.find(c => c.name === selectedQVColor) || prod.colors[0];

  // Set images
  const mainImg = document.getElementById('qv-main-img');
  if (mainImg) mainImg.src = colorObj.front;

  const thumbsStrip = document.getElementById('qv-thumbs-strip');
  if (thumbsStrip) {
    thumbsStrip.innerHTML = `
      <div class="quickview-thumb is-active" onclick="switchQVGalleryImage('${colorObj.front}', this)">
        <img src="${colorObj.front}" alt="Front">
      </div>
      <div class="quickview-thumb" onclick="switchQVGalleryImage('${colorObj.back}', this)">
        <img src="${colorObj.back}" alt="Back">
      </div>
    `;
  }

  // Info fields
  const title = document.getElementById('qv-title');
  if (title) title.textContent = prod.name;

  const currentPrice = document.getElementById('qv-current-price');
  if (currentPrice) currentPrice.textContent = `₹${prod.price.toFixed(2)}`;

  const mrp = document.getElementById('qv-mrp');
  if (mrp) mrp.textContent = `₹${prod.mrp.toFixed(2)}`;

  const desc = document.getElementById('qv-desc');
  if (desc) desc.textContent = prod.description;

  const selectedColorLabel = document.getElementById('qv-selected-color-label');
  if (selectedColorLabel) selectedColorLabel.textContent = selectedQVColor;

  const selectedSizeLabel = document.getElementById('qv-selected-size-label');
  if (selectedSizeLabel) selectedSizeLabel.textContent = selectedQVSize;

  // Swatches
  const swatchesContainer = document.getElementById('qv-swatches-container');
  if (swatchesContainer) {
    swatchesContainer.innerHTML = prod.colors.map(c => `
      <div 
        class="qv-swatch-item ${c.name === selectedQVColor ? 'is-active' : ''}" 
        style="background-color: ${c.hex};" 
        title="${c.name}"
        onclick="selectQVColor('${c.name}')"
      ></div>
    `).join('');
  }

  // Sizes
  const sizesContainer = document.getElementById('qv-sizes-container');
  if (sizesContainer) {
    sizesContainer.innerHTML = prod.sizes.map(s => `
      <div 
        class="qv-size-pill ${s === selectedQVSize ? 'is-active' : ''}"
        onclick="selectQVSize('${s}')"
      >${s}</div>
    `).join('');
  }

  // Qty
  const qtyInput = document.getElementById('qv-qty-input');
  if (qtyInput) qtyInput.value = 1;

  if (modal) {
    modal.classList.add('is-open');
    document.body.classList.add('modal-open');
  }
}

function switchQVGalleryImage(src, element) {
  const mainImg = document.getElementById('qv-main-img');
  if (mainImg) mainImg.src = src;

  document.querySelectorAll('.quickview-thumb').forEach(t => t.classList.remove('is-active'));
  if (element) element.classList.add('is-active');
}

function selectQVColor(colorName) {
  if (!activeQuickViewProduct) return;
  selectedQVColor = colorName;

  const colorObj = activeQuickViewProduct.colors.find(c => c.name === colorName);
  if (colorObj) {
    const mainImg = document.getElementById('qv-main-img');
    if (mainImg) mainImg.src = colorObj.front;

    const thumbsStrip = document.getElementById('qv-thumbs-strip');
    if (thumbsStrip) {
      thumbsStrip.innerHTML = `
        <div class="quickview-thumb is-active" onclick="switchQVGalleryImage('${colorObj.front}', this)">
          <img src="${colorObj.front}" alt="Front">
        </div>
        <div class="quickview-thumb" onclick="switchQVGalleryImage('${colorObj.back}', this)">
          <img src="${colorObj.back}" alt="Back">
        </div>
      `;
    }
  }

  const selectedColorLabel = document.getElementById('qv-selected-color-label');
  if (selectedColorLabel) selectedColorLabel.textContent = colorName;

  document.querySelectorAll('.qv-swatch-item').forEach(s => {
    if (s.getAttribute('title') === colorName) {
      s.classList.add('is-active');
    } else {
      s.classList.remove('is-active');
    }
  });
}

function selectQVSize(size) {
  selectedQVSize = size;
  const selectedSizeLabel = document.getElementById('qv-selected-size-label');
  if (selectedSizeLabel) selectedSizeLabel.textContent = size;

  document.querySelectorAll('.qv-size-pill').forEach(pill => {
    if (pill.textContent.trim() === size) {
      pill.classList.add('is-active');
    } else {
      pill.classList.remove('is-active');
    }
  });
}

function changeQVQuantity(delta) {
  const input = document.getElementById('qv-qty-input');
  if (!input) return;
  let val = parseInt(input.value) || 1;
  val = Math.max(1, val + delta);
  input.value = val;
  qvQuantity = val;
}

function closeQuickView() {
  const modal = document.getElementById('quick-view-modal');
  if (modal) {
    modal.classList.remove('is-open');
    document.body.classList.remove('modal-open');
  }
}

function addQVToCart() {
  if (!activeQuickViewProduct) return;

  const colorObj = activeQuickViewProduct.colors.find(c => c.name === selectedQVColor) || activeQuickViewProduct.colors[0];

  addItemToCart({
    productId: activeQuickViewProduct.id,
    name: activeQuickViewProduct.name,
    color: selectedQVColor,
    size: selectedQVSize,
    price: activeQuickViewProduct.price,
    quantity: qvQuantity,
    image: colorObj.front
  });

  closeQuickView();
  openCartDrawer();
}

function buyQVNow() {
  addQVToCart();
  showToast('Proceeding to instant checkout!', 'success', '<i class="las la-bolt"></i>');
}

/* ==========================================================================
   7. SHOPPING CART DRAWER LOGIC
   ========================================================================== */
function initCartDrawer() {
  const trigger = document.getElementById('header-cart-trigger');
  const drawer = document.getElementById('sidebar-cart-drawer');
  const overlay = document.getElementById('cart-drawer-overlay');
  const closeBtn = document.getElementById('cart-drawer-close');

  if (trigger) {
    trigger.addEventListener('click', openCartDrawer);
  }

  if (closeBtn) {
    closeBtn.addEventListener('click', closeCartDrawer);
  }

  if (overlay) {
    overlay.addEventListener('click', closeCartDrawer);
  }

  renderCartDrawer();
}

function openCartDrawer() {
  const drawer = document.getElementById('sidebar-cart-drawer');
  const overlay = document.getElementById('cart-drawer-overlay');
  if (drawer && overlay) {
    renderCartDrawer();
    drawer.classList.add('is-open');
    overlay.classList.add('is-open');
    document.body.classList.add('modal-open');
  }
}

function closeCartDrawer() {
  const drawer = document.getElementById('sidebar-cart-drawer');
  const overlay = document.getElementById('cart-drawer-overlay');
  if (drawer && overlay) {
    drawer.classList.remove('is-open');
    overlay.classList.remove('is-open');
    document.body.classList.remove('modal-open');
  }
}

function quickAddToCart(productId) {
  const prod = NOOL_PRODUCTS.find(p => p.id === productId);
  if (!prod) return;

  const color = prod.currentColor || prod.colors[0].name;
  const size = prod.currentSize || 'M';
  const colorObj = prod.colors.find(c => c.name === color) || prod.colors[0];

  addItemToCart({
    productId: prod.id,
    name: prod.name,
    color: color,
    size: size,
    price: prod.price,
    quantity: 1,
    image: colorObj.front
  });

  openCartDrawer();
}

function addItemToCart(item) {
  const cart = getStoredCart();
  const existingIdx = cart.findIndex(c => 
    c.productId === item.productId && 
    c.color === item.color && 
    c.size === item.size
  );

  if (existingIdx > -1) {
    cart[existingIdx].quantity += item.quantity;
  } else {
    cart.push(item);
  }

  saveCart(cart);
  updateHeaderBadges();
  renderCartDrawer();
  showToast(`Added "${item.name.slice(0, 24)}..." to Cart!`, 'success', '<i class="las la-check-circle"></i>');
}

function updateCartItemQty(index, delta) {
  const cart = getStoredCart();
  if (cart[index]) {
    cart[index].quantity += delta;
    if (cart[index].quantity <= 0) {
      cart.splice(index, 1);
      showToast('Item removed from cart', 'info', '<i class="las la-trash"></i>');
    }
    saveCart(cart);
    updateHeaderBadges();
    renderCartDrawer();
  }
}

function removeCartItem(index) {
  const cart = getStoredCart();
  cart.splice(index, 1);
  saveCart(cart);
  updateHeaderBadges();
  renderCartDrawer();
  showToast('Item removed from cart', 'info', '<i class="las la-trash"></i>');
}

function renderCartDrawer() {
  const cart = getStoredCart();
  const itemsContainer = document.getElementById('cart-drawer-items');
  const emptyState = document.getElementById('cart-empty-state');
  const footer = document.getElementById('cart-drawer-footer');
  const countDisplay = document.getElementById('cart-drawer-count');
  const subtotalDisplay = document.getElementById('cart-subtotal-val');
  const totalDisplay = document.getElementById('cart-total-val');
  const freeShippingProgress = document.getElementById('free-shipping-progress');
  const freeShippingText = document.getElementById('free-shipping-text');

  const totalCount = cart.reduce((sum, item) => sum + item.quantity, 0);
  const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

  if (countDisplay) countDisplay.textContent = `(${totalCount})`;
  if (subtotalDisplay) subtotalDisplay.textContent = `₹${subtotal.toFixed(2)}`;
  if (totalDisplay) totalDisplay.textContent = `₹${subtotal.toFixed(2)}`;

  // Free shipping threshold (₹499)
  const minFreeShipping = NOOL_STORE_SETTINGS.freeShippingMin;
  if (freeShippingProgress && freeShippingText) {
    const percent = Math.min(100, (subtotal / minFreeShipping) * 100);
    freeShippingProgress.style.width = `${percent}%`;

    if (subtotal >= minFreeShipping) {
      freeShippingText.innerHTML = `🎉 Congratulations! You have unlocked <strong>FREE Shipping</strong>!`;
    } else {
      const remaining = minFreeShipping - subtotal;
      freeShippingText.innerHTML = `Add <strong>₹${remaining.toFixed(2)}</strong> more to get <strong>FREE Shipping</strong>!`;
    }
  }

  if (cart.length === 0) {
    if (itemsContainer) itemsContainer.style.display = 'none';
    if (emptyState) emptyState.style.display = 'block';
    if (footer) footer.style.display = 'none';
  } else {
    if (emptyState) emptyState.style.display = 'none';
    if (itemsContainer) {
      itemsContainer.style.display = 'block';
      itemsContainer.innerHTML = cart.map((item, idx) => `
        <div class="cart-item">
          <img src="${item.image}" alt="${item.name}" class="cart-item-img">
          <div class="cart-item-details">
            <h4 class="cart-item-title">${item.name}</h4>
            <div class="cart-item-variant">Variant: <strong>${item.color}</strong> / Size: <strong>${item.size}</strong></div>
            <div class="cart-item-price-row">
              <span class="cart-item-price">₹${(item.price * item.quantity).toFixed(2)}</span>
              <div class="qty-control">
                <button class="qty-btn" onclick="updateCartItemQty(${idx}, -1)">-</button>
                <span class="qty-number">${item.quantity}</span>
                <button class="qty-btn" onclick="updateCartItemQty(${idx}, 1)">+</button>
              </div>
            </div>
          </div>
          <button class="cart-item-remove" onclick="removeCartItem(${idx})" title="Remove item">
            <i class="las la-times"></i>
          </button>
        </div>
      `).join('');
    }
    if (footer) footer.style.display = 'block';
  }
}

function handleCheckout() {
  const cart = getStoredCart();
  if (cart.length === 0) {
    showToast('Your cart is empty!', 'info', '<i class="las la-exclamation-circle"></i>');
    return;
  }
  const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
  closeCartDrawer();
  openAuthModal(`Proceeding to Checkout for ₹${total.toFixed(2)}. Please verify your mobile number.`);
}

/* ==========================================================================
   8. WISHLIST & COMPARE BADGES
   ========================================================================== */
function toggleWishlist(productId) {
  let list = getStoredWishlist();
  const exists = list.includes(productId);
  const prod = NOOL_PRODUCTS.find(p => p.id === productId);

  if (exists) {
    list = list.filter(id => id !== productId);
    showToast(`Removed "${prod ? prod.name.slice(0, 20) : ''}..." from Wishlist`, 'info', '<i class="lar la-heart"></i>');
  } else {
    list.push(productId);
    showToast(`Added to Wishlist! ❤️`, 'success', '<i class="las la-heart"></i>');
  }

  saveWishlist(list);
  updateHeaderBadges();
  renderProducts();
}

function toggleCompare(productId) {
  let list = getStoredCompare();
  const exists = list.includes(productId);

  if (exists) {
    list = list.filter(id => id !== productId);
    showToast('Removed from compare list', 'info', '<i class="las la-sync-alt"></i>');
  } else {
    list.push(productId);
    showToast('Added to compare list', 'success', '<i class="las la-check"></i>');
  }

  saveCompare(list);
  updateHeaderBadges();
  renderProducts();
}

function updateHeaderBadges() {
  const cart = getStoredCart();
  const wishlist = getStoredWishlist();
  const compare = getStoredCompare();

  const cartTotalQty = cart.reduce((sum, i) => sum + i.quantity, 0);
  const cartTotalAmount = cart.reduce((sum, i) => sum + (i.price * i.quantity), 0);

  // Header badges
  const cartBadge = document.getElementById('header-cart-count');
  if (cartBadge) {
    cartBadge.textContent = cartTotalQty;
    cartBadge.classList.add('pulse');
    setTimeout(() => cartBadge.classList.remove('pulse'), 300);
  }

  const wishlistBadge = document.getElementById('header-wishlist-count');
  if (wishlistBadge) wishlistBadge.textContent = wishlist.length;

  const compareBadge = document.getElementById('header-compare-count');
  if (compareBadge) compareBadge.textContent = compare.length;

  const headerCartAmount = document.getElementById('header-cart-amount');
  if (headerCartAmount) headerCartAmount.textContent = `₹${cartTotalAmount.toFixed(2)}`;

  // Mobile Bottom Nav Badges
  const mbCartBadge = document.getElementById('mobile-bottom-cart-count');
  if (mbCartBadge) mbCartBadge.textContent = cartTotalQty;

  const mbCompareBadge = document.getElementById('mobile-bottom-compare-count');
  if (mbCompareBadge) mbCompareBadge.textContent = compare.length;
}

/* ==========================================================================
   9. AUTH / MOBILE OTP MODAL
   ========================================================================== */
let otpTimerInterval = null;

function initAuthModal() {
  const modal = document.getElementById('auth-modal');
  const closeBtn = document.getElementById('auth-close-btn');

  if (closeBtn && modal) {
    closeBtn.addEventListener('click', closeAuthModal);
  }

  if (modal) {
    modal.addEventListener('click', (e) => {
      if (e.target === modal) closeAuthModal();
    });
  }

  const sendOtpBtn = document.getElementById('btn-send-otp');
  const verifyOtpBtn = document.getElementById('btn-verify-otp');
  const phoneStep = document.getElementById('auth-step-phone');
  const otpStep = document.getElementById('auth-step-otp');
  const phoneField = document.getElementById('auth-phone-number');

  if (sendOtpBtn && phoneField) {
    sendOtpBtn.addEventListener('click', () => {
      const phone = phoneField.value.trim();
      if (phone.length < 10) {
        showToast('Please enter a valid 10-digit mobile number', 'info', '<i class="las la-exclamation-triangle"></i>');
        return;
      }

      phoneStep.style.display = 'none';
      otpStep.style.display = 'block';
      document.getElementById('otp-mobile-display').textContent = `+91 ${phone}`;

      // Start countdown timer
      startOtpCountdown();
      showToast(`OTP sent to +91 ${phone}! (Demo code: 1234)`, 'success', '<i class="las la-sms"></i>');
    });
  }

  if (verifyOtpBtn) {
    verifyOtpBtn.addEventListener('click', () => {
      showToast('Login successful! Welcome to Nool & Crop.', 'success', '<i class="las la-user-check"></i>');
      closeAuthModal();
    });
  }
}

function openAuthModal(customSubtitle = null) {
  const modal = document.getElementById('auth-modal');
  const subtitle = document.getElementById('auth-modal-subtitle');
  if (subtitle && customSubtitle) {
    subtitle.textContent = customSubtitle;
  }
  if (modal) {
    modal.classList.add('is-open');
    document.body.classList.add('modal-open');
  }
}

function closeAuthModal() {
  const modal = document.getElementById('auth-modal');
  if (modal) {
    modal.classList.remove('is-open');
    document.body.classList.remove('modal-open');
  }
  if (otpTimerInterval) clearInterval(otpTimerInterval);
}

function startOtpCountdown() {
  let seconds = 30;
  const timerElem = document.getElementById('otp-timer-count');
  const resendBtn = document.getElementById('btn-resend-otp');

  if (resendBtn) resendBtn.disabled = true;

  if (otpTimerInterval) clearInterval(otpTimerInterval);
  otpTimerInterval = setInterval(() => {
    seconds--;
    if (timerElem) timerElem.textContent = `${seconds}s`;
    if (seconds <= 0) {
      clearInterval(otpTimerInterval);
      if (resendBtn) resendBtn.disabled = false;
      if (timerElem) timerElem.textContent = '';
    }
  }, 1000);
}

/* ==========================================================================
   10. MOBILE BOTTOM NAVIGATION
   ========================================================================== */
function initMobileBottomNav() {
  const navItems = document.querySelectorAll('.bottom-nav-item a');
  navItems.forEach(item => {
    item.addEventListener('click', (e) => {
      const action = item.getAttribute('data-action');
      if (action === 'cart') {
        e.preventDefault();
        openCartDrawer();
      } else if (action === 'account') {
        e.preventDefault();
        openAuthModal();
      } else if (action === 'categories') {
        e.preventDefault();
        const catSection = document.getElementById('categories-section');
        if (catSection) {
          catSection.scrollIntoView({ behavior: 'smooth' });
        }
      } else if (action === 'compare') {
        e.preventDefault();
        showToast(`You have ${getStoredCompare().length} item(s) in compare list`, 'info', '<i class="las la-sync-alt"></i>');
      }
    });
  });
}

/* ==========================================================================
   11. NEWSLETTER
   ========================================================================== */
function initNewsletter() {
  const form = document.getElementById('newsletter-form');
  if (form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const input = form.querySelector('.newsletter-input');
      if (input && input.value) {
        showToast('Thank you for subscribing to Nool & Crop!', 'success', '<i class="las la-envelope"></i>');
        input.value = '';
      }
    });
  }
}

/* ==========================================================================
   12. TOAST NOTIFICATION UTILITY
   ========================================================================== */
function showToast(message, type = 'success', iconHTML = '<i class="las la-check"></i>') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `toast-msg toast-${type}`;
  toast.innerHTML = `
    <div class="toast-icon">${iconHTML}</div>
    <div class="toast-text">${message}</div>
  `;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(10px)';
    toast.style.transition = 'all 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 3200);
}

/* ==========================================================================
   14. FLOATING WHATSAPP BUTTON (User Side)
   ========================================================================== */
function initWhatsAppFloat() {
  if (document.getElementById('wa-floating-btn') || document.getElementById('wa-float-btn')) return;

  const waPhone = '918098830937';
  const waMsg = encodeURIComponent('Hi I Need Admin Support');
  const waUrl = `https://wa.me/${waPhone}?text=${waMsg}`;

  const btn = document.createElement('a');
  btn.id = 'wa-float-btn';
  btn.className = 'wa-float-btn';
  btn.href = waUrl;
  btn.target = '_blank';
  btn.rel = 'noopener noreferrer';
  btn.setAttribute('aria-label', 'Chat on WhatsApp');
  btn.setAttribute('title', 'Chat on WhatsApp');
  btn.innerHTML = `
    <svg viewBox="0 0 32 32" width="26" height="26" aria-hidden="true">
      <path fill="currentColor" d="M19.11 17.27c-.27-.14-1.6-.79-1.85-.88-.25-.09-.43-.14-.61.14-.18.27-.7.88-.86 1.06-.16.18-.32.2-.59.07-.27-.14-1.14-.42-2.18-1.34-.81-.72-1.35-1.61-1.51-1.88-.16-.27-.02-.42.12-.55.12-.12.27-.32.41-.48.14-.16.18-.27.27-.45.09-.18.05-.34-.02-.48-.07-.14-.61-1.47-.84-2.01-.22-.53-.45-.46-.61-.47l-.52-.01c-.18 0-.48.07-.73.34-.25.27-.96.94-.96 2.29 0 1.35.99 2.66 1.13 2.84.14.18 1.95 2.97 4.72 4.16.66.29 1.18.46 1.58.59.66.21 1.26.18 1.74.11.53-.08 1.6-.66 1.83-1.29.23-.63.23-1.18.16-1.29-.07-.11-.25-.18-.52-.32zM16 4C9.38 4 4 9.38 4 16c0 2.29.64 4.41 1.76 6.21L4 28l5.91-1.55A11.93 11.93 0 0 0 16 28c6.62 0 12-5.38 12-12S22.62 4 16 4zm0 21.82c-1.96 0-3.79-.55-5.36-1.5l-.38-.23-3.5.92.93-3.41-.25-.4A9.85 9.85 0 0 1 6.18 16C6.18 10.6 10.6 6.18 16 6.18S25.82 10.6 25.82 16 21.4 25.82 16 25.82z"/>
    </svg>
    <span class="wa-label">Chat with Us</span>
  `;

  document.body.appendChild(btn);
}

