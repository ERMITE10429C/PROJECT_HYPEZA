document.addEventListener('DOMContentLoaded', () => {
  // Animation pour le footer
  gsap.registerPlugin(ScrollTrigger);

  // Animation des colonnes du footer
  gsap.from('.footer-col', {
    y: 50,
    opacity: 0,
    stagger: 0.1,
    duration: 0.8,
    ease: "power2.out",
    scrollTrigger: {
      trigger: '.footer',
      start: 'top 80%',
    }
  });

  // Animation de la ligne du bas
  gsap.from('.footer-bottom', {
    opacity: 0,
    duration: 1,
    delay: 0.5,
    scrollTrigger: {
      trigger: '.footer',
      start: 'top 80%',
    }
  });

  // Effet hover sur les liens sociaux
  const socialLinks = document.querySelectorAll('.social-links a');
  socialLinks.forEach(link => {
    link.addEventListener('mouseenter', () => {
      gsap.to(link, {
        scale: 1.1,
        duration: 0.3,
        ease: "back.out(1.7)"
      });
    });

    link.addEventListener('mouseleave', () => {
      gsap.to(link, {
        scale: 1,
        duration: 0.3,
        ease: "power2.out"
      });
    });
  });
});