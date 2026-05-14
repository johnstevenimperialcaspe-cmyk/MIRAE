 (function () {
  var yearEl = document.getElementById("year");
  if (yearEl) yearEl.textContent = new Date().getFullYear();
  
  function hidePreloader() {
    var preloader = document.querySelector(".preloader");
    if (preloader && !preloader.classList.contains("fade-out")) {
      preloader.classList.add("fade-out");
      setTimeout(function() { preloader.style.display = "none"; }, 600);
    }
  }
  window.addEventListener("load", hidePreloader);
  document.addEventListener("DOMContentLoaded", function() {
    setTimeout(hidePreloader, 2500); // 2.5s fallback
  });
 })();

(function () {
  var root = document.querySelector("main#home .hero-content[data-hero-sync]");
  if (!root) return;

  var brandLayers = Array.prototype.slice.call(
    root.querySelectorAll("[data-hero-brand-layer]")
  );
  var visualLayers = Array.prototype.slice.call(
    root.querySelectorAll("[data-hero-visual-layer]")
  );
  if (brandLayers.length !== 2 || visualLayers.length !== 2) return;

  var reduceMotion =
    window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var intervalMs = 4000;
  var busy = false;
  var i = 0;

  function clearAnimClasses(layer) {
    layer.classList.remove(
      "exit-left",
      "exit-right",
      "prep-from-right",
      "prep-from-left"
    );
  }

  function settleInactive(layer) {
    layer.style.setProperty("transition", "none");
    clearAnimClasses(layer);
    layer.classList.remove("is-on");
    layer.classList.add("is-off");
    void layer.offsetWidth;
    layer.style.removeProperty("transition");
  }

  function goTo(next) {
    if (busy || next === i) return;
    busy = true;

    var prev = 1 - next;
    var bLeave = brandLayers[prev];
    var bEnter = brandLayers[next];
    var vLeave = visualLayers[prev];
    var vEnter = visualLayers[next];

    if (reduceMotion) {
      settleInactive(bLeave);
      settleInactive(vLeave);
      clearAnimClasses(bEnter);
      clearAnimClasses(vEnter);
      bEnter.classList.remove("is-off");
      bEnter.classList.add("is-on");
      vEnter.classList.remove("is-off");
      vEnter.classList.add("is-on");
      i = next;
      busy = false;
      return;
    }

    bLeave.classList.remove("is-on");
    bLeave.classList.add("exit-left");

    vLeave.classList.remove("is-on");
    vLeave.classList.add("exit-right");

    bEnter.classList.remove("is-off");
    vEnter.classList.remove("is-off");

    if (next === 1) {
      bEnter.classList.add("prep-from-right");
      vEnter.classList.add("prep-from-left");
    } else {
      bEnter.classList.add("prep-from-left");
      vEnter.classList.add("prep-from-right");
    }

    void bEnter.offsetWidth;
    void vEnter.offsetWidth;

    bEnter.classList.remove("prep-from-right", "prep-from-left");
    bEnter.classList.add("is-on");

    vEnter.classList.remove("prep-from-left", "prep-from-right");
    vEnter.classList.add("is-on");

    window.setTimeout(function () {
      settleInactive(bLeave);
      settleInactive(vLeave);
      i = next;
      busy = false;
    }, 2050);
  }

  function tick() {
    if (document.hidden) return;
    goTo((i + 1) % 2);
  }

  setInterval(tick, intervalMs);
})();

 (function () {
  var diagram = document.querySelector(".features-diagram");
  if (!diagram) return;
  var svg = diagram.querySelector(".features-diagram-lines");
  var center = diagram.querySelector(".center-circle");
  if (!svg || !center) return;

  var map = {
    safety: diagram.querySelector(".feature-circle-safety"),
    detergency: diagram.querySelector(".feature-circle-detergency"),
    sterilization: diagram.querySelector(".feature-circle-sterilization"),
    rust: diagram.querySelector(".feature-circle-rust"),
  };

  function getCenter(el, rootRect) {
    var r = el.getBoundingClientRect();
    return {
      x: r.left - rootRect.left + r.width / 2,
      y: r.top - rootRect.top + r.height / 2,
      r: Math.min(r.width, r.height) / 2,
    };
  }

  function rayRectBorderExit(Fx, Fy, ux, uy, left, top, right, bottom) {
    var tMin = Infinity;
    function consider(t, xe, ye) {
      if (t <= 1e-9 || t >= tMin) return;
      if (xe < left - 0.5 || xe > right + 0.5) return;
      if (ye < top - 0.5 || ye > bottom + 0.5) return;
      tMin = t;
    }
    if (Math.abs(ux) > 1e-9) {
      consider((left - Fx) / ux, left, Fy + ((left - Fx) / ux) * uy);
      consider((right - Fx) / ux, right, Fy + ((right - Fx) / ux) * uy);
    }
    if (Math.abs(uy) > 1e-9) {
      consider((top - Fy) / uy, Fx + ((top - Fy) / uy) * ux, top);
      consider((bottom - Fy) / uy, Fx + ((bottom - Fy) / uy) * ux, bottom);
    }
    if (tMin === Infinity) {
      return { x: Fx, y: Fy };
    }
    return { x: Fx + tMin * ux, y: Fy + tMin * uy };
  }

  function setLine(lineEl, featureEl, c, rootRect) {
    var fr = featureEl.getBoundingClientRect();
    var Fx = fr.left - rootRect.left + fr.width / 2;
    var Fy = fr.top - rootRect.top + fr.height / 2;
    var dx = c.x - Fx;
    var dy = c.y - Fy;
    var len = Math.hypot(dx, dy) || 1;
    var ux = dx / len;
    var uy = dy / len;
    var left = fr.left - rootRect.left;
    var top = fr.top - rootRect.top;
    var right = fr.right - rootRect.left;
    var bottom = fr.bottom - rootRect.top;
    var hit = rayRectBorderExit(Fx, Fy, ux, uy, left, top, right, bottom);
    var outsidePad = 1;
    var x1 = hit.x + ux * outsidePad;
    var y1 = hit.y + uy * outsidePad;
    var x2 = c.x - ux * c.r;
    var y2 = c.y - uy * c.r;
    lineEl.setAttribute("x1", x1.toFixed(2));
    lineEl.setAttribute("y1", y1.toFixed(2));
    lineEl.setAttribute("x2", x2.toFixed(2));
    lineEl.setAttribute("y2", y2.toFixed(2));
  }

  function layout() {
    var rootRect = diagram.getBoundingClientRect();
    svg.setAttribute("viewBox", "0 0 " + rootRect.width + " " + rootRect.height);
    svg.setAttribute("preserveAspectRatio", "none");
    svg.setAttribute("width", rootRect.width);
    svg.setAttribute("height", rootRect.height);

    var c = getCenter(center, rootRect);
    Object.keys(map).forEach(function (key) {
      var circle = map[key];
      if (!circle) return;
      var lineEl = svg.querySelector('line[data-link="' + key + '"]');
      if (!lineEl) return;
      setLine(lineEl, circle, c, rootRect);
    });
  }

  layout();
  window.addEventListener("resize", layout);
})();

