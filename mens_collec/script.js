// Common JS functionality for all HYPEZA pages

// DOM Elements
document.addEventListener('DOMContentLoaded', function() {
  // Mobile menu toggle
  const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
  const navMenu = document.querySelector('nav ul');

  if (mobileMenuBtn && navMenu) {
    mobileMenuBtn.addEventListener('click', function() {
      navMenu.classList.toggle('show');
    });
  }

// Color selection functionality

  const colorOptions = document.querySelectorAll('.color-option');


  if (colorOptions.length > 0) {
    colorOptions.forEach(option => {
      option.addEventListener('click', function() {
        // Remove active class from all options
        colorOptions.forEach(opt => opt.classList.remove('active'));
        // Add active class to clicked option
        this.classList.add('active');

        // You could update product info or image based on color
        const selectedColor = this.getAttribute('data-color');
        console.log('Selected color:', selectedColor);
      });
    });
  }


// Shopping cart functionality
const cartToggle = document.querySelector('.panier'); // Changed from .cart-toggle to .panier
const cart = document.querySelector('.cart');
const cartClose = document.querySelector('.cart-close');
const cartItems = document.querySelector('.cart-items');
const cartTotal = document.querySelector('.total-amount');
const body = document.body;

if (cartToggle && cart) {
  cartToggle.addEventListener('click', function(e) {
    e.preventDefault(); // Prevent the default link behavior
    cart.classList.add('open');
    body.style.overflow = 'hidden';
  });

  if (cartClose) {
    cartClose.addEventListener('click', function() {
      cart.classList.remove('open');
      body.style.overflow = '';
    });
  }
}

  // Product modal functionality
  const productCards = document.querySelectorAll('.product-card');
  const modal = document.querySelector('.modal');
  const modalClose = document.querySelector('.modal-close');

  if (productCards.length > 0 && modal) {
    productCards.forEach(card => {
      const quickViewBtn = card.querySelector('.quick-view-btn');
      if (quickViewBtn) {
          // In script.js, modify the quickViewBtn event handler:// In the quickViewBtn click handler, replace the thumbnail generation code with:
          quickViewBtn.addEventListener('click', function(e) {
            e.preventDefault();

            // Get product info from data attributes
            const productId = card.dataset.id;
            const productTitle = card.dataset.title;
            const productPrice = card.dataset.price;
            const productImage = card.dataset.image;
            const productDescription = card.dataset.description;

            // Update modal content
            const modalTitle = modal.querySelector('.product-detail-title');
            const modalPrice = modal.querySelector('.product-detail-price');
            const modalImage = modal.querySelector('.product-main-image');
            const modalDescription = modal.querySelector('.product-detail-description');

            if (modalTitle) modalTitle.textContent = productTitle;
            if (modalPrice) modalPrice.textContent = productPrice;
            if (modalImage) modalImage.src = productImage;
            if (modalDescription) modalDescription.textContent = productDescription;

            // Create zoomed thumbnails
            createZoomedThumbnails(productImage);

            // Add color option event listeners
            const colorOptions = modal.querySelectorAll('.color-option');
            colorOptions.forEach(option => {
              option.addEventListener('click', function() {
                // Remove active class from all options
                colorOptions.forEach(opt => opt.classList.remove('active'));
                // Add active class to clicked option
                this.classList.add('active');

                // Get selected color
                const selectedColor = this.getAttribute('data-color').toLowerCase();
                const colorImage = card.dataset[`color${selectedColor.charAt(0).toUpperCase() + selectedColor.slice(1)}`] || productImage;

                // If we have a color-specific image, update both main image and thumbnails
                if (colorImage !== productImage) {
                  modalImage.src = colorImage;
                  createZoomedThumbnails(colorImage);
                }
              });
            });

            // Show modal
            modal.style.display = 'flex';
            body.style.overflow = 'hidden';
          });

          // Update or add these helper functions if they're not already defined:
          function createZoomedThumbnails(mainImageSrc) {
            const thumbnailsContainer = document.querySelector('.product-thumbnails');
            thumbnailsContainer.innerHTML = '';

            // Add main image thumbnail (regular view)
            const mainThumbnail = document.createElement('img');
            mainThumbnail.src = mainImageSrc;
            mainThumbnail.classList.add('product-thumbnail', 'active');
            mainThumbnail.setAttribute('data-zoom', 'none');
            mainThumbnail.addEventListener('click', function() {
              updateMainProductImage(mainImageSrc, 'none');
              setActiveThumbnail(this);
            });
            thumbnailsContainer.appendChild(mainThumbnail);

            // Add zoomed top-left area thumbnail
            const topLeftThumbnail = document.createElement('img');
            topLeftThumbnail.src = mainImageSrc;
            topLeftThumbnail.classList.add('product-thumbnail');
            topLeftThumbnail.setAttribute('data-zoom', 'top-left');
            topLeftThumbnail.style.objectPosition = 'left top';
            topLeftThumbnail.style.objectFit = 'cover';
            topLeftThumbnail.addEventListener('click', function() {
              updateMainProductImage(mainImageSrc, 'top-left');
              setActiveThumbnail(this);
            });
            thumbnailsContainer.appendChild(topLeftThumbnail);

            // Add zoomed bottom-right area thumbnail
            const bottomRightThumbnail = document.createElement('img');
            bottomRightThumbnail.src = mainImageSrc;
            bottomRightThumbnail.classList.add('product-thumbnail');
            bottomRightThumbnail.setAttribute('data-zoom', 'bottom-right');
            bottomRightThumbnail.style.objectPosition = 'right bottom';
            bottomRightThumbnail.style.objectFit = 'cover';
            bottomRightThumbnail.addEventListener('click', function() {
              updateMainProductImage(mainImageSrc, 'bottom-right');
              setActiveThumbnail(this);
            });
            thumbnailsContainer.appendChild(bottomRightThumbnail);
          }

function updateMainProductImage(imageSrc, zoomPosition) {
  const mainImage = document.querySelector('.product-main-image');
  const detailImages = document.querySelector('.product-detail-images');
  const thumbnailsContainer = document.querySelector('.product-thumbnails');

  // Create a separate container for the main image if it doesn't exist
  let mainImageContainer = document.querySelector('.main-image-container');
  if (!mainImageContainer) {
    mainImageContainer = document.createElement('div');
    mainImageContainer.className = 'main-image-container';

    // Insert the container before the thumbnails
    detailImages.insertBefore(mainImageContainer, thumbnailsContainer);

    // Move the main image into this container
    mainImageContainer.appendChild(mainImage);
  }

  mainImage.src = imageSrc;

  // Reset zoom styles
  mainImage.style.objectFit = 'cover';
  mainImage.style.objectPosition = 'center';
  mainImage.style.transform = 'scale(1)';

  // Apply zoom based on position
  if (zoomPosition === 'top-left') {
    mainImage.style.objectPosition = 'left top';
    mainImage.style.transform = 'scale(1.8)';
  } else if (zoomPosition === 'bottom-right') {
    mainImage.style.objectPosition = 'right bottom';
    mainImage.style.transform = 'scale(1.8)';
  }
}

          function setActiveThumbnail(clickedThumbnail) {
            const thumbnails = document.querySelectorAll('.product-thumbnail');
            thumbnails.forEach(thumbnail => {
              thumbnail.classList.remove('active');
            });
            clickedThumbnail.classList.add('active');
          }
      }
    });

    if (modalClose) {
      modalClose.addEventListener('click', function() {
        modal.style.display = 'none';
        body.style.overflow = '';
      });
    }

    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
      if (e.target === modal) {
        modal.style.display = 'none';
        body.style.overflow = '';
      }
    });
  }

  // Size selection in product detail
  const sizeOptions = document.querySelectorAll('.size-option');
  if (sizeOptions.length > 0) {
    sizeOptions.forEach(option => {
      option.addEventListener('click', function() {
        // Remove active class from all options
        sizeOptions.forEach(opt => opt.classList.remove('active'));
        // Add active class to clicked option
        this.classList.add('active');
      });
    });
  }

  // Product thumbnails in modal
  const productThumbnails = document.querySelectorAll('.product-thumbnail');
  const productMainImage = document.querySelector('.product-main-image');

  if (productThumbnails.length > 0 && productMainImage) {
    productThumbnails.forEach(thumbnail => {
      thumbnail.addEventListener('click', function() {
        const imgSrc = this.src;
        productMainImage.src = imgSrc;

        // Update active thumbnail
        productThumbnails.forEach(thumb => thumb.classList.remove('active'));
        this.classList.add('active');
      });
    });
  }

