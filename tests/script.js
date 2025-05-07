// Slider functionality
let currentIndex = 0;
const images = document.querySelectorAll('.background-categorie');
const dots = document.querySelectorAll('.dot');
const ringProgress = document.querySelector('.ring-progress');

function updateDots() {
  dots.forEach((dot, index) => {
    dot.classList.toggle('active', index === currentIndex);
  });
}

function changeBackground(direction) {
  images[currentIndex].classList.remove('active');
  currentIndex = (currentIndex + direction + images.length) % images.length;
  images[currentIndex].classList.add('active');
  updateDots();
  resetRingAnimation();
}

function goToSlide(index) {
  images[currentIndex].classList.remove('active');
  currentIndex = index;
  images[currentIndex].classList.add('active');
  updateDots();
  resetRingAnimation();
}

function resetRingAnimation() {
  ringProgress.style.animation = 'none';
  requestAnimationFrame(() => {
    ringProgress.style.animation = 'spinRing 6s linear infinite';
  });
}

// Auto-advance slider
setInterval(() => {
  changeBackground(1);
}, 6000);

// Initialize ring animation
document.addEventListener('DOMContentLoaded', () => {
  resetRingAnimation();
});

// Scroll effects
const body = document.body;
const video = document.getElementById('scrollingVideo');

window.addEventListener('scroll', () => {
  // Header scroll effect
  if (window.scrollY > 50) {
    body.classList.add('scrolled');
  } else {
    body.classList.remove('scrolled');
  }

  // Video scroll effect
  if (video) {
    const scrollPosition = window.scrollY;
    const windowHeight = window.innerHeight;
    const videoBounds = video.getBoundingClientRect();

    const opacity = Math.max(0, Math.min(1, 1 - Math.max(videoBounds.top, 0) / (windowHeight * 0.75)));
    video.style.opacity = opacity;

    const scale = 1 + ((1 - opacity) * 0.2);
    video.style.transform = `scale(${Math.min(1.3, scale)})`;
    video.style.clipPath = `inset(${(1 - opacity) * 5}% 0 ${(1 - opacity) * 5}% 0)`;
  }
});