(function () {
  var tabsRoot = document.querySelector(".insights-tabs");
  if (!tabsRoot) return;

  var tabs = Array.prototype.slice.call(tabsRoot.querySelectorAll(".insights-tab"));
  var panels = Array.prototype.slice.call(document.querySelectorAll(".insights-panel"));

  function setActive(name) {
    tabs.forEach(function (t) {
      var isActive = t.getAttribute("data-panel") === name;
      t.classList.toggle("is-active", isActive);
      t.setAttribute("aria-selected", isActive ? "true" : "false");
      t.tabIndex = isActive ? 0 : -1;
    });

    panels.forEach(function (p) {
      var isActive = p.getAttribute("data-panel") === name;
      if (isActive) {
        p.hidden = false;
        p.classList.add("is-entering");
        requestAnimationFrame(function () {
          p.classList.remove("is-entering");
          p.classList.add("is-active");
        });
      } else {
        p.classList.remove("is-active");
        p.hidden = true;
      }
    });
  }

  tabsRoot.addEventListener("click", function (e) {
    var btn = e.target && e.target.closest ? e.target.closest(".insights-tab") : null;
    if (!btn) return;
    setActive(btn.getAttribute("data-panel"));
  });

  tabsRoot.addEventListener("keydown", function (e) {
    var key = e.key;
    if (key !== "ArrowLeft" && key !== "ArrowRight") return;
    var activeIndex = tabs.findIndex(function (t) {
      return t.classList.contains("is-active");
    });
    var next = key === "ArrowRight" ? activeIndex + 1 : activeIndex - 1;
    if (next < 0) next = tabs.length - 1;
    if (next >= tabs.length) next = 0;
    tabs[next].focus();
    setActive(tabs[next].getAttribute("data-panel"));
    e.preventDefault();
  });
})();