// Add to cart functionality
const addToCartBtns = document.querySelectorAll('.add-to-cart, .product-btn.cart-btn');
let cartItemsCount = 0;

if (addToCartBtns.length > 0) {
  addToCartBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      // Get product info - in a real app, you'd get this from the product data
      const productElement = this.closest('.product-card, .product-detail');
      if (!productElement) return;

      let productTitle, productPrice, productImage, productSize, productColor;

      if (productElement.classList.contains('product-card')) {
        productTitle = productElement.querySelector('.product-title').textContent;
        productPrice = productElement.querySelector('.product-price').textContent;
        productImage = productElement.querySelector('.product-image').src;
        productSize = 'M'; // Default size
        productColor = 'Default'; // Default color
      } else {
        productTitle = productElement.querySelector('.product-detail-title').textContent;
        productPrice = productElement.querySelector('.product-detail-price').textContent;
        productImage = productElement.querySelector('.product-main-image').src;

        const activeSize = productElement.querySelector('.size-option.active');
        productSize = activeSize ? activeSize.textContent : 'M';

        const activeColor = productElement.querySelector('.color-option.active');
        productColor = activeColor ? activeColor.getAttribute('data-color') : 'Default';
      }

      // Add item to cart
      addItemToCart(productTitle, productPrice, productImage, productSize, productColor);

      // Show notification
      showNotification('Item added to cart');

      // Open cart if it's a direct add to cart
      if (cart && this.classList.contains('add-to-cart')) {
        cart.classList.add('open');
        body.style.overflow = 'hidden';
      }
    });
  });
}

