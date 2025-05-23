<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-1K37NRKQ9N"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-1K37NRKQ9N');
    </script>
    <!-- Google Tag Manager -->
    <script>
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-T5PXQ6SM');
    </script>
    <!-- End Google Tag Manager -->

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>HYPZA – Luxury Fashion & Streetwear | hypza.tech</title>

    <meta name="description" content="Discover HYPZA – a luxury fashion and streetwear brand. Explore exclusive clothing, shoes, and accessories at hypza.tech. Where identity meets style.">
    <meta name="keywords" content="hypza, hypeza, HYPZA clothing, streetwear, luxury fashion, designer apparel, fashion brand, luxury streetwear, exclusive clothing, premium apparel, modern fashion, unique style, quality craftsmanship, contemporary design, fashion accessories, HYPEZA, hypeza, hypza, HYPZA, haipza? haypza">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://hypeza.tech/">

    <!-- Schema.org JSON-LD for Brand SEO -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": "HYPZA",
            "alternateName": "HYPEZA",
            "url": "https://hypeza.tech",
            "logo": "https://hypeza.tech/favicon.ico",
            "description": "HYPZA – premium fashion and streetwear brand for exclusive modern apparel.",
            "sameAs": [
                "https://facebook.com/hypeza",
                "https://instagram.com/hypeza"
            ]
        }
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prata&family=Roboto&family=Satisfy&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prata&family=Cormorant+Garamond:wght@400;600&display=swap" rel="stylesheet">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="https://slater.app/10324/23333.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="./style.css">
    <link rel="stylesheet" href="split1.css">
    <link rel="stylesheet" href="horizontal_scroll.css">
    <link rel="stylesheet" href="paralax_1.css">
</head>

<body>


<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-T5PXQ6SM"
                  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->


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
            <a href="connexion2.html" class="product-link">Products</a>
            <div class="dropdown-menu">
                <a href="connexion2.html">Man's Selection</a>
                <a href="connexion2.html">Woman's Selection</a>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const productLink = document.querySelector('.product-link');
                const dropdownMenu = document.querySelector('.dropdown-menu');

                // Afficher le menu au survol
                productLink.addEventListener('mouseover', () => {
                    dropdownMenu.style.display = 'block';
                });

                // Cacher le menu lorsque la souris quitte le bouton ou le menu
                productLink.addEventListener('mouseout', (e) => {
                    if (!dropdownMenu.contains(e.relatedTarget)) {
                        dropdownMenu.style.display = 'none';
                    }
                });

                dropdownMenu.addEventListener('mouseleave', () => {
                    dropdownMenu.style.display = 'none';
                });

                // Afficher ou cacher le menu au clic
                productLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    dropdownMenu.style.display =
                        dropdownMenu.style.display === 'block' ? 'none' : 'block';
                });
                productLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    dropdownMenu.style.display = 'block';
                });
            });
        </script>

        <p class="contact">
            <a href="#contact" class="contact-link">Contact</a>
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

        <a href="connexion2.html">
            <img src="svgs/profil_user.svg" class="user" alt="Profil">
        </a>


    </div>

</div>


<div class="overlay2">
    <div class="navbar">
        <span class="navbar-text shimmer-text">HYPEZA</span>
    </div>
    <div class="hero">
        <span class="hero-text shimmer-text">HYPEZA</span>
    </div>
</div>

<div class="overlay">
    <div class="background">
        <img src="whisk_storyboardd94a966788374da88f1ee0b5.webp" alt="Featured Collection" class="background-categorie active">
        <img src="whisk_storyboard9376d4f287c64902ba787667.webp" alt="Men's Collection" class="background-categorie">


        <div class="carousel-controls">
            <button class="previous" onclick="changeBackground(-1)">&#8592; <u>PREV</u></button>
            <button class="next" onclick="changeBackground(1)"><u>NEXT</u> &#8594;</button>

            <div class="dot-navigator">
                <span class="dot" onclick="goToSlide(0)"></span>
                <span class="dot" onclick="goToSlide(1)"></span>
            </div>

            <div class="ring-timer">
                <svg class="ring-svg" viewBox="0 0 32 32" width="50" height="50">
                    <circle class="ring-background" cx="16" cy="16" r="14" stroke-width="2" fill="none"></circle>
                    <circle class="ring-progress" cx="16" cy="16" r="14" stroke-width="2" fill="none"></circle>
                </svg>
            </div>
        </div>
    </div>
</div>