(function () {
  var switchRoot = document.querySelector(".portfolio-switch");
  if (!switchRoot) return;

  var btns = Array.prototype.slice.call(
    switchRoot.querySelectorAll(".portfolio-switch-btn")
  );
  var panels = Array.prototype.slice.call(
    document.querySelectorAll(".portfolio-panel[data-portfolio-panel]")
  );
  var titleEl = document.getElementById("portfolio-title");
  var ewashOnlyBlocks = Array.prototype.slice.call(
    document.querySelectorAll("[data-ewash-only]")
  );

  function setActive(name) {
    btns.forEach(function (b) {
      var isActive = b.getAttribute("data-portfolio") === name;
      b.classList.toggle("is-active", isActive);
      b.setAttribute("aria-selected", isActive ? "true" : "false");
    });

    panels.forEach(function (p) {
      var isActive = p.getAttribute("data-portfolio-panel") === name;
      p.hidden = !isActive;
    });

    ewashOnlyBlocks.forEach(function (el) {
      el.hidden = name !== "ewash";
    });

    if (titleEl) {
      titleEl.innerHTML =
        name === "supersol"
          ? '<span class="portfolio-brand">Super Sol</span> Product Portfolio'
          : '<span class="portfolio-brand">e-WASH</span> Product Portfolio';
    }
  }

  switchRoot.addEventListener("click", function (e) {
    var btn = e.target && e.target.closest ? e.target.closest(".portfolio-switch-btn") : null;
    if (!btn) return;
    setActive(btn.getAttribute("data-portfolio"));
  });
})();

(function () {
  var root = document.querySelector("[data-supersol-connectors]");
  if (!root) return;

  var svg = root.querySelector(".super-sol-portfolio-lines");
  var centerCircle = root.querySelector(".super-sol-portfolio-center-circle");
  if (!svg || !centerCircle) return;

  var map = {
    l1: root.querySelector(".super-sol-feature-box--l1"),
    l2: root.querySelector(".super-sol-feature-box--l2"),
    l3: root.querySelector(".super-sol-feature-box--l3"),
    r1: root.querySelector(".super-sol-feature-box--r1"),
    r2: root.querySelector(".super-sol-feature-box--r2"),
    r3: root.querySelector(".super-sol-feature-box--r3"),
  };

  function getCenter(el, rootRect) {
    var r = el.getBoundingClientRect();
    return {
      x: r.left - rootRect.left + r.width / 2,
      y: r.top - rootRect.top + r.height / 2,
      r: Math.min(r.width, r.height) / 2,
    };
  }

  function layoutSvg() {
    var rootRect = root.getBoundingClientRect();
    svg.setAttribute("viewBox", "0 0 " + rootRect.width + " " + rootRect.height);
    svg.setAttribute("preserveAspectRatio", "none");
    svg.setAttribute("width", rootRect.width);
    svg.setAttribute("height", rootRect.height);
  }

  function setLine(lineEl, fromEl, rootRect) {
    var c = getCenter(centerCircle, rootRect);

    var fr = fromEl.getBoundingClientRect();
    var boxCenterX = fr.left - rootRect.left + fr.width / 2;
    var boxCenterY = fr.top - rootRect.top + fr.height / 2;

    // Start exactly at the midpoint of the side that faces the circle.
    var x1;
    var y1;
    if (Math.abs(c.x - boxCenterX) >= Math.abs(c.y - boxCenterY)) {
      x1 = c.x >= boxCenterX
        ? fr.right - rootRect.left
        : fr.left - rootRect.left;
      y1 = boxCenterY;
    } else {
      x1 = boxCenterX;
      y1 = c.y >= boxCenterY
        ? fr.bottom - rootRect.top
        : fr.top - rootRect.top;
    }

    var dx = c.x - x1;
    var dy = c.y - y1;
    var len = Math.hypot(dx, dy) || 1;
    var ux = dx / len;
    var uy = dy / len;

    var startPad = 1;
    x1 += ux * startPad;
    y1 += uy * startPad;

    // End on the visible edge of the center circle.
    var circleEdgePad = 2;
    var x2 = c.x - ux * Math.max(0, c.r - circleEdgePad);
    var y2 = c.y - uy * Math.max(0, c.r - circleEdgePad);

    lineEl.setAttribute("x1", x1.toFixed(2));
    lineEl.setAttribute("y1", y1.toFixed(2));
    lineEl.setAttribute("x2", x2.toFixed(2));
    lineEl.setAttribute("y2", y2.toFixed(2));
  }

  function layout() {
    var rootRect = root.getBoundingClientRect();
    layoutSvg();
    Object.keys(map).forEach(function (key) {
      var feature = map[key];
      var lineEl = svg.querySelector('line[data-supersol-line="' + key + '"]');
      if (!feature || !lineEl) return;
      setLine(lineEl, feature, rootRect);
    });
  }

  layout();
  window.addEventListener("resize", layout);
  window.addEventListener("scroll", layout, { passive: true });
})();

