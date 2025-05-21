/**
 * Add item to cart
 * @param {Object} item - The product to add to cart
 */
function addToCart(item) {
  let cart = JSON.parse(localStorage.getItem('cartItems')) || [];

  const existingItemIndex = cart.findIndex(
    (cartItem) =>
      cartItem.id === item.id &&
      cartItem.size === item.size &&
      cartItem.color === item.color
  );

  if (existingItemIndex !== -1) {
    // Check if adding more exceeds max_per_order
    if (cart[existingItemIndex].quantity + 1 > item.max_per_order) {
      alert(`You cannot add more than ${item.max_per_order} of this product.`);
      return;
    }
    cart[existingItemIndex].quantity += 1;
  } else {
    item.quantity = 1;
    cart.push(item);
  }

  localStorage.setItem('cartItems', JSON.stringify(cart));
  updateCartDisplay();
}

/**
 * Update cart item quantity
 * @param {number} index - Index of item to update
 * @param {number} change - Amount to change (-1, +1)
 * @param {number|null} newQuantity - Direct quantity to set (optional)
 */
function updateCartItemQuantity(index, change, newQuantity = null) {
  let cart = JSON.parse(localStorage.getItem('cartItems')) || [];

  if (index >= 0 && index < cart.length) {
    const maxPerOrder = cart[index].max_per_order;

    if (newQuantity !== null) {
      if (newQuantity > maxPerOrder) {
        alert(`You cannot add more than ${maxPerOrder} of this product.`);
        return;
      }
      cart[index].quantity = Math.max(1, newQuantity);
    } else {
      const newQty = cart[index].quantity + change;
      if (newQty > maxPerOrder) {
        alert(`You cannot add more than ${maxPerOrder} of this product.`);
        return;
      }
      cart[index].quantity = Math.max(1, newQty);
    }

    localStorage.setItem('cartItems', JSON.stringify(cart));
    loadCartItems();
  }
}

  /**
   * Load cart items and display in summary
   */
  function loadCartItems() {
    // Get cart items from localStorage
    let cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];

    // Display items in the order summary if we're on checkout page
    const summaryItemsContainer = document.getElementById('summary-items');
    if (summaryItemsContainer) {
      summaryItemsContainer.innerHTML = '';

      let subtotal = 0;

      if (cartItems.length === 0) {
        summaryItemsContainer.innerHTML = '<p>Your cart is empty</p>';
        document.getElementById('subtotal-amount').textContent = '$0.00';
        document.getElementById('shipping-amount').textContent = '$0.00';
        document.getElementById('total-amount').textContent = '$0.00';
      } else {
        cartItems.forEach((item, index) => {
          const price = parseFloat(item.price.replace(/[$,]/g, ''));
          const quantity = item.quantity || 1;
          subtotal += price * quantity;

          const itemElement = document.createElement('div');
          itemElement.className = 'summary-item';
          itemElement.innerHTML = `
            <img src="${fixImagePath(item.image)}" alt="${item.title}" class="summary-item-image">
            <div class="summary-item-details">
              <h4 class="summary-item-title">${item.title}</h4>
              <div class="summary-item-options">
                <div class="item-option quantity-control">
                  <label>Qty:</label>
                  <div class="quantity-wrapper">
                    <button class="quantity-btn minus" data-index="${index}">-</button>
                    <input type="number" min="1" value="${quantity}" class="quantity-input" data-index="${index}">
                    <button class="quantity-btn plus" data-index="${index}">+</button>
                  </div>
                </div>
              </div>
              <p class="summary-item-price">$${(price * quantity).toFixed(2)}</p>
            </div>
            <button class="remove-item-btn" data-index="${index}">&times;</button>
          `;

          summaryItemsContainer.appendChild(itemElement);
        });

        // Add event listeners for quantity controls
        document.querySelectorAll('.quantity-btn.minus').forEach((button) => {
          button.addEventListener('click', function () {
            const index = parseInt(this.getAttribute('data-index'));
            updateCartItemQuantity(index, -1);
          });
        });

        document.querySelectorAll('.quantity-btn.plus').forEach((button) => {
          button.addEventListener('click', function () {
            const index = parseInt(this.getAttribute('data-index'));
            updateCartItemQuantity(index, 1);
          });
        });

        document.querySelectorAll('.quantity-input').forEach((input) => {
          input.addEventListener('change', function () {
            const index = parseInt(this.getAttribute('data-index'));
            updateCartItemQuantity(index, 0, parseInt(this.value));
          });
        });

        // Calculate shipping (free for orders over $500)
        const shipping = subtotal > 500 ? 0 : 15;
        const total = subtotal + shipping;

        // Update summary amounts
        document.getElementById('subtotal-amount').textContent = `$${subtotal.toFixed(2)}`;
        document.getElementById('shipping-amount').textContent = shipping === 0 ? 'Free' : `$${shipping.toFixed(2)}`;
        document.getElementById('total-amount').textContent = `$${total.toFixed(2)}`;
      }
    }

    // Update cart icon/count
    updateCartDisplay();
  }