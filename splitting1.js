document.addEventListener("DOMContentLoaded", () => {
    // Register GSAP Plugins
    gsap.registerPlugin(ScrollTrigger);

    // Initialize Splitting
    Splitting();

    // Set a small timeout to ensure Splitting has fully processed the DOM
    setTimeout(() => {
        // Great Horned Owl animation
        if (document.querySelector(".titleGreathorned")) {
            const chars = document.querySelectorAll('.titleGreathorned .char');

            const tlSplitGreat = gsap.timeline();
            tlSplitGreat.from(chars, {
                duration: 0.8,
                opacity: 0,
                y: 10,
                ease: "circ.out",
                stagger: 0.02,
            }, "+=0");
        }

        // Burrowing Owl animation
        function setupSplits() {
            const chars = document.querySelectorAll('.titleBurrowing .char');

            if (chars.length) {
                const tlSplitBurrowing = gsap.timeline();

                tlSplitBurrowing.from(chars, {
                    duration: 0.8,
                    opacity: 0,
                    y: 10,
                    ease: "circ.out",
                    stagger: 0.02,
                    scrollTrigger: {
                        trigger: ".titleBurrowing",
                        start: "top 75%",
                        end: "bottom center",
                        scrub: 1
                    }
                }, "+=0");
            }
        }

        ScrollTrigger.addEventListener("refresh", setupSplits);
        setupSplits();

        // Parallax Layers
        document.querySelectorAll('[data-parallax-layers]').forEach((triggerElement) => {
            // Your existing parallax code
        });
    }, 100);
});

/* Lenis */
if (window.Lenis) {
    const lenis = new Lenis();
    lenis.on('scroll', ScrollTrigger.update);
    gsap.ticker.add((time) => {lenis.raf(time * 1000);});
    gsap.ticker.lagSmoothing(0);
}