// Cinematic Horizontal Showcase
document.addEventListener('DOMContentLoaded', function() {
  class CinematicShowcase {
    constructor() {
      this.showcase = document.querySelector('.cinematic-showcase');
      if (!this.showcase) return;

      this.track = document.querySelector('.cinematic-track');
      this.prevBtn = document.querySelector('.cinematic-prev');
      this.nextBtn = document.querySelector('.cinematic-next');
      this.progressBar = document.querySelector('.cinematic-progress-bar');
      this.currentSlideElement = document.querySelector('.current-slide');
      this.totalSlidesElement = document.querySelector('.total-slides');

      this.items = [];
      this.currentIndex = 0;
      this.totalItems = 0;
      this.autoplayInterval = null;
      this.touchStartX = 0;
      this.touchEndX = 0;
      this.isDragging = false;
      this.initialPosition = 0;
      this.currentPosition = 0;

      this.init();
    }

    init() {
      this.loadItems();
      this.setupEventListeners();
      this.goToSlide(0);
      this.updateProgressBar();
      this.startAutoplay();
    }

    loadItems() {
      const trendingItems = [
        {
          image: 'images/designer_suit.webp',
          title: 'Signature Tailored Suit',
          description: 'Impeccably crafted from premium Italian wool, our signature suit offers refined elegance with modern details.',
          price: '$1,895'
        },
        {
          image: 'images/trousers.webp',
          title: 'Leather Weekend Duffle',
          description: 'Handcrafted from full-grain calfskin leather with brushed gold hardware and ultra-suede interior.',
          price: '$1,250'
        },
        {
          image: 'images/mens_collec_watch.webp',
          title: 'Limited Edition Chronograph',
          description: 'Swiss-made mechanical movement housed in a 42mm case with sapphire crystal and exhibition caseback.',
          price: '$3,750'
        },
        {
          image: 'images/coat_.webp',
          title: 'Cashmere Overcoat',
          description: 'Double-faced pure cashmere with horn buttons and a half-canvas construction for perfect drape.',
          price: '$2,495'
        },
        {
          image: 'images/shirt_black.webp',
          title: 'Italian Leather Oxfords',
          description: 'Goodyear-welted construction ensures these hand-polished calfskin shoes will last for years.',
          price: '$875'
        }
      ];

      // Create items in the showcase
      trendingItems.forEach((item, index) => {
        const itemElement = document.createElement('div');
        itemElement.className = 'cinematic-item';
        itemElement.innerHTML = `
          <div class="cinematic-media">
            <img src="${item.image}?auto=format&fit=crop&w=800&q=80" alt="${item.title}">
          </div>
          <div class="cinematic-content">
            <h2 class="cinematic-title">${item.title}</h2>
            <p class="cinematic-description">${item.description}</p>
            <div class="cinematic-price">${item.price}</div>
            <a href="#" class="cinematic-btn">
              Shop Now
              <svg viewBox="0 0 24 24" width="18" height="18">
                <path fill="currentColor" d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z"></path>
              </svg>
            </a>
          </div>
        `;

        this.track.appendChild(itemElement);
        this.items.push(itemElement);
      });

      this.totalItems = this.items.length;
      this.totalSlidesElement.textContent = this.totalItems;
    }

    setupEventListeners() {
      // Navigation buttons
      if (this.prevBtn) {
        this.prevBtn.addEventListener('click', () => {
          this.prev();
          this.restartAutoplay();
        });
      }

      if (this.nextBtn) {
        this.nextBtn.addEventListener('click', () => {
          this.next();
          this.restartAutoplay();
        });
      }

      // Touch and mouse events
      this.track.addEventListener('touchstart', (e) => {
        this.touchStart(e.touches[0].clientX);
        this.stopAutoplay();
      }, { passive: true });

      this.track.addEventListener('touchmove', (e) => {
        if (!this.isDragging) return;
        this.touchMove(e.touches[0].clientX);
      }, { passive: true });

      this.track.addEventListener('touchend', (e) => {
        this.touchEnd();
        this.restartAutoplay();
      });

      this.track.addEventListener('mousedown', (e) => {
        this.touchStart(e.clientX);
        this.stopAutoplay();
        e.preventDefault();
      });

      window.addEventListener('mousemove', (e) => {
        if (!this.isDragging) return;
        this.touchMove(e.clientX);
      });

      window.addEventListener('mouseup', () => {
        if (!this.isDragging) return;
        this.touchEnd();
        this.restartAutoplay();
      });

      // Pause autoplay on hover
      this.showcase.addEventListener('mouseenter', () => this.stopAutoplay());
      this.showcase.addEventListener('mouseleave', () => this.restartAutoplay());

      // Handle resize
      window.addEventListener('resize', () => {
        this.goToSlide(this.currentIndex);
      });

      // Keyboard navigation
      document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
          this.prev();
          this.restartAutoplay();
        } else if (e.key === 'ArrowRight') {
          this.next();
          this.restartAutoplay();
        }
      });
    }

    touchStart(clientX) {
      this.isDragging = true;
      this.touchStartX = clientX;
      this.initialPosition = this.currentPosition;

      this.track.style.transition = 'none';
    }

    touchMove(clientX) {
      const diff = clientX - this.touchStartX;
      this.track.style.transform = `translateX(${this.initialPosition + diff}px)`;
    }

    touchEnd() {
      this.isDragging = false;
      this.track.style.transition = 'transform 1.2s cubic-bezier(0.23, 1, 0.32, 1)';

      // Get current position after drag
      const translateX = parseFloat(this.track.style.transform.replace(/[^-\d.]/g, '') || 0);

      // Calculate which item should be current after the drag
      const itemWidth = this.items[0].offsetWidth;
      const dragDistance = translateX - this.initialPosition;

      if (Math.abs(dragDistance) > itemWidth * 0.2) {
        if (dragDistance > 0) {
          this.prev();
        } else {
          this.next();
        }
      } else {
        // Go back to current slide if drag wasn't far enough
        this.goToSlide(this.currentIndex);
      }
    }

    goToSlide(index) {
      this.items.forEach((item, i) => {
        item.classList.toggle('active', i === index);
      });

      const itemWidth = this.items[0].offsetWidth;
      this.currentPosition = -index * itemWidth;
      this.track.style.transform = `translateX(${this.currentPosition}px)`;

      this.currentIndex = index;
      this.currentSlideElement.textContent = index + 1;
      this.updateProgressBar();
    }

    updateProgressBar() {
      const progress = ((this.currentIndex + 1) / this.totalItems) * 100;
      this.progressBar.style.width = `${progress}%`;
    }

    prev() {
      let index = this.currentIndex - 1;
      if (index < 0) index = this.totalItems - 1;
      this.goToSlide(index);
    }

    next() {
      let index = this.currentIndex + 1;
      if (index >= this.totalItems) index = 0;
      this.goToSlide(index);
    }

    startAutoplay() {
      this.autoplayInterval = setInterval(() => this.next(), 6000);
    }

    stopAutoplay() {
      clearInterval(this.autoplayInterval);
    }

    restartAutoplay() {
      this.stopAutoplay();
      this.startAutoplay();
    }
  }

  // Initialize the showcase
  new CinematicShowcase();
});