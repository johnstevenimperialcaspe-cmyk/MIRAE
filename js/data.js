document.addEventListener("DOMContentLoaded", () => {
  const legData = [
    { src: ["images/FDA.jpg"], desc: "Food and Drug Administration Certification" },
    {
      src: ["images/corp.jpg"],
      desc: "Certificate of Incorporation - MIRAE Design Trading Inc.",
    },
    {
      src: ["images/distributor.jpg", "images/distributor2.jpg"],
      desc: "Certificate of Authorized Exclusive Distributor",
    },
    { src: ["images/registration.jpg"], desc: "Official Certificate of Registration" },
  ];

  window.setLegalities = function (index) {
    const glassFrame = document.querySelector("#legalities .main-image-box");
    const mainDesc = document.getElementById("leg-main-desc");
    const thumbs = document.querySelectorAll("#legalities .thumb");

    if (!glassFrame || !mainDesc || !thumbs.length || !legData[index]) {
      return;
    }

    glassFrame.innerHTML = "";

    legData[index].src.forEach((imgSrc) => {
      const img = document.createElement("img");
      img.src = imgSrc;
      img.alt = "Legality Document";
      if (legData[index].src.length > 1) {
        img.classList.add("dual-img");
      }
      glassFrame.appendChild(img);
    });

    mainDesc.textContent = legData[index].desc;

    thumbs.forEach((t) => t.classList.remove("active"));
    if (thumbs[index]) {
      thumbs[index].classList.add("active");
    }
  };

  const certDataTitles = [
    "Certificate of Authorized Exclusive Distributor",
    "Certificate of Appreciation (e-WASH)- Municipality of El Nido",
    "Certificate of Appreciation (MIRAE Design) - Municipality of El Nido",
  ];

  let currentCertIndex = 0;
  const certSlides = document.querySelectorAll(".slider-item");
  const certDots = document.querySelectorAll(".slider-dots .dot");
  const certDescBox = document.getElementById("cert-slider-desc");
  let certAutoPlay;
  let isDragging = false;
  let startPos = 0;
  let currentTranslate = 0;

  function updateCertSlider() {
    if (!certSlides.length) return;

    certSlides.forEach((slide, i) => {
      slide.classList.remove("active", "prev", "next");
      const offset = i - currentCertIndex;

      if (offset === 0) {
        slide.classList.add("active");
      } else if (offset === 1 || (currentCertIndex === certSlides.length - 1 && i === 0)) {
        slide.classList.add("next");
      } else if (offset === -1 || (currentCertIndex === 0 && i === certSlides.length - 1)) {
        slide.classList.add("prev");
      }
    });

    certDots.forEach((dot, i) => {
      dot.classList.toggle("active", i === currentCertIndex);
    });

    if (certDescBox) {
      certDescBox.textContent = certDataTitles[currentCertIndex];
    }
  }

  function nextCertSlide() {
    currentCertIndex = (currentCertIndex + 1) % certSlides.length;
    updateCertSlider();
  }

  function prevCertSlide() {
    currentCertIndex = (currentCertIndex - 1 + certSlides.length) % certSlides.length;
    updateCertSlider();
  }

  function startCertAutoPlay() {
    stopCertAutoPlay();
    certAutoPlay = setInterval(nextCertSlide, 3000);
  }

  function stopCertAutoPlay() {
    if (certAutoPlay) {
      clearInterval(certAutoPlay);
    }
  }

  updateCertSlider();
  startCertAutoPlay();

  certSlides.forEach((slide, i) => {
    slide.addEventListener("click", () => {
      if (i !== currentCertIndex) {
        currentCertIndex = i;
        updateCertSlider();
        startCertAutoPlay();
      }
    });
  });

  certDots.forEach((dot, i) => {
    dot.addEventListener("click", () => {
      currentCertIndex = i;
      updateCertSlider();
      startCertAutoPlay();
    });
  });

  const sliderContainer = document.getElementById("cert-slider-container");
  if (sliderContainer) {
    sliderContainer.addEventListener("mousedown", touchStart);
    sliderContainer.addEventListener("touchstart", touchStart, { passive: true });
    sliderContainer.addEventListener("mouseup", touchEnd);
    sliderContainer.addEventListener("touchend", touchEnd, { passive: true });
    sliderContainer.addEventListener("mouseleave", () => {
      if (isDragging) touchEnd();
    });
    sliderContainer.addEventListener("mousemove", touchMove);
    sliderContainer.addEventListener("touchmove", touchMove, { passive: true });
  }

  function getPositionX(event) {
    if (event.type.includes("mouse")) {
      return event.pageX;
    }
    const touch = event.touches?.[0] ?? event.changedTouches?.[0];
    return touch ? touch.clientX : 0;
  }

  function touchStart(event) {
    startPos = getPositionX(event);
    isDragging = true;
    stopCertAutoPlay();
  }

  function touchMove(event) {
    if (!isDragging) return;
    currentTranslate = getPositionX(event) - startPos;
  }

  function touchEnd() {
    if (!isDragging) return;
    isDragging = false;
    const movedBy = currentTranslate;

    if (movedBy < -50) {
      nextCertSlide();
    } else if (movedBy > 50) {
      prevCertSlide();
    }

    currentTranslate = 0;
    startCertAutoPlay();
  }

  window.toggleTestImage = function (index) {
    const gallery = document.getElementById("test-gallery");
    const thumbs = document.querySelectorAll(".test-thumb");
    const clickedThumb = document.getElementById(`thumb-${index}`);

    if (!gallery || !clickedThumb) {
      return;
    }

    if (gallery.classList.contains("expanded") && clickedThumb.classList.contains("active")) {
      gallery.classList.remove("expanded");
      clickedThumb.classList.remove("active");
    } else {
      thumbs.forEach((t) => t.classList.remove("active"));
      gallery.classList.add("expanded");
      clickedThumb.classList.add("active");
    }
  };

  // Super Sol/e-WASH toggle controls (the two pill buttons).
  const testSwitchRoot = document.querySelector(".test-switch");
  const testTitle = document.getElementById("test-section-title");
  if (testSwitchRoot) {
    const testButtons = Array.from(testSwitchRoot.querySelectorAll(".test-switch-btn[data-test]"));
    const testPanels = Array.from(document.querySelectorAll(".test-panel[data-test-panel]"));
    const ewashOnlySections = Array.from(document.querySelectorAll(".ewash-only-section"));

    // Switches visible panel, title text, and section visibility for active test type.
    function setActiveTest(name, syncHash = true) {
      testButtons.forEach((b) => {
        const isActive = b.getAttribute("data-test") === name;
        b.classList.toggle("is-active", isActive);
        b.classList.toggle("active", isActive);
        b.setAttribute("aria-selected", isActive ? "true" : "false");
      });

      testPanels.forEach((p) => {
        const isActive = p.getAttribute("data-test-panel") === name;
        p.hidden = !isActive;
        p.classList.toggle("is-active", isActive);
      });

      const showEwashSections = name === "ewash";
      ewashOnlySections.forEach((section) => {
        section.hidden = !showEwashSections;
      });
      document.body.classList.toggle("showing-supersol-test", name === "supersol");

      if (testTitle) {
        testTitle.innerHTML =
          name === "supersol"
            ? '<span class="ewash-word">Super Sol</span> Test'
            : '<span class="ewash-word">e-WASH</span> Test';
      }

      // Keep URL hash in sync so users can deep-link to #supersol / #ewash.
      if (syncHash) {
        const hash = name === "supersol" ? "#supersol" : "#ewash";
        if (window.location.hash !== hash) {
          window.history.replaceState(null, "", hash);
        }
      }
    }

    testSwitchRoot.addEventListener("click", (e) => {
      const btn = e.target && e.target.closest ? e.target.closest(".test-switch-btn[data-test]") : null;
      if (!btn) return;
      setActiveTest(btn.getAttribute("data-test"));
    });

    function getInitialTestFromHash() {
      const hash = (window.location.hash || "").toLowerCase();
      if (hash === "#supersol" || hash === "#super-sol" || hash === "#super-sol-test") {
        return "supersol";
      }
      return "ewash";
    }

    window.addEventListener("hashchange", () => {
      setActiveTest(getInitialTestFromHash(), false);
    });

    setActiveTest(getInitialTestFromHash(), false);
  }

  // Everything below handles interactions inside the Super Sol panel.
  const superSolPanel = document.querySelector('.test-panel[data-test-panel="supersol"]');
  if (superSolPanel) {
    const resultCards = Array.from(superSolPanel.querySelectorAll(".super-sol-result-card"));
    const interpretationTitle = document.getElementById("super-sol-interpretation-title");
    const interpretationText = document.getElementById("super-sol-interpretation-text");

    function setInterpretation(card) {
      if (!card || !interpretationTitle || !interpretationText) return;
      const titleEl = card.querySelector(".super-sol-result-title");
      const interpretationEl = card.querySelector(".super-sol-result-interpretation");
      const title = titleEl ? titleEl.textContent.trim() : "Interpretation";
      const interpretation = interpretationEl
        ? interpretationEl.textContent.trim()
        : "No interpretation provided for this result yet.";

      interpretationTitle.textContent = `${title} Interpretation`;
      interpretationText.textContent = interpretation;

      resultCards.forEach((c) => {
        const selected = c === card;
        c.classList.toggle("is-selected", selected);
        c.setAttribute("aria-pressed", selected ? "true" : "false");
      });
    }

    resultCards.forEach((card) => {
      card.addEventListener("click", () => setInterpretation(card));
      card.addEventListener("keydown", (event) => {
        if (event.key === "Enter" || event.key === " ") {
          event.preventDefault();
          setInterpretation(card);
        }
      });
    });

    // ===== SUPER SOL RESULT CARDS + DRAWER LOGIC (START) =====
    // Result cards with expandable inline drawers (Waste Water A, A1, etc.).
    const drawerCards = Array.from(superSolPanel.querySelectorAll(".super-sol-result-card.has-inline-drawer"));
    drawerCards.forEach((drawerCard) => {
      const drawerToggle = drawerCard.querySelector(".wwa-drawer-toggle");
      const drawerPanel = drawerCard.querySelector(".wwa-drawer-panel");
      const drawerInterpretation = drawerCard.querySelector(".super-sol-result-interpretation");
      const drawerText = drawerPanel ? drawerPanel.querySelector(".wwa-drawer-text") : null;
      if (!drawerToggle || !drawerPanel || !drawerText) return;

      if (drawerInterpretation) {
        drawerText.textContent = drawerInterpretation.textContent.trim();
      }

      // Smooth drawer slide animation from card edge.
      function animateDrawer(opening) {
        const isLeftDrawer = drawerCard.classList.contains("has-inline-drawer-left");
        const hiddenOffset = isLeftDrawer ? "translateX(-26px)" : "translateX(26px)";
        const keyframes = opening
          ? [
              { opacity: 0, transform: hiddenOffset },
              { opacity: 1, transform: "translateX(0)" },
            ]
          : [
              { opacity: 1, transform: "translateX(0)" },
              { opacity: 0, transform: hiddenOffset },
            ];
        drawerPanel.animate(keyframes, {
          duration: 260,
          easing: "cubic-bezier(0.22, 1, 0.36, 1)",
          fill: "forwards",
        });
      }

      // Opens/closes drawer and updates all accessibility attributes.
      function setDrawerState(opening) {
        drawerCard.classList.toggle("drawer-open", opening);
        drawerPanel.classList.toggle("drawer-open", opening);
        drawerToggle.setAttribute("aria-expanded", opening ? "true" : "false");
        drawerPanel.setAttribute("aria-hidden", opening ? "false" : "true");
        const isLeftDrawer = drawerCard.classList.contains("has-inline-drawer-left");
        drawerToggle.innerHTML = opening
          ? (isLeftDrawer ? "&rsaquo;" : "&lsaquo;")
          : (isLeftDrawer ? "&lsaquo;" : "&#8250;");
        animateDrawer(opening);
      }

      drawerToggle.addEventListener("click", (event) => {
        event.stopPropagation();
        const opening = !drawerPanel.classList.contains("drawer-open");
        setDrawerState(opening);
      });

      drawerCard.addEventListener(
        "click",
        (event) => {
          const toggleClicked = event.target && event.target.closest
            ? event.target.closest(".wwa-drawer-toggle")
            : null;
          if (toggleClicked) return;
          if (drawerPanel.classList.contains("drawer-open")) {
            event.preventDefault();
            event.stopImmediatePropagation();
            setDrawerState(false);
          }
        },
        true
      );
    });
    // ===== SUPER SOL RESULT CARDS + DRAWER LOGIC (END) =====

  }

  const trialImages = [
    "images/1.png",
    "images/2.png",
    "images/3.png",
    "images/4.png",
    "images/5.png",
    "images/6.png",
    "images/7.png",
    "images/8.png",
  ];

  let currentTrialIndex = 0;
  const trialImgElement = document.getElementById("trial-slider-img");

  function updateTrialSlider() {
    if (!trialImgElement || !trialImages.length) return;

    trialImgElement.style.opacity = "0";

    window.setTimeout(() => {
      currentTrialIndex = (currentTrialIndex + 1) % trialImages.length;
      trialImgElement.src = trialImages[currentTrialIndex];
      trialImgElement.style.opacity = "1";
    }, 800);
  }

  if (trialImgElement) {
    setInterval(updateTrialSlider, 3000);
  }
});
