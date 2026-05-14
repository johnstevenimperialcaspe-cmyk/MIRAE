(function () {
  var slots = document.querySelectorAll("[data-managed-content]");
  if (!slots.length) return;

  var cache = {};

  function applyContent(slot, content) {
    if (!content) {
      slot.style.display = "none";
      return;
    }

    var slotField = slot.getAttribute("data-managed-field");

    var parsed = null;
    try {
      parsed = JSON.parse(content);
    } catch (err) {
      parsed = null;
    }

    function setField(el, key) {
      if (!el || !key) return;
      var value = parsed && typeof parsed === "object" ? parsed[key] : null;
      if (value == null) value = "";
      if (!value) {
        if (el.hasAttribute("data-managed-hide-empty")) {
          el.style.display = "none";
        }
        return;
      }

      el.style.removeProperty("display");

      if (key === "html") {
        el.innerHTML = value;
      } else {
        el.textContent = value;
      }
    }

    if (parsed && typeof parsed === "object") {
      if (slotField) {
        setField(slot, slotField);
        return;
      }

      var fieldEls = slot.querySelectorAll("[data-managed-field]");
      if (fieldEls.length) {
        fieldEls.forEach(function (el) {
          setField(el, el.getAttribute("data-managed-field"));
        });
        return;
      }
    }

    if (slotField === "html") {
      slot.innerHTML = content;
      return;
    }

    slot.innerHTML = content;
  }

  slots.forEach(function (slot) {
    var pageKey = slot.getAttribute("data-managed-content");
    if (!pageKey) return;

    if (cache[pageKey]) {
      cache[pageKey].then(function (content) {
        applyContent(slot, content);
      });
      return;
    }

    cache[pageKey] = fetch("content-api.php?page=" + encodeURIComponent(pageKey))
      .then(function (response) {
        if (!response.ok) return "";
        return response.json();
      })
      .then(function (data) {
        return data && data.content ? data.content : "";
      })
      .catch(function () {
        return "";
      });

    cache[pageKey].then(function (content) {
      applyContent(slot, content);
    });
  });
})();
