<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact HYPEZA - Luxury Fashion & Streetwear</title>

  <meta name="description" content="Contact HYPEZA - We'd love to hear from you. Reach out for inquiries about our luxury fashion and streetwear collections.">
  <meta name="keywords" content="contact HYPEZA, HYPEZA customer service, luxury fashion contact, streetwear brand contact">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="https://hypza.tech/contact.html">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Prata&family=Roboto&family=Satisfy&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Prata&family=Cormorant+Garamond:wght@400;600&display=swap" rel="stylesheet">

  <!-- Stylesheets -->
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="./style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    /* Contact Page Specific Styles */
    body {
      background-color: #000;
      color: #fff;
      font-family: 'Cormorant Garamond', serif;
    }

    .contact-page {
      max-width: 1200px;
      margin: 120px auto 80px;
      padding: 0 20px;
    }

    .contact-header {
      text-align: center;
      margin-bottom: 60px;
    }

    .contact-header h1 {
      font-family: 'Prata', serif;
      font-size: 3rem;
      margin-bottom: 20px;
      color: #C89B3C;
    }

    .contact-header p {
      font-size: 1.2rem;
      max-width: 700px;
      margin: 0 auto;
      line-height: 1.6;
    }

    .contact-content {
      display: flex;
      flex-wrap: wrap;
      gap: 40px;
    }

    .contact-form-container {
      flex: 1;
      min-width: 300px;
    }

    .contact-info {
      flex: 0 0 300px;
    }

    .form-group {
      margin-bottom: 25px;
    }

    .form-label {
      display: block;
      margin-bottom: 8px;
      font-family: 'Prata', serif;
      color: #C89B3C;
    }

    .form-control {
      width: 100%;
      padding: 12px 15px;
      background-color: rgba(255, 255, 255, 0.05);
      border: 1px solid #333;
      color: #fff;
      font-family: 'Cormorant Garamond', serif;
      font-size: 1rem;
      transition: border-color 0.3s;
    }

    .form-control:focus {
      outline: none;
      border-color: #C89B3C;
    }

    textarea.form-control {
      min-height: 150px;
      resize: vertical;
    }

    .submit-btn {
      background-color: #C89B3C;
      color: #000;
      border: none;
      padding: 14px 30px;
      font-family: 'Prata', serif;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .submit-btn:hover {
      background-color: #e0b34a;
      transform: translateY(-2px);
    }

    .contact-info-title {
      font-family: 'Prata', serif;
      font-size: 1.5rem;
      margin-bottom: 25px;
      color: #C89B3C;
    }

    .contact-info-item {
      margin-bottom: 20px;
    }

    .contact-info-item h3 {
      font-family: 'Prata', serif;
      font-size: 1.1rem;
      margin-bottom: 8px;
      color: #C89B3C;
    }

    .contact-info-item p {
      line-height: 1.6;
    }

    .contact-info-item a {
      color: #fff;
      text-decoration: none;
      transition: color 0.3s;
    }

    .contact-info-item a:hover {
      color: #C89B3C;
    }

    .social-links {
      display: flex;
      gap: 15px;
      margin-top: 10px;
    }

    .social-links a {
      color: #C89B3C;
      font-size: 1.2rem;
      transition: all 0.3s;
    }

    .social-links a:hover {
      color: #fff;
      transform: translateY(-3px);
    }

    .map-container {
      width: 100%;
      height: 300px;
      margin-top: 40px;
      border: 1px solid #333;
    }

    @media (max-width: 768px) {
      .contact-content {
        flex-direction: column;
      }

      .contact-info {
        order: -1;
      }

      .contact-header h1 {
        font-size: 2.2rem;
      }
    }

    /* Gold highlight for form focus */
    .form-control:focus {
      box-shadow: 0 0 5px rgba(200, 155, 60, 0.5);
    }

    /* Shimmer effect for submit button */
    .shimmer {
      position: relative;
      overflow: hidden;
    }

    .shimmer::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 50%;
      height: 100%;
      background: linear-gradient(
        to right,
        rgba(255, 255, 255, 0) 0%,
        rgba(255, 255, 255, 0.3) 50%,
        rgba(255, 255, 255, 0) 100%
      );
      transform: skewX(-25deg);
      animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
      100% {
        left: 150%;
      }
    }
  </style>
