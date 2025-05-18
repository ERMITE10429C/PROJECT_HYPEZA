document.addEventListener('DOMContentLoaded', function() {
  // Insert the preview section after the Premium Fabrics section
  const premiumFabricsSection = document.querySelector('.premium-fabrics');
  const previewSectionHTML = `
    <section class="suit-preview-section">
      <div class="container">
        <div class="section-title">
          <h2>Your Custom Suit Preview</h2>
        </div>
        <div class="preview-container">
          <div class="suit-preview">
            <div class="suit-image-container">
              <img id="suit-preview-image" src="images/suits/default-suit.webp" alt="Suit Preview">
            </div>
            <div class="preview-details">
              <h3>Selected Options</h3>
              <ul id="selected-options">
                <li><span class="black_text">Silhouette:</span> <span>Not selected</span></li>
                <li><span class="black_text">Button Style:</span> <span>Not selected</span></li>
                <li><span class="black_text">Fabric:</span> <span>Not selected</span></li>
              </ul>
              <button id="add-to-cart" class="checkout-btn">Add to Cart - $2,500</button>
            </div>
          </div>
        </div>
      </div>
    </section>
  `;

  premiumFabricsSection.insertAdjacentHTML('afterend', previewSectionHTML);

  // Store the current selections
  const selectedOptions = {
    fit: null,
    buttons: null,
    fabric: null
  };

  // Handle option item clicks (silhouette and buttons)
  // Handle option item clicks (silhouette and buttons)
  const optionItems = document.querySelectorAll('.option-item');
  optionItems.forEach(item => {
    item.addEventListener('click', function() {
      const category = this.dataset.category;
      const value = this.dataset.value;

      // Remove selected class from all items in this category
      document.querySelectorAll(`.option-item[data-category="${category}"]`).forEach(el => {
        el.classList.remove('selected');
      });

      // Add selected class to clicked item
      this.classList.add('selected');

      // Update selected options
      selectedOptions[category] = value;

      // Update the preview
      updatePreview();

      // Update selected options display text - TARGET THE SECOND SPAN
      const displayText = this.querySelector('h4').textContent;
      document.querySelector(`#selected-options li:nth-child(${category === 'fit' ? 1 : 2}) span:nth-child(2)`).textContent = displayText;
    });
  });

  // Handle fabric swatch clicks
  const fabricSwatches = document.querySelectorAll('.fabric-swatch');
  fabricSwatches.forEach(swatch => {
    swatch.addEventListener('click', function() {
      const fabric = this.dataset.fabric;

      // Remove selected class from all swatches
      document.querySelectorAll('.fabric-swatch').forEach(el => {
        el.classList.remove('selected');
      });

      // Add selected class to clicked swatch
      this.classList.add('selected');

      // Update selected options
      selectedOptions.fabric = fabric;

      // Update the preview
      updatePreview();

      // Update selected options display text - TARGET THE SECOND SPAN
      const fabricName = this.closest('.fabric-category').querySelector('h3').textContent;
      document.querySelector(`#selected-options li:nth-child(3) span:nth-child(2)`).textContent = fabricName;
    });
  });

  // Function to update the preview image based on selected options
  function updatePreview() {
    const previewImage = document.getElementById('suit-preview-image');

    // Only update if all options are selected
    if (selectedOptions.fit && selectedOptions.buttons && selectedOptions.fabric) {
      // Construct image filename based on selections
      // Format: fit-buttons-fabric.webp
      // Example: classic-two-wool.webp or slim-three-cashmere.webp
      const imageName = `${selectedOptions.fit}-${selectedOptions.buttons}-${selectedOptions.fabric}.webp`;
      previewImage.src = `images/suits/${imageName}`;
    }
  }

  // Add to cart functionality
  const addToCartButton = document.getElementById('add-to-cart');
  addToCartButton.addEventListener('click', function() {
    // Check if all options are selected
    const allSelected = Object.values(selectedOptions).every(value => value !== null);

    if (allSelected) {
      alert('Custom suit added to your cart!');
      // Here you would typically add the product to the cart
      document.querySelector('.cart').classList.add('open');
    } else {
      alert('Please select all options before adding to cart.');
    }
  });

  // Cart toggle functionality
  const cartToggle = document.querySelector('.panier');
  const cartClose = document.querySelector('.cart-close');

  if (cartToggle) {
    cartToggle.addEventListener('click', function(e) {
      e.preventDefault();
      document.querySelector('.cart').classList.add('open');
    });
  }

  if (cartClose) {
    cartClose.addEventListener('click', function() {
      document.querySelector('.cart').classList.remove('open');
    });
  }
});