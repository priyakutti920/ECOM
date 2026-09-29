/**
 * Nool & Crop - Mock Database & Shared Store
 * Offline-ready, persistent with localStorage
 */

const NOOL_STORE_SETTINGS = {
  storeName: "Nool & Crop",
  storePhone: "80560 81594",
  storeEmail: "noolcrop@gmail.com",
  storeLogo: "assets/images/logo.png",
  instagram: "https://www.instagram.com/noolcrop",
  freeShippingMin: 499,
  currency: "₹"
};

const NOOL_CATEGORIES = [
  {
    id: 1,
    name: "Men's T-Shirt",
    slug: "t-shirt",
    count: 8,
    image: "assets/images/purple_front.jpg"
  },
  {
    id: 2,
    name: "Plain Solid Colors",
    slug: "solid-plain",
    count: 6,
    image: "assets/images/orange_front.jpg"
  },
  {
    id: 3,
    name: "Cotton Blend Regular Fit",
    slug: "cotton-regular",
    count: 5,
    image: "assets/images/navy_front.jpg"
  },
  {
    id: 4,
    name: "Round Neck Basics",
    slug: "round-neck",
    count: 7,
    image: "assets/images/black_front.jpg"
  },
  {
    id: 5,
    name: "Summer Casuals",
    slug: "summer-casuals",
    count: 4,
    image: "assets/images/white_front.jpg"
  }
];