(function () {
  var flow = document.querySelector(".about-super-sol-flow");
  if (!flow) return;

  var svg = flow.querySelector(".about-super-sol-diagram-lines");
  var topWrap = flow.querySelector(".about-super-sol-top");
  var topImg = topWrap && topWrap.querySelector
    ? topWrap.querySelector(".about-super-sol-top-image")
    : null;
  var leftCard = flow.querySelector(".about-super-sol-card--left");
  var rightCard = flow.querySelector(".about-super-sol-card--right");
  var bottomCard = flow.querySelector(".about-super-sol-card--bottom");
  if (!svg || !topWrap || !topImg || !leftCard || !rightCard || !bottomCard) return;

  function setPath(name, d) {
    var p = svg.querySelector('path[data-about-line="' + name + '"]');
    if (!p) return;
    p.setAttribute("d", d);
  }

  function centerX(rect, rootRect) {
    return rect.left - rootRect.left + rect.width / 2;
  }

  function centerY(rect, rootRect) {
    return rect.top - rootRect.top + rect.height / 2;
  }

  function layout() {
    var rootRect = flow.getBoundingClientRect();
    svg.setAttribute("viewBox", "0 0 " + rootRect.width + " " + rootRect.height);
    svg.setAttribute("preserveAspectRatio", "none");
    svg.setAttribute("width", rootRect.width);
    svg.setAttribute("height", rootRect.height);

    var topRect = topWrap.getBoundingClientRect();
    var leftRect = leftCard.getBoundingClientRect();
    var rightRect = rightCard.getBoundingClientRect();
    var bottomRect = bottomCard.getBoundingClientRect();

    var imgX = centerX(topRect, rootRect);
    var imgBottom = topRect.bottom - rootRect.top;

    var leftTopX = centerX(leftRect, rootRect);
    var leftTopY = leftRect.top - rootRect.top;

    var rightTopX = centerX(rightRect, rootRect);
    var rightTopY = rightRect.top - rootRect.top;

    var leftEdgeX = leftRect.right - rootRect.left;
    var leftEdgeY = centerY(leftRect, rootRect);

    var rightEdgeX = rightRect.left - rootRect.left;
    var rightEdgeY = centerY(rightRect, rootRect);

    var hubX = (leftEdgeX + rightEdgeX) / 2;
    var hubY = (leftEdgeY + rightEdgeY) / 2;

    var bottomTopX = centerX(bottomRect, rootRect);
    var bottomTopY = bottomRect.top - rootRect.top;

    var underImgPad = 2;
    var jointX = imgX;
    var jointY = imgBottom + underImgPad;
    setPath("topLeft", "M" + jointX.toFixed(2) + " " + jointY.toFixed(2) + " L" + leftTopX.toFixed(2) + " " + leftTopY.toFixed(2));
    setPath("topRight", "M" + jointX.toFixed(2) + " " + jointY.toFixed(2) + " L" + rightTopX.toFixed(2) + " " + rightTopY.toFixed(2));
    setPath("midLeft", "M" + leftEdgeX.toFixed(2) + " " + leftEdgeY.toFixed(2) + " L" + hubX.toFixed(2) + " " + hubY.toFixed(2));
    setPath("midRight", "M" + rightEdgeX.toFixed(2) + " " + rightEdgeY.toFixed(2) + " L" + hubX.toFixed(2) + " " + hubY.toFixed(2));
    setPath("down", "M" + hubX.toFixed(2) + " " + hubY.toFixed(2) + " L" + bottomTopX.toFixed(2) + " " + bottomTopY.toFixed(2));
  }

  layout();
  window.addEventListener("resize", layout);
  window.addEventListener("scroll", layout, { passive: true });
})();


// script.js

// 1. Navigation Logic
function showView(viewId) {
    // Hide the main grid
    document.getElementById('main-grid').classList.add('hidden');

    // Hide all other detail views
    document.querySelectorAll('.detail-view').forEach(view => {
        view.classList.add('hidden');
    });

    // Show the selected view
    const activeView = document.getElementById(viewId + '-view');
    if (activeView) {
        activeView.classList.remove('hidden');
        window.scrollTo(0, 0); // Reset scroll to top
    }
}

function goHome() {
    // Hide all detail views
    document.querySelectorAll('.detail-view').forEach(view => {
        view.classList.add('hidden');
    });

    // Show the main grid
    document.getElementById('main-grid').classList.remove('hidden');
    window.scrollTo(0, 0);
}

// 2. Scroll Reveal Logic
const revealElements = document.querySelectorAll('.reveal');

const revealOnScroll = () => {
    const triggerBottom = window.innerHeight * 0.85;

    revealElements.forEach(el => {
        // Only animate if the view is visible
        if (el.offsetParent !== null) {
            const elementTop = el.getBoundingClientRect().top;
            if (elementTop < triggerBottom) {
                el.classList.add('active');
            }
        }
    });
};

// Initial reveal for visible elements
window.addEventListener('load', revealOnScroll);
window.addEventListener('scroll', revealOnScroll);

// Re-check reveal whenever a view is changed
document.addEventListener('click', () => {
    setTimeout(revealOnScroll, 100);
});