</head>
<body>

<div class="loading-overlay">
  <div class="loader-container">
    <div class="loader-logo">HYPEZA</div>
    <div class="loader-bar">
      <div class="loader-progress"></div>
    </div>
    <div class="loader-text">Please wait...</div>
  </div>
</div>

<!-- Header -->
<div class="header">
  <div class="middle-section">
    <div class="product">

      <?php
      session_start();
      if (isset($_SESSION['user_id'])) {
        // User is logged in, link to men's collection
        echo '<a href="mens_collec/mens_collec.html" class="product-link">Products</a>';
      } else {
        // User is not logged in, link to login page
        echo '<a href="connexion2.html" class="product-link">Products</a>';
      }
      ?>

      <div class="dropdown-menu">
        <a href="<?php echo isset($_SESSION['user_id']) ? 'mens_collec/mens_collec.html' : 'connexion2.html'; ?>">Man's Selection</a>
        <a href="<?php echo isset($_SESSION['user_id']) ? 'womans_collections/Women\'s_Collections.html' : 'connexion2.html'; ?>">Woman's Selection</a>
      </div>
    </div>
      <div class="dropdown-menu">
        <a href="connexion2.html">Man's Selection</a>
        <a href="connexion2.html">Woman's Selection</a>
      </div>
    </div>

    <p class="contact">
      <a href="contact.php" class="contact-link">Contact</a>
    </p>
  </div>

  <div class="right-section">
    <a href="">
      <img src="svgs/favorite.svg" class="user" alt="favorite">
    </a>
    <p style="color: RGB(200, 155, 60);">|</p>

    <a href="">
      <img src="svgs/panier.svg" class="panier" alt="Panier">
    </a>

    <p style="color: RGB(200, 155, 60);">|</p>

    <a href="espace_client.php">
      <img src="svgs/profil_user.svg" class="user" alt="Profil">
    </a>
  </div>
</div>

<!-- Contact Content -->
<div class="contact-page">
  <div class="contact-header">
    <h1 class="shimmer-text">Contact Us</h1>
    <p>We value your feedback and inquiries. Reach out to our team for any questions about our collections, orders, or collaborations.</p>
  </div>

  <div class="contact-content">
    <div class="contact-form-container">
      <form id="contactForm" action="process_contact.php" method="POST">
        <div class="form-group">
          <label for="name" class="form-label">Name</label>
          <input type="text" id="name" name="name" class="form-control" required>
        </div>

        <div class="form-group">
          <label for="email" class="form-label">Email</label>
          <input type="email" id="email" name="email" class="form-control" required>
        </div>

        <div class="form-group">
          <label for="subject" class="form-label">Subject</label>
          <input type="text" id="subject" name="subject" class="form-control" required>
        </div>

        <div class="form-group">
          <label for="message" class="form-label">Message</label>
          <textarea id="message" name="message" class="form-control" required></textarea>
        </div>

        <button type="submit" class="submit-btn shimmer">Send Message</button>

      </form>

    </div>


    <div class="contact-info">
      <h2 class="contact-info-title">Our Information</h2>

      <div class="contact-info-item">
        <h3>Email</h3>
        <p><a href="mailto:team@hypza.tech">team@hypza.tech</a></p>
      </div>

      <div class="contact-info-item">
        <h3>Phone</h3>
        <p><a href="tel:+33123456789">+33 1 23 45 67 89</a></p>
      </div>

      <div class="contact-info-item">
        <h3>Address</h3>
        <p>123 Fashion Boulevard<br>75008 Paris, France</p>
      </div>

      <div class="contact-info-item">
        <h3>Hours</h3>
        <p>Monday - Friday: 9am - 6pm<br>Saturday: 10am - 4pm<br>Sunday: Closed</p>
      </div>

      <div class="contact-info-item">
        <h3>Follow Us</h3>
        <div class="social-links">
          <a href="https://www.instagram.com/p/DJ_u45stWvK/" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
          <a href="#" aria-label="Pinterest"><i class="fab fa-pinterest-p"></i></a>
        </div>
      </div>
    </div>
  </div>

  <div class="map-container">
    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2624.2977999946127!2d2.2969977153885103!3d48.872448179287945!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e66fc4a52a0c1f%3A0xa7630ba454944b0!2sChamps-%C3%89lys%C3%A9es%2C%20Paris%2C%20France!5e0!3m2!1sen!2sus!4v1621438792606!5m2!1sen!2sus" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
  </div>
