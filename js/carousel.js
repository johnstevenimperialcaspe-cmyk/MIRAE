(function () {
    const performanceImages = [
        { src: "images/rt1.png", alt: "e-WASH Characteristics" },
        { src: "images/rt2.png", alt: "e-WASH Functions" },
        { src: "images/rt3.png", alt: "e-WASH Abilities" },
        { src: "images/rt4.png", alt: "e-WASH Core Values" }
    ];

    const tabs = document.querySelectorAll('.perf-tab-btn');
    const descContent = document.querySelector('.desc-content');
    const descImage = document.getElementById('desc-image');

    if (!tabs.length || !descContent || !descImage) {
        return;
    }

    function updateView(index) {
        // Update active class on buttons
        tabs.forEach((tab, i) => {
            if (i === index) {
                tab.classList.add('active');
            } else {
                tab.classList.remove('active');
            }
        });

        // Fade effect for image change
        descContent.classList.add('fade-out');

        setTimeout(() => {
            descImage.src = performanceImages[index].src;
            descImage.alt = performanceImages[index].alt;
            descContent.classList.remove('fade-out');
        }, 300);
    }

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            const index = parseInt(tab.getAttribute('data-index'));
            updateView(index);
        });
    });

    // Initialize with first tab
    updateView(0);
})();