const NOOL_PRODUCTS = [
  {
    id: 1,
    name: "Half Sleeves Solid Plain Round Neck Cotton Blend Regular Fit Men's T-Shirt",
    slug: "mens-t-shirt-flagship",
    categoryId: 1,
    categoryName: "Men's T-Shirt",
    price: 179.00,
    mrp: 499.00,
    discountPercent: 64,
    rating: 4.9,
    reviewsCount: 84,
    isNew: true,
    isBestSeller: true,
    description: "Crafted from 180 GSM bio-washed combed cotton blend fabric, this regular fit men's round neck t-shirt provides all-day breathability and softness. Pre-shrunk to retain shape wash after wash.",
    currentColor: "Purple",
    currentSize: "M",
    colors: [
      {
        name: "Purple",
        hex: "#4b0c4e",
        front: "assets/images/purple_front.jpg",
        back: "assets/images/purple_back.jpg"
      },
      {
        name: "Orange",
        hex: "#b82804",
        front: "assets/images/orange_front.jpg",
        back: "assets/images/orange_back.jpg"
      },
      {
        name: "Navy Blue",
        hex: "#04195a",
        front: "assets/images/navy_front.jpg",
        back: "assets/images/navy_back.jpg"
      },
      {
        name: "Black",
        hex: "#111111",
        front: "assets/images/black_front.jpg",
        back: "assets/images/black_front.jpg"
      },
      {
        name: "White",
        hex: "#f8f9fa",
        front: "assets/images/white_front.jpg",
        back: "assets/images/white_front.jpg"
      }
    ],
    sizes: ["M", "L", "XL"],
    stock: 28
  },
  {
    id: 2,
    name: "Half Sleeves Solid Regular Fit T-Shirt - Sunset Orange",
    slug: "mens-t-shirt-orange",
    categoryId: 2,
    categoryName: "Plain Solid Colors",
    price: 179.00,
    mrp: 499.00,
    discountPercent: 64,
    rating: 4.8,
    reviewsCount: 42,
    isNew: false,
    isBestSeller: true,
    description: "Bright, rich sunset orange shade with color-lock technology. Soft cotton blend material tailored for everyday comfort.",
    currentColor: "Orange",
    currentSize: "L",
    colors: [
      {
        name: "Orange",
        hex: "#b82804",
        front: "assets/images/orange_front.jpg",
        back: "assets/images/orange_back.jpg"
      },
      {
        name: "Purple",
        hex: "#4b0c4e",
        front: "assets/images/purple_front.jpg",
        back: "assets/images/purple_back.jpg"
      },
      {
        name: "Navy Blue",
        hex: "#04195a",
        front: "assets/images/navy_front.jpg",
        back: "assets/images/navy_back.jpg"
      }
    ],
    sizes: ["M", "L", "XL"],
    stock: 19
  },
  {
    id: 3,
    name: "Half Sleeves Solid Regular Fit T-Shirt - Midnight Navy",
    slug: "mens-t-shirt-navy",
    categoryId: 3,
    categoryName: "Cotton Blend Regular Fit",
    price: 179.00,
    mrp: 499.00,
    discountPercent: 64,
    rating: 4.9,
    reviewsCount: 56,
    isNew: true,
    isBestSeller: true,
    description: "Deep, elegant navy blue tone that pairs perfectly with denim, shorts, or chinos. Ultra-durable double needle hem stitching.",
    currentColor: "Navy Blue",
    currentSize: "XL",
    colors: [
      {
        name: "Navy Blue",
        hex: "#04195a",
        front: "assets/images/navy_front.jpg",
        back: "assets/images/navy_back.jpg"
      },
      {
        name: "Black",
        hex: "#111111",
        front: "assets/images/black_front.jpg",
        back: "assets/images/black_front.jpg"
      },
      {
        name: "White",
        hex: "#f8f9fa",
        front: "assets/images/white_front.jpg",
        back: "assets/images/white_front.jpg"
      }
    ],
    sizes: ["M", "L", "XL"],
    stock: 35
  },
  {
    id: 4,
    name: "Minimalist Bio-Wash Regular Fit Cotton T-Shirt - Jet Black",
    slug: "mens-t-shirt-black",
    categoryId: 4,
    categoryName: "Round Neck Basics",
    price: 179.00,
    mrp: 499.00,
    discountPercent: 64,
    rating: 5.0,
    reviewsCount: 92,
    isNew: false,
    isBestSeller: true,
    description: "The timeless jet black tee. Made with 100% bio-washed combed cotton blend for zero lint and maximum color fastness.",
    currentColor: "Black",
    currentSize: "M",
    colors: [
      {
        name: "Black",
        hex: "#111111",
        front: "assets/images/black_front.jpg",
        back: "assets/images/black_front.jpg"
      },
      {
        name: "Navy Blue",
        hex: "#04195a",
        front: "assets/images/navy_front.jpg",
        back: "assets/images/navy_back.jpg"
      },
      {
        name: "Purple",
        hex: "#4b0c4e",
        front: "assets/images/purple_front.jpg",
        back: "assets/images/purple_back.jpg"
      }
    ],
    sizes: ["M", "L", "XL"],
    stock: 44
  },
  {
    id: 5,
    name: "Breathable Daily Essentials Plain Crew Neck T-Shirt - Crisp White",
    slug: "mens-t-shirt-white",
    categoryId: 5,
    categoryName: "Summer Casuals",
    price: 179.00,
    mrp: 499.00,
    discountPercent: 64,
    rating: 4.7,
    reviewsCount: 38,
    isNew: true,
    isBestSeller: false,
    description: "Clean, crisp white cotton t-shirt designed for hot Indian summers. Soft rib knit collar that doesn't sag.",
    currentColor: "White",
    currentSize: "L",
    colors: [
      {
        name: "White",
        hex: "#f8f9fa",
        front: "assets/images/white_front.jpg",
        back: "assets/images/white_front.jpg"
      },
      {
        name: "Black",
        hex: "#111111",
        front: "assets/images/black_front.jpg",
        back: "assets/images/black_front.jpg"
      },
      {
        name: "Orange",
        hex: "#b82804",
        front: "assets/images/orange_front.jpg",
        back: "assets/images/orange_back.jpg"
      }
    ],
    sizes: ["M", "L", "XL"],
    stock: 22
  },
  {
    id: 6,
    name: "Royal Heritage Solid Plain Round Neck T-Shirt - Rich Purple",
    slug: "mens-t-shirt-purple-edition",
    categoryId: 1,
    categoryName: "Men's T-Shirt",
    price: 179.00,
    mrp: 499.00,
    discountPercent: 64,
    rating: 4.9,
    reviewsCount: 67,
    isNew: true,
    isBestSeller: true,
    description: "Exclusive rich royal purple shade. Tailored to fit comfortably with clean shoulders and relaxed sleeves.",
    currentColor: "Purple",
    currentSize: "XL",
    colors: [
      {
        name: "Purple",
        hex: "#4b0c4e",
        front: "assets/images/purple_front.jpg",
        back: "assets/images/purple_back.jpg"
      },
      {
        name: "Orange",
        hex: "#b82804",
        front: "assets/images/orange_front.jpg",
        back: "assets/images/orange_back.jpg"
      },
      {
        name: "Navy Blue",
        hex: "#04195a",
        front: "assets/images/navy_front.jpg",
        back: "assets/images/navy_back.jpg"
      }
    ],
    sizes: ["M", "L", "XL"],
    stock: 16
  }
];

// Helper functions for state
function getStoredCart() {
  try {
    const raw = localStorage.getItem('nool_cart');
    return raw ? JSON.parse(raw) : [];
  } catch (e) {
    return [];
  }
}

function saveCart(cart) {
  localStorage.setItem('nool_cart', JSON.stringify(cart));
}

function getStoredWishlist() {
  try {
    const raw = localStorage.getItem('nool_wishlist');
    return raw ? JSON.parse(raw) : [];
  } catch (e) {
    return [];
  }
}

function saveWishlist(list) {
  localStorage.setItem('nool_wishlist', JSON.stringify(list));
}

function getStoredCompare() {
  try {
    const raw = localStorage.getItem('nool_compare');
    return raw ? JSON.parse(raw) : [];
  } catch (e) {
    return [];
  }
}

function saveCompare(list) {
  localStorage.setItem('nool_compare', JSON.stringify(list));
}
