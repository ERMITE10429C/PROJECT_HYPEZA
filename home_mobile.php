<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // Si l'utilisateur n'est pas connecté, on le redirige
    header("Location: connexion2.html");
    exit();
}
?>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>HYPEZA - Luxury Fashion</title>
    <link href="data:image/x-icon;base64," rel="icon" type="image/x-icon"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gold: {
                            light: '#F0D78C',
                            DEFAULT: '#D4AF37',
                            dark: '#BA8A00',
                        },
                        dark: {
                            DEFAULT: '#121212',
                            lighter: '#1E1E1E',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .material-icons-outlined {
            font-weight: normal;
            font-style: normal;
            font-size: 24px;
            display: inline-block;
            line-height: 1;
            text-transform: none;
            letter-spacing: normal;
            word-wrap: normal;
            white-space: nowrap;
            direction: ltr;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
            -moz-osx-font-smoothing: grayscale;
            font-feature-settings: 'liga';
        }

        .gold-gradient {
            background: linear-gradient(45deg, #BA8A00, #F0D78C, #D4AF37);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        /* Navigation drawer styles */
        .nav-drawer {
            position: fixed;
            top: 0;
            left: -300px;
            width: 300px;
            height: 100vh;
            background-color: #121212;
            z-index: 50;
            transition: left 0.3s ease;
            overflow-y: auto;
            border-right: 1px solid rgba(212,175,55,0.3);
        }

        .nav-drawer.open {
            left: 0;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 40;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease;
        }

        .overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #000;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease-out;
        }

        .loader-container {
            text-align: center;
        }

        .loader-logo {
            font-family: 'Prata', serif;
            font-size: 3rem;
            color: #c89b3c;
            margin-bottom: 2rem;
            letter-spacing: 5px;
        }
        .loader-logo2 {
            font-family: 'Prata', serif;
            font-size: 2rem;
            color: #c89b3c;
            margin-bottom: 0;
            letter-spacing: 2px;
        }

        .loader-bar {
            width: 200px;
            height: 3px;
            background-color: rgba(200, 155, 60, 0.3);
            margin: 0 auto;
            position: relative;
            overflow: hidden;
        }

        .loader-progress {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 0%;
            background-color: rgb(200, 155, 60);
            transition: width 0.1s linear;
        }

        .loader-text {
            font-family: 'Cormorant Garamond', serif;
            color: #c89b3c;
            margin-top: 1rem;
            font-size: 0.9rem;
            letter-spacing: 2px;
        }

        @keyframes shimmerLoader {
            0% {
                background-position: -100% 0;
            }
            100% {
                background-position: 100% 0;
            }
        }
        .loader-logo {
            background: linear-gradient(
                    90deg,
                    rgba(200, 155, 60, 0.8) 0%,
                    rgba(255, 215, 100, 1) 50%,
                    rgba(200, 155, 60, 0.8) 100%
            );
            background-size: 200% auto;
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            animation: shimmerLoader 2s linear infinite;
        }
        .loader-logo2 {
            background: linear-gradient(
                    90deg,
                    rgba(200, 155, 60, 0.8) 0%,
                    rgba(255, 215, 100, 1) 50%,
                    rgba(200, 155, 60, 0.8) 100%
            );
            background-size: 200% auto;
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            animation: shimmerLoader 2s linear infinite;
        }

    </style>
</head>

<body class="bg-dark">
<!-- Loading Overlay (outside main container) -->
<div class="loading-overlay">
    <div class="loader-container">
        <div class="loader-logo">HYPEZA</div>
        <div class="loader-bar">
            <div class="loader-progress"></div>
        </div>
        <div class="loader-text">Please wait...</div>
    </div>
</div>

<!-- Navigation Overlay -->
<div class="overlay" id="overlay"></div>

<!-- Navigation Drawer -->
<div class="nav-drawer" id="navDrawer">
    <div class="p-6 border-b border-gold/30">
        <div class="flex justify-between items-center">
            <h2 class="gold-gradient text-2xl font-bold tracking-widest">HYPEZA</h2>
            <button id="closeNavBtn" class="flex items-center justify-center text-gold">
                <span class="material-icons-outlined">close</span>
            </button>
        </div>
    </div>

    <div class="p-4">
        <nav class="space-y-6">
            <div class="space-y-3">
                <h3 class="text-gold text-sm uppercase tracking-wider">Collections</h3>
                <ul class="space-y-3 pl-2">
                    <li><a href="womans_collections/Women's_Collections.html" class="text-white hover:text-gold transition-colors block py-1">Femme</a></li>
                    <li><a href="mens_collec/mens_collec.html" class="text-white hover:text-gold transition-colors block py-1">Homme</a></li>
                </ul>
            </div>

            <div class="space-y-3">
                <h3 class="text-gold text-sm uppercase tracking-wider">Information</h3>
                <ul class="space-y-3 pl-2">
                    <li><a href="about.php" class="text-white hover:text-gold transition-colors block py-1">About HYPEZA</a></li>
                    <li><a href="contact.html" class="text-white hover:text-gold transition-colors block py-1">Contact Us</a></li>
                </ul>
            </div>

            <div class="pt-6 border-t border-gold/20">
                <div class="flex justify-around">
                    <a href="https://www.instagram.com/hypeza_official?igsh=MTY3eDcwM2FpbG12aA==" class="text-gold"><i class="fab fa-instagram text-xl"></i></a>
                    <a href="#" class="text-gold"><i class="fab fa-facebook text-xl"></i></a>
                    <a href="#" class="text-gold"><i class="fab fa-twitter text-xl"></i></a>
                    <a href="#" class="text-gold"><i class="fab fa-pinterest text-xl"></i></a>
                </div>
            </div>
        </nav>
    </div>
</div>

<!-- Main Container - single container with transparent background -->
<div class="relative flex size-full min-h-screen flex-col bg-transparent justify-between group/design-root overflow-x-hidden" style='font-family: "Montserrat", sans-serif;'>

    <header class="sticky top-0 z-10 bg-transparent backdrop-blur-sm border-b border-gold/30">

        <div class="flex items-center p-4 justify-between">

            <button id="openNavBtn" class="flex items-center justify-center rounded-full h-10 w-10 text-gold">
                <span class="material-icons-outlined">menu</span>
            </button>

            <h1 class="loader-logo2">HYPEZA</h1>

            <button class=""></button>

        </div>

    </header>
    <!-- Main Content -->

    <main class="pb-20">
        <!-- Replace your current hero section with this -->
        <div class="absolute top-0 left-0 w-full h-screen -z-10">
            <div class="h-full w-full bg-cover bg-center flex flex-col justify-end"
                 style='background-image: linear-gradient(0deg, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 70%)
         , url("https://images.unsplash.com/photo-1549062572-544a64fb0c56?q=80&w=1000");'>

                <div class="p-8 space-y-5">
                    <h2 class="text-gold text-4xl font-bold leading-tight" style="font-family: 'Playfair Display', serif;">Redefine Luxury</h2>
                    <p class="text-gray-200 text-base font-light leading-relaxed max-w-xs">Discover exquisite craftsmanship and timeless elegance with our collection of haute couture pieces.</p>
                    <button onclick="openNav()" class="bg-gold text-dark text-base font-semibold leading-normal tracking-wide py-3 px-8 rounded-none hover:bg-gold-light transition-colors duration-300 flex items-center gap-2">
                        Shop Collection <span class="material-icons-outlined text-sm">arrow_forward</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Add this empty div to create spacing for the rest of your content -->
        <div class="h-screen"></div>

        <section class="py-10">
            <div class="px-6 pb-6">
                <h3 class="text-gold text-2xl font-bold leading-tight" style="font-family: 'Playfair Display', serif;">Exclusive Designs</h3>
                <div class="w-20 h-0.5 bg-gold mt-2"></div>
            </div>
            <div class="flex overflow-x-auto [-ms-scrollbar-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden pl-6 pr-3">
                <div class="flex items-stretch gap-5">
                    <div class="flex flex-col gap-3 rounded-none min-w-[280px] w-[280px] group">
                        <div class="w-full bg-center bg-no-repeat aspect-[3/4] bg-cover overflow-hidden relative group-hover:shadow-[0_0_15px_rgba(212,175,55,0.3)] transition-shadow duration-300" style='background-image: url("mens_collec/images/signature_jacket.jpeg");'>
                            <div class="absolute inset-0 bg-gradient-to-t from-dark/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                                <button onclick="openNav()" class="bg-gold text-dark text-sm font-medium py-2 px-4">View Details</button>
                            </div>
                        </div>
                        <div>
                            <p class="text-white text-lg font-semibold leading-snug">Italian Leather Jacket</p>
                            <p class="text-gold font-light">€890</p>
                        </div>
                    </div>
                    <div class="flex flex-col gap-3 rounded-none min-w-[280px] w-[280px] group">
                        <div class="w-full bg-center bg-no-repeat aspect-[3/4] bg-cover overflow-hidden relative group-hover:shadow-[0_0_15px_rgba(212,175,55,0.3)] transition-shadow duration-300" style='background-image: url("mens_collec/images/designer_suit.webp");'>
                            <div class="absolute inset-0 bg-gradient-to-t from-dark/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                                <button onclick="openNav()" class="bg-gold text-dark text-sm font-medium py-2 px-4">View Details</button>
                            </div>
                        </div>
                        <div>
                            <p class="text-white text-lg font-semibold leading-snug">Signature Grey Suit</p>
                            <p class="text-gold font-light">€1,290</p>
                        </div>
                    </div>
                    <div class="flex flex-col gap-3 rounded-none min-w-[280px] w-[280px] group">
                        <div class="w-full bg-center bg-no-repeat aspect-[3/4] bg-cover overflow-hidden relative group-hover:shadow-[0_0_15px_rgba(212,175,55,0.3)] transition-shadow duration-300" style='background-image: url("womans_collections/dress_mobile.jpeg");'>
                            <div class="absolute inset-0 bg-gradient-to-t from-dark/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                                <button onclick="openNav()" class="bg-gold text-dark text-sm font-medium py-2 px-4">View Details</button>
                            </div>
                        </div>
                        <div>
                            <p class="text-white text-lg font-semibold leading-snug">Elegance Evening dress</p>
                            <p class="text-gold font-light">€1,450</p>
                        </div>
                    </div>
                </div>
                <div class="w-3"></div>
            </div>
        </section>

        <section class="py-10 bg-dark-lighter">
            <div class="px-6 pb-6">
                <h3 class="text-gold text-2xl font-bold leading-tight" style="font-family: 'Playfair Display', serif;">Shop by Category</h3>
                <div class="w-20 h-0.5 bg-gold mt-2"></div>
            </div>
            <div class="grid grid-cols-2 gap-5 px-6">
                <div class="flex flex-col gap-2 group">
                    <div class="w-full bg-center bg-no-repeat aspect-square bg-cover overflow-hidden relative"
                         style='background-image: url("womans_collections/images/Black Cocktail Dress.webp");'>
                        <div class="absolute inset-0 bg-dark/40 flex items-center justify-center">
                            <a href="connexion2.html"><p class="text-gold text-lg font-medium">Women's Dresses</p>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-2 group">
                    <div class="w-full bg-center bg-no-repeat aspect-square bg-cover overflow-hidden relative"

                         style='background-image: url("mens_collec/images/suit_hero.webp");'>

                        <div class="absolute inset-0 bg-dark/40 flex items-center justify-center">
                            <a href="connexion2.html"><p class="text-gold text-lg font-medium">Suits</p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-2 group">
                    <div class="w-full bg-center bg-no-repeat aspect-square bg-cover overflow-hidden relative"

                         style='background-image: url("womans_collections/images2/Sequin Evening Top.webp");'>

                        <div class="absolute inset-0 bg-dark/40 flex items-center justify-center">

                            <a href="connexion2.html"><p class="text-gold text-lg font-medium">Tops</p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-2 group">
                    <div class="w-full bg-center bg-no-repeat aspect-square bg-cover overflow-hidden relative"

                         style='background-image: url("mens_collec/images/suits/slim.webp");'>

                        <div class="absolute inset-0 bg-dark/40 flex items-center justify-center">
                            <a href="connexion2.html"><p class="text-gold text-lg font-medium">Your Custom suit</p></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-10 px-6">
            <div class="border border-gold/30 p-6 text-center">
                <h3 class="text-gold text-xl font-bold mb-3" style="font-family: 'Playfair Display', serif;">Our Heritage</h3>
                <p class="text-gray-300 text-sm leading-relaxed mb-4">Depuis 2010, HYPEZA incarne l'élégance et le raffinement dans chaque création. Notre maison de couture allie savoir-faire traditionnel et vision contemporaine.</p>
                <button onclick="openNav()" class="border border-gold text-gold text-sm font-medium py-2 px-6 hover:bg-gold hover:text-dark transition-colors duration-300">
                    Discover More
                </button>
            </div>
        </section>

        <section class="py-8 bg-dark-lighter px-6">
            <h3 class="text-gold text-xl font-medium mb-4 text-center" style="font-family: 'Playfair Display', serif;">Join the Elite</h3>
            <p class="text-gray-300 text-sm text-center mb-5">Receive exclusive previews of our new collections and private invitations.</p>
            <form class="flex border-b border-gold/50">
                <input type="email" placeholder="Your email address" class="bg-transparent text-white flex-1 py-3 focus:outline-none text-sm">
                <button type="submit" class="text-gold px-2">
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>
        </section>
    </main>


    <footer class="fixed bottom-0 left-0 w-full bg-dark border-t border-gold/30 shadow-lg z-40">
        <nav class="flex justify-around items-center h-16 px-2">
            <a class="flex flex-col items-center justify-center gap-0.5 text-gold flex-1 py-2 rounded-lg" href="#">
                <span class="material-icons-outlined text-[22px]">home</span>
                <span class="text-xs font-medium">Home</span>
            </a>
            <a class="flex flex-col items-center justify-center gap-0.5 text-gray-500 hover:text-gold flex-1 py-2 rounded-lg transition-colors" href="womans_collections/Women's_Collections.html">
                <span class="material-icons-outlined text-[22px]">favorite_border</span>
                <span class="text-xs font-medium">Wishlist</span>
            </a>
            <a class="flex flex-col items-center justify-center gap-0.5 text-gray-500 hover:text-gold flex-1 py-2 rounded-lg transition-colors" href="womans_collections/Women's_Collections.html">
                <span class="material-icons-outlined text-[22px]">shopping_bag</span>
                <span class="text-xs font-medium">Bag</span>
            </a>
            <a class="flex flex-col items-center justify-center gap-0.5 text-gray-500 hover:text-gold flex-1 py-2 rounded-lg transition-colors" href="espace_client.php">
                <span class="material-icons-outlined text-[22px]">person_outline</span>
                <span class="text-xs font-medium">Profile</span>
            </a>
        </nav>
        <div class="h-[env(safe-area-inset-bottom)] bg-dark"></div>
    </footer>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const loadingOverlay = document.querySelector('.loading-overlay');
        const loaderProgress = document.querySelector('.loader-progress');

        // Animate progress bar from 0 to 100% over 900ms
        let startTime = performance.now();
        let duration = 900; // slightly less than 1s to allow for fade out

        function animateProgress(currentTime) {
            let elapsed = currentTime - startTime;
            let progress = Math.min(elapsed / duration * 100, 100);

            loaderProgress.style.width = progress + '%';

            if (progress < 100) {
                requestAnimationFrame(animateProgress);
            } else {
                // Start fade out after progress reaches 100%
                setTimeout(() => {
                    loadingOverlay.style.opacity = '0';
                    setTimeout(() => {
                        loadingOverlay.style.display = 'none';
                    }, 100);
                }, 0);
            }
        }

        requestAnimationFrame(animateProgress);
    });
    // Add smooth scroll behavior for product sliders
    document.querySelectorAll('.overflow-x-auto').forEach(slider => {
        let isDown = false;
        let startX;
        let scrollLeft;

        slider.addEventListener('mousedown', (e) => {
            isDown = true;
            slider.classList.add('active');
            startX = e.pageX - slider.offsetLeft;
            scrollLeft = slider.scrollLeft;
        });

        slider.addEventListener('mouseleave', () => {
            isDown = false;
            slider.classList.remove('active');
        });

        slider.addEventListener('mouseup', () => {
            isDown = false;
            slider.classList.remove('active');
        });

        slider.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - slider.offsetLeft;
            const walk = (x - startX) * 2;
            slider.scrollLeft = scrollLeft - walk;
        });
    });
    // Add navigation drawer functionality
    const openNavBtn = document.getElementById('openNavBtn');
    const closeNavBtn = document.getElementById('closeNavBtn');
    const navDrawer = document.getElementById('navDrawer');
    const overlay = document.getElementById('overlay');

    function openNav() {
        navDrawer.classList.add('open');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent scrolling
    }

    function closeNav() {
        navDrawer.classList.remove('open');
        overlay.classList.remove('active');
        document.body.style.overflow = ''; // Re-enable scrolling
    }

    openNavBtn.addEventListener('click', openNav);
    closeNavBtn.addEventListener('click', closeNav);
    overlay.addEventListener('click', closeNav);

    // Close navigation when ESC key is pressed
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeNav();
        }
    });
</script>
</body>

</html>


