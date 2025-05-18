// Project data with minimalist titles
const projects = [
    {
        id: 1,
        title: "Classic Oxford",
        year: "Spring/Summer",
        image:
            "./images/suit_1.webp"
    },
    {
        id: 2,
        title: "Italian Slim Fit",
        year: "All Seasons",
        image:
            "./images/suit_2.webp"
    },
    {
        id: 3,
        title: "British Wool",
        year: "Fall/Winter",
        image:
            "./images/British Wool.webp"
    },
    {
        id: 4,
        title: "Midnight Tuxedo",
        year: "Formal Collection",
        image:
            "./images/Midnight Tuxedo.webp"
    },

];

document.addEventListener("DOMContentLoaded", function () {
    const projectsContainer = document.querySelector(".projects-container");
    const backgroundImage = document.getElementById("background-image");

    // Render projects
    renderProjects(projectsContainer);

    // Initialize animations
    initialAnimation();

    // Preload images
    preloadImages();

    // Add hover events to project items
    setupHoverEvents(backgroundImage, projectsContainer);
});

// Render project items
function renderProjects(container) {
    projects.forEach((project) => {
        const projectItem = document.createElement("div");
        projectItem.classList.add("project-item");
        projectItem.dataset.id = project.id;
        projectItem.dataset.image = project.image;

        projectItem.innerHTML = `
      <div class="project-title">${project.title}</div>
      <div class="project-year">${project.year}</div>
    `;

        container.appendChild(projectItem);
    });
}

// Initial animation for project items
function initialAnimation() {
    const projectItems = document.querySelectorAll(".project-item");

    // Set initial state
    projectItems.forEach((item, index) => {
        item.style.opacity = "0";
        item.style.transform = "translateY(20px)";

        // Animate in with staggered delay
        setTimeout(() => {
            item.style.transition = "opacity 0.8s ease, transform 0.8s ease";
            item.style.opacity = "1";
            item.style.transform = "translateY(0)";
        }, index * 60);
    });
}

// Setup hover events for project items
function setupHoverEvents(backgroundImage, projectsContainer) {
    const projectItems = document.querySelectorAll(".project-item");
    let currentImage = null;
    let zoomTimeout = null;

    // Preload all images to ensure immediate display
    const preloadedImages = {};
    projects.forEach((project) => {
        const img = new Image();
        img.crossOrigin = "anonymous";
        img.src = project.image;
        preloadedImages[project.id] = img;
    });

    projectItems.forEach((item) => {
        item.addEventListener("mouseenter", function () {
            const imageUrl = this.dataset.image;

            // Clear any pending zoom timeout
            if (zoomTimeout) {
                clearTimeout(zoomTimeout);
            }

            // Reset transform and transition
            backgroundImage.style.transition = "none";
            backgroundImage.style.transform = "scale(1.2)";

            // Immediately show the new image
            backgroundImage.src = imageUrl;
            backgroundImage.style.opacity = "1";

            // Force browser to acknowledge the scale reset before animating
            // This ensures the zoom effect happens every time
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    // Re-enable transition and animate to scale 1.0
                    backgroundImage.style.transition =
                        "transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94)";
                    backgroundImage.style.transform = "scale(1.0)";
                });
            });

            // Update current image
            currentImage = imageUrl;
        });
    });

    // Handle mouse leaving the projects container
    projectsContainer.addEventListener("mouseleave", function () {
        // Hide the image
        backgroundImage.style.opacity = "0";
        currentImage = null;
    });
}

// Preload images
function preloadImages() {
    projects.forEach((project) => {
        const img = new Image();
        img.crossOrigin = "anonymous";
        img.src = project.image;
    });
}