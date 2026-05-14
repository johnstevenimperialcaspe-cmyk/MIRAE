(function () {
  var cartButtons = Array.prototype.slice.call(
    document.querySelectorAll(".cart-btn")
  );

  var cartPanel = document.getElementById("cart-panel");
  var cartOverlay = document.getElementById("cart-overlay");
  var cartItemsEl = document.getElementById("cart-items");
  var cartTotalEl = document.getElementById("cart-total");
  var cartCheckoutBtn = document.getElementById("cart-checkout-btn");
  var cartCloseBtn = document.getElementById("cart-close-btn");
  var cartOpenBtn = document.getElementById("cart-open-btn");
  var cartBadgeEl = document.getElementById("cart-badge");

  if (!cartPanel || !cartItemsEl || !cartTotalEl) return;

  var cart = new Map();
  
  // Load from localStorage
  try {
    var stored = JSON.parse(localStorage.getItem('mirae_cart') || '[]');
    stored.forEach(function(item) {
      cart.set(item.id, item);
    });
  } catch(e) { console.error('Cart load error', e); }

  function saveCart() {
    localStorage.setItem('mirae_cart', JSON.stringify(Array.from(cart.values())));
  }

  function formatPHP(amount) {
    var num = Number(amount) || 0;
    return "PHP " + num.toFixed(2);
  }

  function isCartOpen() {
    return cartPanel.classList.contains("cart-panel--open");
  }

  function openCart() {
    cartPanel.classList.add("cart-panel--open");
    cartOverlay && cartOverlay.classList.add("cart-overlay--open");
    cartPanel.setAttribute("aria-hidden", "false");
    document.body.classList.add("cart-open");

    if (cartCloseBtn) cartCloseBtn.focus();
  }

  function closeCart() {
    cartPanel.classList.remove("cart-panel--open");
    cartOverlay && cartOverlay.classList.remove("cart-overlay--open");
    cartPanel.setAttribute("aria-hidden", "true");
    document.body.classList.remove("cart-open");
  }

  cartOpenBtn &&
    cartOpenBtn.addEventListener("click", function () {
      openCart();
    });

  function getPayloadFromBtn(btn) {
    var dataset = btn.dataset || {};
    return {
      id: dataset.cartId || "unknown",
      name: dataset.cartName || "Product",
      size: dataset.cartSize || "",
      unitPrice: parseFloat(dataset.cartPrice || "0") || 0,
      image: dataset.cartImage || "",
    };
  }

  function addToCart(payload) {
    if (!payload || !payload.id) return;
    var existing = cart.get(payload.id);
    if (existing) {
      existing.qty += 1;
      return;
    }

    cart.set(payload.id, {
      id: payload.id,
      name: payload.name,
      size: payload.size,
      unitPrice: payload.unitPrice,
      image: payload.image,
      qty: 1,
    });
    saveCart();
  }

  function setItemQty(id, delta) {
    var existing = cart.get(id);
    if (!existing) return;

    existing.qty += delta;
    if (existing.qty <= 0) {
      cart.delete(id);
    }
    saveCart();
  }

  function renderCart() {
    var entries = Array.from(cart.values());

    cartItemsEl.innerHTML = "";

    var totalQty = entries.reduce(function (sum, item) {
      return sum + item.qty;
    }, 0);

    var total = entries.reduce(function (sum, item) {
      return sum + item.unitPrice * item.qty;
    }, 0);

    cartTotalEl.textContent = "Total: " + formatPHP(total);
    if (cartBadgeEl) cartBadgeEl.textContent = String(totalQty);

    if (!entries.length) {
      cartCheckoutBtn && (cartCheckoutBtn.disabled = true);
      cartItemsEl.innerHTML =
        '<div class="cart-empty">Your cart is empty.</div>';
      return;
    }

    cartCheckoutBtn && (cartCheckoutBtn.disabled = false);

    entries.forEach(function (item) {
      var lineTotal = item.unitPrice * item.qty;

      var itemEl = document.createElement("div");
      itemEl.className = "cart-item";
      itemEl.setAttribute("data-cart-item-id", item.id);

      itemEl.innerHTML =
        '<img class="cart-item-img" src="' +
        item.image +
        '" alt="' +
        item.name +
        '">' +
        '<div class="cart-item-info">' +
        '<div class="cart-item-name">' +
        item.name +
        "</div>" +
        (item.size
          ? '<p class="cart-item-meta">Size: ' + item.size + "</p>"
          : "") +
        '<div class="cart-item-line-total">Total: ' +
        formatPHP(lineTotal) +
        "</div>" +
        '<div class="cart-qty-row">' +
        '<button type="button" class="cart-qty-btn cart-qty-minus" aria-label="Decrease quantity" data-cart-item-id="' +
        item.id +
        '">-</button>' +
        '<div class="cart-qty" aria-label="Quantity">' +
        item.qty +
        "</div>" +
        '<button type="button" class="cart-qty-btn cart-qty-plus" aria-label="Increase quantity" data-cart-item-id="' +
        item.id +
        '">+</button>' +
        "</div>" +
        "</div>";

      cartItemsEl.appendChild(itemEl);
    });
  }

  cartButtons.forEach(function (btn) {
    btn.addEventListener("click", function () {
      var payload = getPayloadFromBtn(btn);
      addToCart(payload);
      renderCart();
      animateFlyToNav(btn);
    });
  });

  // Buy Now Buttons
  document.querySelectorAll(".buy-now-btn").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var cartBtn = btn.parentElement.querySelector(".cart-btn");
      if (cartBtn) {
        var payload = getPayloadFromBtn(cartBtn);
        addToCart(payload);
        saveCart();
        window.location.href = "user/checkout.php";
      }
    });
  });

  // Checkout Button
  cartCheckoutBtn && cartCheckoutBtn.addEventListener("click", function() {
    window.location.href = "user/checkout.php";
  });

  function prefersReducedMotion() {
    return window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  }

  function animateFlyToNav(fromBtn) {
    if (!fromBtn || !cartOpenBtn || prefersReducedMotion()) return;

    var fromImg = fromBtn.querySelector("img");
    if (!fromImg) return;

    var fromRect = fromBtn.getBoundingClientRect();
    var toRect = cartOpenBtn.getBoundingClientRect();

    var fromCenterX = fromRect.left + fromRect.width / 2;
    var fromCenterY = fromRect.top + fromRect.height / 2;
    var toCenterX = toRect.left + toRect.width / 2;
    var toCenterY = toRect.top + toRect.height / 2;

    var fly = fromImg.cloneNode(true);
    fly.style.position = "fixed";
    fly.style.left = fromCenterX + "px";
    fly.style.top = fromCenterY + "px";
    fly.style.width = Math.max(16, Math.round(fromRect.width * 0.45)) + "px";
    fly.style.height = "auto";
    fly.style.zIndex = "60";
    fly.style.pointerEvents = "none";
    fly.style.transform = "translate3d(-50%, -50%, 0) scale(1)";
    fly.style.opacity = "1";
    fly.style.filter = fromImg.style.filter || "";

    document.body.appendChild(fly);

    void fly.getBoundingClientRect();

    var dx = toCenterX - fromCenterX;
    var dy = toCenterY - fromCenterY;

    fly.style.transition =
      "transform 650ms cubic-bezier(0.16, 1, 0.3, 1), opacity 450ms ease";

    window.requestAnimationFrame(function () {
      fly.style.transform =
        "translate3d(calc(-50% + " +
        dx +
        "px), calc(-50% + " +
        dy +
        "px), 0) scale(0.15)";
      fly.style.opacity = "0";
    });

    fly.addEventListener(
      "transitionend",
      function () {
        if (fly && fly.parentNode) fly.parentNode.removeChild(fly);
      },
      { once: true }
    );

    cartOpenBtn.classList.remove("cart-nav-btn--bump");
    cartOpenBtn.getBoundingClientRect();
    cartOpenBtn.classList.add("cart-nav-btn--bump");
  }

  cartCloseBtn &&
    cartCloseBtn.addEventListener("click", function () {
      closeCart();
    });

  cartOverlay &&
    cartOverlay.addEventListener("click", function () {
      closeCart();
    });

  document.addEventListener("keydown", function (e) {
    if (!isCartOpen()) return;
    if (e.key === "Escape") closeCart();
  });

  cartItemsEl.addEventListener("click", function (e) {
    var target = e.target;
    if (!target || !target.closest) return;

    var btn = target.closest(".cart-qty-btn");
    if (!btn) return;

    var id = btn.dataset.cartItemId;
    if (!id) return;

    if (btn.classList.contains("cart-qty-plus")) {
      setItemQty(id, 1);
    } else if (btn.classList.contains("cart-qty-minus")) {
      setItemQty(id, -1);
    }

    renderCart();
  });

  renderCart();
})();

