(function () {
  var cards = Array.prototype.slice.call(
    document.querySelectorAll(".product-process-card")
  );
  if (!cards.length) return;

  function setExpanded(card, open) {
    card.classList.toggle("is-open", open);
    card.setAttribute("aria-expanded", open ? "true" : "false");
  }

  function closeOthers(activeCard) {
    cards.forEach(function (card) {
      if (card === activeCard) return;
      setExpanded(card, false);
    });
  }

  function onActivate(card) {
    var shouldOpen = !card.classList.contains("is-open");
    closeOthers(card);
    setExpanded(card, shouldOpen);
  }

  cards.forEach(function (card) {
    card.classList.remove("is-open");
    card.setAttribute("role", "button");
    card.setAttribute("tabindex", "0");
    card.setAttribute("aria-expanded", "false");
    card.style.cursor = "pointer";

    card.addEventListener("click", function () {
      onActivate(card);
    });

    card.addEventListener("keydown", function (e) {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        onActivate(card);
      }
    });
  });
})();

