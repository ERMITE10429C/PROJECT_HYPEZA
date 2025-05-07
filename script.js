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