<div class="spliting_animation">

    <div class="blank1"></div>

    <section>
        <div class="title1 titleBurrowing" data-splitting>
            AT
            <span class="span1">HYEPZA</span>
            <span class="span2">
                we redefine luxury—timeless style, unmatched
                <span style="color: #f6bf1e">quality</span>, and bold expression in
                <span style="color: #f6bf1e">every piece.</span>
            </span>
        </div>
    </section>

    <div class="blank1"></div>
    <div class="blank1"></div>
    <div class="blank1"></div>

</div>


<div class="header-categorie-top">
    <div class="header-categorie" style="height: 300px;">
        <p class="selection">Woman's Selection</p>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                gsap.registerPlugin(ScrollTrigger);

                gsap.fromTo(".header-categorie",
                    {scale: 1},
                    {
                        scale: 1.5,
                        scrollTrigger: {
                            trigger: ".header-categorie",
                            start: "top center",
                            end: "bottom center",
                            scrub: true
                        }
                    }
                );

                gsap.fromTo(".selection",
                    {scale: 0.8, opacity: 0},
                    {scale: 1, opacity: 1, duration: 0.8, ease: "back.out(1.7)"}
                );
            });
        </script>
    </div>
</div>



<div class="horizantal_css">

    <a href="womans_collections/Women's_Collections.html">

        <section class="comparisonSection" >
            <div class="comparisonImage beforeImage">
                <img src="Untitled-3.webp" alt="before" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <div class="comparisonImage afterImage">
                <img src="Untitled-2.webp" alt="after" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
        </section>

    </a>

</div>

<!-- partial -->
<script src='https://assets.codepen.io/16327/gsap-latest-beta.min.js?r=v3.11.3'></script>
<script src='https://assets.codepen.io/16327/ScrollTrigger.min.js'></script>

<script type="module" src="./script.js"></script>

<!-- partial -->


<div class="explore_selection">
    <p class="explore">Explore Our Selection</p>
</div>

<div class="overlay3">
    <div class="drag1">
        <section class="cloneable">
            <div class="overlay10">
                <div class="overlay-inner">
                    <div class="overlay-count-row">
                        <div class="count-column">
                            <h2 data-slide-count="step" class="count-heading">01</h2>
                        </div>
                        <div class="count-row-divider"></div>
                        <div class="count-column">
                            <h2 data-slide-count="total" class="count-heading">04</h2>
                        </div>
                    </div>
                    <div class="overlay-nav-row"><button aria-label="previous slide" data-slider="button-prev" class="button"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewbox="0 0 17 12" fill="none" class="button-arrow">
                                <path d="M6.28871 12L7.53907 10.9111L3.48697 6.77778H16.5V5.22222H3.48697L7.53907 1.08889L6.28871 0L0.5 6L6.28871 12Z" fill="currentColor"></path>
                            </svg>
                            <div class="button-overlay">
                                <div class="overlay-corner"></div>
                                <div class="overlay-corner top-right"></div>
                                <div class="overlay-corner bottom-left"></div>
                                <div class="overlay-corner bottom-right"></div>
                            </div>
                        </button><button aria-label="previous slide" data-slider="button-next" class="button"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewbox="0 0 17 12" fill="none" class="button-arrow next">
                                <path d="M6.28871 12L7.53907 10.9111L3.48697 6.77778H16.5V5.22222H3.48697L7.53907 1.08889L6.28871 0L0.5 6L6.28871 12Z" fill="currentColor"></path>
                            </svg>
                            <div class="button-overlay">
                                <div class="overlay-corner"></div>
                                <div class="overlay-corner top-right"></div>
                                <div class="overlay-corner bottom-left"></div>
                                <div class="overlay-corner bottom-right"></div>
                            </div>
                        </button></div>
                </div>
            </div>
            <div class="main">
                <div class="slider-wrap">
                    <div data-slider="list" class="slider-list">
                        <div data-slider="slide" class="slider-slide">
                            <div class="slide-inner">
                                <a href="womans_collections/dresses.html" class="slide-link">
                                    <img src="https://images.unsplash.com/photo-1595777457583-95e059d581b8?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1974&q=80" loading="lazy" sizes="(max-width: 479px) 100vw, 560px" alt="Women's Dresses Collection">
                                    <div class="slide-caption">
                                        <div class="caption-dot"></div>
                                        <p class="caption">Women's Dresses</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div data-slider="slide" class="slider-slide active">
                            <div class="slide-inner">
                                <a href="womans_collections/tops.html" class="slide-link">
                                    <img src="https://images.unsplash.com/photo-1728567471456-8bf765731741?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" loading="lazy" alt="Women's Tops Collection">
                                    <div class="slide-caption">
                                        <div class="caption-dot"></div>
                                        <p class="caption">Women's Tops</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div data-slider="slide" class="slider-slide">
                            <div class="slide-inner">
                                <a href="womans_collections/outerwear.html" class="slide-link">
                                    <img src="https://images.unsplash.com/photo-1539533018447-63fcce2678e3?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" loading="lazy" sizes="(max-width: 479px) 100vw, 560px" alt="Women's Outerwear Collection">
                                    <div class="slide-caption">
                                        <div class="caption-dot"></div>
                                        <p class="caption">Women's Outerwear</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div data-slider="slide" class="slider-slide">
                            <div class="slide-inner">
                                <a href="womans_collections/bottoms.html" class="slide-link">
                                    <img src="https://images.unsplash.com/photo-1509551388413-e18d0ac5d495?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1974&q=80" loading="lazy" sizes="(max-width: 479px) 100vw, 560px" alt="Women's Bottoms Collection">
                                    <div class="slide-caption">
                                        <div class="caption-dot"></div>
                                        <p class="caption">Women's Bottoms</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<div class="header-categorie-top">
    <div class="header-categorie" style="height: 300px;">
        <p class="selection">Men's Selection</p>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                gsap.registerPlugin(ScrollTrigger);

                gsap.fromTo(".header-categorie",
                    {scale: 1},
                    {
                        scale: 1.5,
                        scrollTrigger: {
                            trigger: ".header-categorie",
                            start: "top center",
                            end: "bottom center",
                            scrub: true
                        }
                    }
                );

                gsap.fromTo(".selection",
                    {scale: 0.8, opacity: 0},
                    {scale: 1, opacity: 1, duration: 0.8, ease: "back.out(1.7)"}
                );
            });
        </script>
    </div>