const data = [
    {
        number: "1",
        title: "Cafe and Restaurants",
        image: "images/19.png"
    },
    {
        number: "2",
        title: "Living Room and Bedroom",
        image: "images/20.png" // placeholder
    },
    {
        number: "3",
        title: "Bathroom and Toilet Room",
        image: "images/21.png" // placeholder
    },
    {
        number: "4",
        title: "Washing Clothes and Swimwear",
        image: "images/22.png" // placeholder
    },
    {
        number: "5",
        title: "Beach and Pool",
        image: "images/23.png" // placeholder
    },
    {
        number: "6",
        title: "After Island Hopping",
        image: "images/24.png" // placeholder
    }
];

const buttons = document.querySelectorAll('.nav-btn');
const mainImage = document.getElementById('main-image');
const imageContainer = document.getElementById('image-container');

buttons.forEach(btn => {
    btn.addEventListener('click', () => {
        // Remove active class from all
        buttons.forEach(b => b.classList.remove('active'));

        // Add active class to clicked
        btn.classList.add('active');

        const index = btn.getAttribute('data-index');
        const item = data[index];

        // Add fade out effect
        imageContainer.style.opacity = 0;
        imageContainer.style.transform = 'scale(0.98)';

        setTimeout(() => {
            mainImage.src = item.image;

            // Wait for image to load before fading back in
            mainImage.onload = () => {
                imageContainer.style.opacity = 1;
                imageContainer.style.transform = 'scale(1)';
            };

            // Fallback if image is already cached or fails
            setTimeout(() => {
                imageContainer.style.opacity = 1;
                imageContainer.style.transform = 'scale(1)';
            }, 100);

        }, 300); // Wait for fade out
    });
});