// Function to add item to cart
function addItemToCart(title, price, image, size, color) {
  cartItemsCount++;

  // Update cart count
  const cartCount = document.querySelector('.cart-count');
  if (cartCount) {
    cartCount.textContent = cartItemsCount;
  }

  // Create cart item HTML
  const cartItemHTML = `
    <div class="cart-item">
      <img src="${image}" alt="${title}" class="cart-item-image">
      <div class="cart-item-details">
        <h4 class="cart-item-title">${title}</h4>
        <p class="cart-item-price">${price}</p>
        <p class="cart-item-options">Size: ${size} | Color: ${color}</p>
        <button class="cart-item-remove">Remove</button>
      </div>
    </div>
  `;

  // Add to cart
  if (cartItems) {
    cartItems.innerHTML += cartItemHTML;

    // Update cart total - in a real app, you'd calculate this based on actual prices
    const priceValue = parseFloat(price.replace(/[^0-9.-]+/g, ''));
    let currentTotal = cartTotal ? parseFloat(cartTotal.textContent.replace(/[^0-9.-]+/g, '')) : 0;
    currentTotal += priceValue;

    if (cartTotal) {
      cartTotal.textContent = `$${currentTotal.toFixed(2)}`;
    }

    // Add event listeners to new remove buttons
    const removeButtons = document.querySelectorAll('.cart-item-remove');
    removeButtons.forEach(button => {
      button.addEventListener('click', function() {
        const cartItem = this.closest('.cart-item');
        const itemPrice = cartItem.querySelector('.cart-item-price');
        const priceValue = parseFloat(itemPrice.textContent.replace(/[^0-9.-]+/g, ''));

        // Update total
        let currentTotal = cartTotal ? parseFloat(cartTotal.textContent.replace(/[^0-9.-]+/g, '')) : 0;
        currentTotal -= priceValue;

        if (cartTotal) {
          cartTotal.textContent = `$${currentTotal.toFixed(2)}`;
        }

        // Remove item
        cartItem.remove();

        // Update count
        cartItemsCount--;
        const cartCount = document.querySelector('.cart-count');
        if (cartCount) {
          cartCount.textContent = cartItemsCount;
        }
      });
    });
  }
}

  // Notification system
  function showNotification(message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    document.body.appendChild(notification);

    // Show and then hide after timeout
    setTimeout(() => {
      notification.classList.add('show');

      setTimeout(() => {
        notification.classList.remove('show');

        // Remove from DOM after fade out
        setTimeout(() => {
          notification.remove();
        }, 500);
      }, 2000);
    }, 100);
  }

  // Product filter functionality
  const filterBtns = document.querySelectorAll('.filter-btn');
  const productItems = document.querySelectorAll('.product-card');

  if (filterBtns.length > 0 && productItems.length > 0) {
    filterBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        // Update active button
        filterBtns.forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        const filterValue = this.dataset.filter;

        productItems.forEach(item => {
          if (filterValue === 'all') {
            item.style.display = 'block';
          } else {
            if (item.dataset.category === filterValue) {
              item.style.display = 'block';
            } else {
              item.style.display = 'none';
            }
          }
        });
      });
    });
  }

  // Newsletter form
  const newsletterForm = document.querySelector('.newsletter-form');
  if (newsletterForm) {
    newsletterForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const emailInput = this.querySelector('input');
      const email = emailInput.value;

      if (email && isValidEmail(email)) {
        showNotification('Thank you for subscribing!');
        emailInput.value = '';
      } else {
        showNotification('Please enter a valid email address');
      }
    });
  }

  // Email validation helper
  function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
  }

  // Parallax effect on scroll
  window.addEventListener('scroll', function() {
    const scrollPosition = window.scrollY;

    const parallaxElements = document.querySelectorAll('.parallax');
    parallaxElements.forEach(element => {
      const speed = element.dataset.speed || 0.5;
      element.style.transform = `translateY(${scrollPosition * speed}px)`;
    });
  });

  // Shimmer effect
  const shimmerElements = document.querySelectorAll('.shimmer');
  if (shimmerElements.length > 0) {
    shimmerElements.forEach(element => {
      element.addEventListener('mouseenter', function() {
        this.classList.add('active-shimmer');
      });

      element.addEventListener('mouseleave', function() {
        this.classList.remove('active-shimmer');
      });
    });
  }

  // Add animation classes as elements scroll into view
  const animatedElements = document.querySelectorAll('.animate-on-scroll');

  function checkScroll() {
    const triggerBottom = window.innerHeight * 0.8;

    animatedElements.forEach(element => {
      const elementTop = element.getBoundingClientRect().top;

      if (elementTop < triggerBottom) {
        const animation = element.dataset.animation || 'fade-in';
        element.classList.add(animation);
      }
    });
  }

  // Initial check and add scroll listener
  checkScroll();
  window.addEventListener('scroll', checkScroll);
});

// CSS for notification that's added via JS
const style = document.createElement('style');
style.textContent = `
  .notification {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background-color: var(--gold);
    color: var(--black);
    padding: 15px 25px;
    border-radius: 5px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    transform: translateY(100px);
    opacity: 0;
    transition: all 0.5s ease;
    z-index: 9999;
    font-family: 'Prata', serif;
  }
  
  .notification.show {
    transform: translateY(0);
    opacity: 1;
  }
`;
document.head.appendChild(style);