</div>

<div class="horizantal_css">

    <a href="mens_collec/mens_collec.html" >

        <section class="comparisonSection" >
            <div class="comparisonImage beforeImage">
                <img src="mens_collec_slide.webp" alt="before" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <div class="comparisonImage afterImage">
                <img src="mens_collec_slide2.webp" alt="after" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
        </section>

    </a>

</div>

<div class="explore_selection">
    <p class="explore">Explore Our Selection</p>
</div>

<div class="overlay3">
    <div class="drag1">
        <section class="cloneable">
            <div class="overlay10">
                <div class="overlay-inner">
                    <div class="overlay-count-row">
                        <div class="count-column">
                            <h2 data-slide-count="step" class="count-heading">01</h2>
                        </div>
                        <div class="count-row-divider"></div>
                        <div class="count-column">
                            <h2 data-slide-count="total" class="count-heading">04</h2>
                        </div>
                    </div>
                    <div class="overlay-nav-row"><button aria-label="previous slide" data-slider="button-prev" class="button"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewbox="0 0 17 12" fill="none" class="button-arrow">
                                <path d="M6.28871 12L7.53907 10.9111L3.48697 6.77778H16.5V5.22222H3.48697L7.53907 1.08889L6.28871 0L0.5 6L6.28871 12Z" fill="currentColor"></path>
                            </svg>
                            <div class="button-overlay">
                                <div class="overlay-corner"></div>
                                <div class="overlay-corner top-right"></div>
                                <div class="overlay-corner bottom-left"></div>
                                <div class="overlay-corner bottom-right"></div>
                            </div>
                        </button><button aria-label="previous slide" data-slider="button-next" class="button"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewbox="0 0 17 12" fill="none" class="button-arrow next">
                                <path d="M6.28871 12L7.53907 10.9111L3.48697 6.77778H16.5V5.22222H3.48697L7.53907 1.08889L6.28871 0L0.5 6L6.28871 12Z" fill="currentColor"></path>
                            </svg>
                            <div class="button-overlay">
                                <div class="overlay-corner"></div>
                                <div class="overlay-corner top-right"></div>
                                <div class="overlay-corner bottom-left"></div>
                                <div class="overlay-corner bottom-right"></div>
                            </div>
                        </button></div>
                </div>
            </div>

            <div class="main">

                <div class="slider-wrap">

                    <div data-slider="list" class="slider-list">
                        <div data-slider="slide" class="slider-slide">
                            <div class="slide-inner">
                                <a href="mens_collec/Suits.html" class="slide-link">
                                    <img src="https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1974&q=80" loading="lazy" sizes="(max-width: 479px) 100vw, 560px" alt="Men's Suits Collection">
                                    <div class="slide-caption">
                                        <div class="caption-dot"></div>
                                        <p class="caption">Men's Suits</p>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <div data-slider="slide" class="slider-slide active">
                            <div class="slide-inner">
                                <a href="mens_collec/Shirts.html" class="slide-link">
                                    <img src="https://images.unsplash.com/photo-1598961942613-ba897716405b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1974&q=80" loading="lazy" alt="Men's Shirts Collection">
                                    <div class="slide-caption">
                                        <div class="caption-dot"></div>
                                        <p class="caption">Men's Shirts</p>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <div data-slider="slide" class="slider-slide">
                            <div class="slide-inner">
                                <a href="mens_collec/outerwear.html" class="slide-link">
                                    <img src="https://images.unsplash.com/photo-1591047139829-d91aecb6caea?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1974&q=80" loading="lazy" sizes="(max-width: 479px) 100vw, 560px" alt="Men's Outerwear Collection">
                                    <div class="slide-caption">
                                        <div class="caption-dot"></div>
                                        <p class="caption">Men's Outerwear</p>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <div data-slider="slide" class="slider-slide">
                            <div class="slide-inner">
                                <a href="mens_collec/Trousers.html" class="slide-link">
                                    <img src="https://images.unsplash.com/photo-1605518216938-7c31b7b14ad0?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1974&q=80" loading="lazy" sizes="(max-width: 479px) 100vw, 560px" alt="Men's Trousers Collection">
                                    <div class="slide-caption">
                                        <div class="caption-dot"></div>
                                        <p class="caption">Men's Trousers</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </section>
    </div>
