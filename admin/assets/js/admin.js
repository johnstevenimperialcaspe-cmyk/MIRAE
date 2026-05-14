(function () {
  var toggle = document.getElementById("sidebar-toggle");
  var layout = document.querySelector(".admin-layout");

  window.addEventListener("load", function() {
    var preloader = document.querySelector(".preloader");
    if (preloader) {
      preloader.classList.add("fade-out");
    }
  });

  if (!toggle || !layout) return;

  toggle.addEventListener("click", function () {
    layout.classList.toggle("sidebar-collapsed");
  });
})();

(function () {
  var modal = document.getElementById("product-modal");
  if (!modal) return;

  var title = document.getElementById("product-modal-title");
  var desc = document.getElementById("product-modal-desc");
  var editLink = document.getElementById("product-modal-edit");
  var deleteButton = document.getElementById("product-modal-delete");
  var deleteForm = document.getElementById("dashboard-delete-form");
  var deleteId = document.getElementById("dashboard-delete-id");
  var actionButtons = document.querySelectorAll("[data-action]");

  function openModal() {
    modal.classList.add("is-open");
    modal.setAttribute("aria-hidden", "false");
  }

  function closeModal() {
    modal.classList.remove("is-open");
    modal.setAttribute("aria-hidden", "true");
  }

  actionButtons.forEach(function (button) {
    button.addEventListener("click", function () {
      var action = button.getAttribute("data-action");
      var id = button.getAttribute("data-id") || "";
      var name = button.getAttribute("data-name") || "this product";

      if (action === "edit") {
        title.textContent = "Edit product";
        desc.textContent = "Open the product editor for " + name + "?";
        if (editLink) {
          editLink.style.display = "inline-flex";
          editLink.href = "products.php?edit=" + encodeURIComponent(id);
        }
        if (deleteButton) {
          deleteButton.style.display = "none";
        }
      } else {
        title.textContent = "Delete product";
        desc.textContent = "Delete " + name + "? This cannot be undone.";
        if (editLink) {
          editLink.style.display = "none";
        }
        if (deleteButton) {
          deleteButton.style.display = "inline-flex";
          deleteButton.setAttribute("data-id", id);
        }
      }

      openModal();
    });
  });

  if (deleteButton) {
    deleteButton.addEventListener("click", function () {
      if (!deleteForm || !deleteId) return;
      deleteId.value = deleteButton.getAttribute("data-id") || "";
      deleteForm.submit();
    });
  }

  modal.addEventListener("click", function (event) {
    var target = event.target;
    if (target && target.getAttribute("data-modal-close") === "true") {
      closeModal();
    }
  });

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape" && modal.classList.contains("is-open")) {
      closeModal();
    }
  });
})();