</div>

<!-- Footer -->
<footer class="footer">
  <div class="footer-container">
    <div class="footer-top">
      <div class="footer-logo">
        <h3 class="shimmer1">HYPEZA</h3>
        <p class="tagline">Redéfinir le luxe avec style et qualité</p>
      </div>

      <div class="footer-navigation">
        <div class="footer-col">
          <h3>Collections</h3>
          <ul>
            <li><a href="#">Femme</a></li>
            <li><a href="#">Homme</a></li>
            <li><a href="#">Accessoires</a></li>
            <li><a href="#">Nouveautés</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h3>À propos</h3>
          <ul>
            <li><a href="#">Notre histoire</a></li>
            <li><a href="#">Engagements</a></li>
            <li><a href="#">Durabilité</a></li>
            <li><a href="#">Presse</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h3>Service client</h3>
          <ul>
            <li><a href="contact.html">Contactez-nous</a></li>
            <li><a href="#">Livraison & Retours</a></li>
            <li><a href="#">FAQ</a></li>
            <li><a href="#">Guide des tailles</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h3>Restez informé</h3>
          <p>Inscrivez-vous pour recevoir nos actualités</p>
          <form class="newsletter-form">
            <input type="email" placeholder="Votre email">
            <button type="submit">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
          </form>
        </div>
      </div>
    </div>

    <div class="footer-middle">
      <div class="social-links">
        <a href="https://www.instagram.com/p/DJ_u45stWvK/" aria-label="Instagram">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" fill="currentColor"/>
          </svg>
        </a>
        <a href="#" aria-label="Facebook">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385h-3.047v-3.47h3.047v-2.642c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953h-1.514c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385c5.737-.9 10.125-5.864 10.125-11.854z" fill="currentColor"/>
          </svg>
        </a>
        <a href="#" aria-label="Twitter">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" fill="currentColor"/>
          </svg>
        </a>
        <a href="#" aria-label="Pinterest">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z" fill="currentColor"/>
          </svg>
        </a>
      </div>
    </div>

    <div class="footer-bottom">
      <div class="footer-info">
        <p>&copy; 2024 HYPEZA. Tous droits réservés.</p>
      </div>
      <div class="footer-links">
        <a href="#">Mentions légales</a>
        <a href="#">Politique de confidentialité</a>
        <a href="#">Conditions générales</a>
        <a href="#">Plan du site</a>
      </div>
    </div>
  </div>
</footer>