</div>

<div class="div_paralax">

    <div class="parallax">

        <section class="parallax__header">
            <div class="parallax__visuals">
                <div class="parallax__black-line-overflow"></div>
                <div data-parallax-layers class="parallax__layers">
                    <img src="filali_page_1.webp" loading="eager"  data-parallax-layer="1" alt="" class="parallax__layer-img">
                    <img src="paralax_2.png" loading="eager"  data-parallax-layer="2" alt="" class="parallax__layer-img">
                    <div data-parallax-layer="3" class="parallax__layer-title">
                        <h2 class="parallax__title">HYPEZA</h2>
                    </div>
                    <img src="3.webp" loading="eager"  data-parallax-layer="4" alt="" class="parallax__layer-img">
                </div>

                <div class="parallax__fade"></div>
            </div>

        </section>

        <section class="parallax__content">

        </section>

    </div>

</div>


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
                        <li><a href="#">Contactez-nous</a></li>
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
                <a href="#" aria-label="Instagram">
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



<!-- First load jQuery -->
<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>

<!-- Then load GSAP -->
<script src='https://cdnjs.cloudflare.com/ajax/libs/gsap/3.3.4/gsap.min.js'></script>

<!-- Then load GSAP plugins -->
<script src='https://s3-us-west-2.amazonaws.com/s.cdpn.io/16327/ScrollTrigger.min.js'></script>

<!-- Then load Splitting.js (which is missing from your current script tags) -->
<script src="https://unpkg.com/splitting/dist/splitting.min.js"></script>

<!-- Finally load your custom scripts -->
<script src="splitting1.js"></script>
<script src="main.js"></script>



<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
<script src="https://unpkg.com/lenis@1.1.14/dist/lenis.min.js"></script>


<!-- Move all scripts to the end of the body -->
<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/gsap/3.3.4/gsap.min.js'></script>
<script src='https://s3-us-west-2.amazonaws.com/s.cdpn.io/16327/ScrollTrigger.min.js'></script>
<script src="https://unpkg.com/splitting/dist/splitting.min.js"></script>
<script src="https://unpkg.com/lenis@1.1.14/dist/lenis.min.js"></script>
<script src="./script.js"></script>

<!-- partial -->
<script src='https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/Draggable.min.js'></script>
<script src='https://cdn.jsdelivr.net/gh/ilja-van-eck/osmo/assets/gsap/InertiaPlugin.min.js'></script>

<script src='https://assets.codepen.io/16327/gsap-latest-beta.min.js?r=v3.12.6'></script>
<script src='https://assets.codepen.io/16327/ScrollTrigger.min.js'></script>




<script  src="script.js"></script>

<script  src="slider1.js" ></script>


<script  src="./script_horizental_scroll.js"></script>

<script  src="./paralax_script.js"></script>

<script src="./cartManager.js"></script>



<script>
    function animerCompteur(id, valeurFinale, durée = 1000) {
        const el = document.getElementById(id);
        let valeurActuelle = 0;
        const incrément = Math.ceil(valeurFinale / (durée / 50));

        const interval = setInterval(() => {
            valeurActuelle += incrément;
            if (valeurActuelle >= valeurFinale) {
                valeurActuelle = valeurFinale;
                clearInterval(interval);
            }
            el.textContent = valeurActuelle;
        }, 50);
    }

    fetch('stats.php')
        .then(res => res.json())
        .then(data => {
            if (data.total_users !== undefined && data.total_products !== undefined) {
                animerCompteur("compteur-users", data.total_users);
                animerCompteur("compteur-produits", data.total_products);
            }
        });
</script>


</body>

</html>