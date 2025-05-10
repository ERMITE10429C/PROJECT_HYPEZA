// Create a cartManager.js file with these functions

/**
 * Add item to cart
 * @param {Object} item - The product to add to cart
 */
function addToCart(item) {
  // Get existing cart or initialize empty array
  let cart = JSON.parse(localStorage.getItem('cartItems')) || [];

  // Add new item to cart
  cart.push(item);

  // Save updated cart to localStorage
  localStorage.setItem('cartItems', JSON.stringify(cart));

  // Update cart count display if needed
  updateCartDisplay();
}



/**
 * Remove item from cart by index
 * @param {number} index - Index of item to remove
 */
function removeCartItem(index) {
  // Get current cart
  let cart = JSON.parse(localStorage.getItem('cartItems')) || [];

  // Remove the item at specified index
  if (index >= 0 && index < cart.length) {
    cart.splice(index, 1);

    // Update localStorage
    localStorage.setItem('cartItems', JSON.stringify(cart));

    // Reload cart display
    loadCartItems();
  }
}
/**
 * Fix image paths for local images
 * @param {string} path - The image path to fix
 * @returns {string} - The corrected image path
 */
function fixImagePath(path) {
  // If already an absolute URL (https://, http://), leave it alone
  if (path && path.match(/^(https?:)?\/\//)) {
    return path;
  }

  // For local files, ensure they have the correct path
  if (path && !path.includes('mens_collec/images/')) {
    // If path includes just 'images/', add the collection prefix
    if (path.includes('images/')) {
      return 'mens_collec/' + path;
    }
    // If path is just a filename, add the full path
    else if (!path.includes('/')) {
      return 'mens_collec/images/' + path;
    }
  }

  return path;
}

/**
 * Load cart items and display in summary
 */
function loadCartItems() {
  // Get cart items from localStorage
  let cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];

  console.log('Cart items loaded:', cartItems); // Debug log

  // Display items in the order summary if we're on checkout page
  const summaryItemsContainer = document.getElementById('summary-items');
  if (summaryItemsContainer) {
    summaryItemsContainer.innerHTML = '';

    let subtotal = 0;

    if (cartItems.length === 0) {
      summaryItemsContainer.innerHTML = '<p>Your cart is empty</p>';

      // Reset all price displays to zero when cart is empty
      document.getElementById('subtotal-amount').textContent = '$0.00';
      document.getElementById('shipping-amount').textContent = '$0.00';
      document.getElementById('total-amount').textContent = '$0.00';

    } else {
      cartItems.forEach((item, index) => {
        // Create HTML for each item
        const itemElement = document.createElement('div');
        itemElement.className = 'summary-item';

        // Fix the image path
        const fixedImagePath = fixImagePath(item.image);
        console.log('Image path fixed:', item.image, '->', fixedImagePath);

        // Convert price string to number (remove $ and commas)
        const price = parseFloat(item.price.replace(/[$,]/g, ''));
        const quantity = item.quantity || 1; // Default to 1 if not specified
        subtotal += price * quantity;

        // Create available sizes array
        const sizes = ['XS', 'S', 'M', 'L', 'XL'];
        let sizeOptions = '';
        sizes.forEach(size => {
          sizeOptions += `<option value="${size}" ${item.size === size ? 'selected' : ''}>${size}</option>`;
        });

        // Create available colors array
        const colors = ['Black', 'Gold', 'White', 'Red', 'Blue'];
        let colorOptions = '';
        colors.forEach(color => {
          colorOptions += `<option value="${color}" ${item.color === color ? 'selected' : ''}>${color}</option>`;
        });

        itemElement.innerHTML = `
          <img src="${fixedImagePath}" alt="${item.title}" class="summary-item-image" onerror="console.error('Failed to load image:', this.src); this.src='./placeholder.png';">
          <div class="summary-item-details">
            <h4 class="summary-item-title">${item.title}</h4>
            <div class="summary-item-options">
              <div class="item-option">
                <label>Size:</label>
                <select class="size-select" data-index="${index}">
                  ${sizeOptions}
                </select>
              </div>
              <div class="item-option">
                <label>Color:</label>
                <select class="color-select" data-index="${index}">
                  ${colorOptions}
                </select>
              </div>
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

      // Add event listeners for the remove buttons
      document.querySelectorAll('.remove-item-btn').forEach(button => {
        button.addEventListener('click', function() {
          const index = parseInt(this.getAttribute('data-index'));
          removeCartItem(index);
        });
      });

      // Add event listeners for quantity controls
      document.querySelectorAll('.quantity-btn.minus').forEach(button => {
        button.addEventListener('click', function() {
          const index = parseInt(this.getAttribute('data-index'));
          updateCartItemQuantity(index, -1);
        });
      });

      document.querySelectorAll('.quantity-btn.plus').forEach(button => {
        button.addEventListener('click', function() {
          const index = parseInt(this.getAttribute('data-index'));
          updateCartItemQuantity(index, 1);
        });
      });

      document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
          const index = parseInt(this.getAttribute('data-index'));
          updateCartItemQuantity(index, 0, parseInt(this.value));
        });
      });

      // Add event listeners for size and color selects
      document.querySelectorAll('.size-select').forEach(select => {
        select.addEventListener('change', function() {
          const index = parseInt(this.getAttribute('data-index'));
          updateCartItemProperty(index, 'size', this.value);
        });
      });

      document.querySelectorAll('.color-select').forEach(select => {
        select.addEventListener('change', function() {
          const index = parseInt(this.getAttribute('data-index'));
          updateCartItemProperty(index, 'color', this.value);
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

  // Update cart icon/count if we're on main page
  updateCartDisplay();
}
/**
 * Update cart item quantity
 * @param {number} index - Index of item to update
 * @param {number} change - Amount to change (-1, +1)
 * @param {number|null} newQuantity - Direct quantity to set (optional)
 */
function updateCartItemQuantity(index, change, newQuantity = null) {
  // Get current cart
  let cart = JSON.parse(localStorage.getItem('cartItems')) || [];

  // Check if index is valid
  if (index >= 0 && index < cart.length) {
    // If quantity doesn't exist, initialize it to 1
    if (!cart[index].quantity) {
      cart[index].quantity = 1;
    }

    // If newQuantity is directly provided, use it
    if (newQuantity !== null) {
      cart[index].quantity = Math.max(1, newQuantity); // Minimum quantity is 1
    } else {
      // Otherwise adjust by change amount
      cart[index].quantity = Math.max(1, cart[index].quantity + change); // Minimum quantity is 1
    }

    // Update localStorage
    localStorage.setItem('cartItems', JSON.stringify(cart));

    // Reload cart display
    loadCartItems();
  }
}

/**
 * Update cart item property (size, color)
 * @param {number} index - Index of item to update
 * @param {string} property - Property to update (size, color)
 * @param {string} value - New value
 */
function updateCartItemProperty(index, property, value) {
  // Get current cart
  let cart = JSON.parse(localStorage.getItem('cartItems')) || [];

  // Check if index is valid
  if (index >= 0 && index < cart.length) {
    // Update the specified property
    cart[index][property] = value;

    // Update localStorage
    localStorage.setItem('cartItems', JSON.stringify(cart));

    // Reload cart display
    loadCartItems();
  }
}
/**
 * Update cart icon/count display
 */
function updateCartDisplay() {
  const cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
  const cartCount = document.querySelector('.cart-count');

  if (cartCount) {
    cartCount.textContent = cartItems.length;
    cartCount.style.display = cartItems.length > 0 ? 'block' : 'none';
  }
}

// Initialize cart functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  console.log('DOM loaded - initializing cart'); // Debug log

  // Load cart items
  loadCartItems();

  // Set up event listeners for "Add to Cart" buttons on product pages
  const addToCartButtons = document.querySelectorAll('.cart-btn');
  if (addToCartButtons) {
    addToCartButtons.forEach(button => {
      button.addEventListener('click', function() {
        const productCard = this.closest('.product-card');
        if (productCard) {
          // Get product details from data attributes or DOM
          const product = {
            id: productCard.dataset.id,
            title: productCard.dataset.title,
            price: productCard.dataset.price,
            image: productCard.dataset.image,
            color: document.querySelector('.color-option.active')?.dataset.color || 'Default',
            size: document.querySelector('.size-option.active')?.textContent || 'S'
          };

          addToCart(product);
        }
      });
    });
  }

  // Set up event listeners for quick view
  const quickViewButtons = document.querySelectorAll('.quick-view-btn');
  if (quickViewButtons) {
    quickViewButtons.forEach(button => {
      button.addEventListener('click', function() {
        // Quick view functionality here
      });
    });
  }
});