

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
    setTimeout(() => (ringProgress.style.animation = 'spinRing 6s linear infinite'), 10);
}

    setInterval(() => {
    changeBackground(1);
}, 6000);

    document.addEventListener('DOMContentLoaded', () => {
    resetRingAnimation();
});

        // JavaScript for handling scroll-based class toggling
        const heroText = document.querySelector('.hero-text');
        const navbarText = document.querySelector('.navbar-text');
        const body = document.body;

        window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
        body.classList.add('scrolled');
    } else {
        body.classList.remove('scrolled');
    }
    });



