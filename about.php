<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
    <title>À Propos - HYPEZA</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <style>
        body {
            background-color: #0a0a0a;
            color: #ffffff;
        }
        .hover-scale {
            transition: transform 0.3s ease;
        }
        .hover-scale:hover {
            transform: scale(1.03);
        }
        .gradient-border {
            position: relative;
            background: linear-gradient(45deg, #0a0a0a, #1a1a1a);
            border: 1px solid transparent;
        }
        .gradient-border::before {
            content: '';
            position: absolute;
            top: -2px; right: -2px; bottom: -2px; left: -2px;
            background: linear-gradient(45deg, #C6A55C, #E5BF7D);
            z-index: -1;
            border-radius: inherit;
            opacity: 0.3;
        }
    </style>
</head>

<body class="bg-[#0a0a0a]">
<main class="pb-20 px-6 max-w-7xl mx-auto">
    <!-- Section Hero -->
    <section class="py-16 text-center" data-aos="fade-up">
        <h1 class="text-gold text-5xl font-bold mb-8" style="font-family: 'Playfair Display', serif;">Notre Histoire</h1>
        <div class="w-32 h-0.5 bg-gradient-to-r from-[#C6A55C] to-[#E5BF7D] mx-auto mb-12"></div>
        <p class="text-gray-300 leading-relaxed mb-8 max-w-2xl mx-auto text-lg">
            Fondée en 2010 à Paris, HYPEZA incarne l'excellence de la haute couture française. Notre maison se distingue par son approche innovante tout en respectant les traditions séculaires de la mode.
        </p>
    </section>

    <!-- Section Valeurs -->
    <section class="py-16 grid md:grid-cols-3 gap-8">
        <div class="gradient-border p-8 rounded-lg hover-scale" data-aos="fade-up" data-aos-delay="100">
            <h3 class="text-[#C6A55C] mb-4 text-xl font-semibold">Excellence</h3>
            <p class="text-gray-300">Chaque création HYPEZA est le fruit d'un savoir-faire exceptionnel et d'une attention méticuleuse aux détails.</p>
        </div>

        <div class="gradient-border p-8 rounded-lg hover-scale" data-aos="fade-up" data-aos-delay="200">
            <h3 class="text-[#C6A55C] mb-4 text-xl font-semibold">Innovation</h3>
            <p class="text-gray-300">Nous repoussons constamment les limites de la création tout en respectant les codes de la haute couture.</p>
        </div>

        <div class="gradient-border p-8 rounded-lg hover-scale" data-aos="fade-up" data-aos-delay="300">
            <h3 class="text-[#C6A55C] mb-4 text-xl font-semibold">Durabilité</h3>
            <p class="text-gray-300">Notre engagement envers l'environnement se reflète dans le choix de nos matériaux et nos processus de production.</p>
        </div>
    </section>

    <!-- Section Artisanat -->
    <section class="py-16" data-aos="fade-up">
        <div class="relative h-96 mb-12 rounded-xl overflow-hidden hover-scale">
            <img src="Atelier_Hypeza.png" alt="Notre atelier" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0a] to-transparent"></div>
        </div>
        <h2 class="text-[#C6A55C] text-3xl font-bold mb-6" style="font-family: 'Playfair Display', serif;">L'Art de la Haute Couture</h2>
        <p class="text-gray-300 leading-relaxed mb-8 text-lg">
            Dans nos ateliers parisiens, nos artisans perpétuent les gestes ancestraux de la haute couture. Chaque pièce est confectionnée à la main, témoignant d'un savoir-faire unique.
        </p>
    </section>

    <!-- Section Contact -->
    <section class="py-16 gradient-border rounded-xl p-10" data-aos="fade-up">
        <h2 class="text-[#C6A55C] text-3xl font-bold mb-8" style="font-family: 'Playfair Display', serif;">Contactez-nous</h2>
        <div class="grid md:grid-cols-3 gap-8 text-gray-300">
            <div class="space-y-2">
                <p class="text-[#C6A55C] font-semibold">Adresse</p>
                <p>8 Place, 20200 CASABLANCA</p>
            </div>
            <div class="space-y-2">
                <p class="text-[#C6A55C] font-semibold">Téléphone</p>
                <p>+212 694 02 46 91</p>
            </div>
            <div class="space-y-2">
                <p class="text-[#C6A55C] font-semibold">Email</p>
                <p>team@hypeza.tech</p>
            </div>
        </div>
        <div class="mt-12 flex justify-center space-x-8">
            <a href="#" class="text-[#C6A55C] hover:text-[#E5BF7D] transition-colors"><i class="fab fa-instagram text-2xl"></i></a>
            <a href="#" class="text-[#C6A55C] hover:text-[#E5BF7D] transition-colors"><i class="fab fa-facebook text-2xl"></i></a>
            <a href="#" class="text-[#C6A55C] hover:text-[#E5BF7D] transition-colors"><i class="fab fa-twitter text-2xl"></i></a>
            <a href="#" class="text-[#C6A55C] hover:text-[#E5BF7D] transition-colors"><i class="fab fa-pinterest text-2xl"></i></a>
        </div>
    </section>
</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
    AOS.init({
        duration: 1000,
        once: true,
        offset: 100
    });
</script>
</body>
</html>