<!-- JavaScript -->
<script>
  document.addEventListener("DOMContentLoaded", function() {
    // Loading animation
    const loadingOverlay = document.querySelector('.loading-overlay');
    const loaderProgress = document.querySelector('.loader-progress');

    let startTime = performance.now();
    let duration = 900;

    function animateProgress(currentTime) {
      let elapsed = currentTime - startTime;
      let progress = Math.min(elapsed / duration * 100, 100);

      loaderProgress.style.width = progress + '%';

      if (progress < 100) {
        requestAnimationFrame(animateProgress);
      } else {
        setTimeout(() => {
          loadingOverlay.style.opacity = '0';
          setTimeout(() => {
            loadingOverlay.style.display = 'none';
          }, 300);
        }, 200);
      }
    }

    requestAnimationFrame(animateProgress);

    // Product dropdown menu
    const productLink = document.querySelector('.product-link');
    const dropdownMenu = document.querySelector('.dropdown-menu');

    if (productLink && dropdownMenu) {
      productLink.addEventListener('mouseover', () => {
        dropdownMenu.style.display = 'block';
      });

      productLink.addEventListener('mouseout', (e) => {
        if (!dropdownMenu.contains(e.relatedTarget)) {
          dropdownMenu.style.display = 'none';
        }
      });

      dropdownMenu.addEventListener('mouseleave', () => {
        dropdownMenu.style.display = 'none';
      });

      productLink.addEventListener('click', (e) => {
        e.preventDefault();
        dropdownMenu.style.display =
          dropdownMenu.style.display === 'block' ? 'none' : 'block';
      });
    }

    // Cart functionality
    const cartOpenBtn = document.querySelector('.panier');
    const cartCloseBtn = document.querySelector('.cart-close');
    const cart = document.querySelector('.cart');

    if (cartOpenBtn && cartCloseBtn && cart) {
      cartOpenBtn.addEventListener('click', (e) => {
        e.preventDefault();
        cart.classList.add('open');
      });

      cartCloseBtn.addEventListener('click', () => {
        cart.classList.remove('open');
      });
    }

// Form submission handling
const contactForm = document.getElementById('contactForm');

if (contactForm) {
  contactForm.addEventListener('submit', function(e) {
    e.preventDefault();

    // Create success popup if it doesn't exist
    if (!document.getElementById('successPopup')) {
      const popupHTML = `
        <div class="success-popup" id="successPopup">
          <div class="success-popup-content">
            <div class="success-icon">
              <svg viewBox="0 0 24 24" width="32" height="32">
                <path fill="none" stroke="#C89B3C" stroke-width="2" d="M1,12 L8,19 L23,5"></path>
              </svg>
            </div>
            <h3>Message Sent</h3>
            <p>Thank you for contacting us. We'll respond shortly.</p>
            <button id="closePopupBtn">Close</button>
          </div>
        </div>
      `;
      document.body.insertAdjacentHTML('beforeend', popupHTML);

      // Add styles if not already in stylesheet
      if (!document.getElementById('popupStyles')) {
        const styles = `
          <style id="popupStyles">
            .success-popup {
              position: fixed;
              top: 0;
              left: 0;
              width: 100%;
              height: 100%;
              background-color: rgba(0, 0, 0, 0.5);
              backdrop-filter: blur(5px);
              z-index: 1000;
              display: flex;
              align-items: center;
              justify-content: center;
              opacity: 0;
              visibility: hidden;
              transition: opacity 0.3s, visibility 0.3s;
            }
            .success-popup-content {
              background-color: white;
              padding: 2rem;
              border-radius: 8px;
              box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
              text-align: center;
              max-width: 400px;
              width: 90%;
              animation: slideIn 0.4s forwards;
              border-top: 3px solid #C89B3C;
            }
            .success-icon {
              margin: 0 auto 1rem;
              background-color: rgba(200, 155, 60, 0.1);
              border-radius: 50%;
              width: 60px;
              height: 60px;
              display: flex;
              align-items: center;
              justify-content: center;
            }
            .success-popup h3 {
              font-family: 'Prata', serif;
              margin-bottom: 0.5rem;
              color: #333;
            }
            .success-popup p {
              margin-bottom: 1.5rem;
              color: #666;
            }
            .success-popup button {
              background-color: #C89B3C;
              color: white;
              border: none;
              padding: 0.7rem 1.5rem;
              border-radius: 4px;
              font-family: inherit;
              cursor: pointer;
              transition: background-color 0.3s;
            }
            .success-popup button:hover {
              background-color: #b48933;
            }
            @keyframes slideIn {
              from { opacity: 0; transform: translateY(20px); }
              to { opacity: 1; transform: translateY(0); }
            }
            .success-popup.show {
              opacity: 1;
              visibility: visible;
            }
          </style>
        `;
        document.head.insertAdjacentHTML('beforeend', styles);
      }
    }

    // Display the popup
    const successPopup = document.getElementById('successPopup');
    successPopup.classList.add('show');

    // Close popup when close button is clicked
    const closePopupBtn = document.getElementById('closePopupBtn');
    closePopupBtn.addEventListener('click', function() {
      successPopup.classList.remove('show');
    });

    // Close popup when clicking outside the popup content
    successPopup.addEventListener('click', function(e) {
      if (e.target === successPopup) {
        successPopup.classList.remove('show');
      }
    });

    // Reset form
    contactForm.reset();
  });
}


  });
</script>

</body>
</html>