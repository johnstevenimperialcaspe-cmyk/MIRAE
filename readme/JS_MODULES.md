# JavaScript Modules Reference

### `main.js`
- **Hero Synchronization**: Handles the cross-fading and sliding logic of the landing page hero section.
- **Features Diagram**: Manages the SVG line-drawing logic.
- **Tabbed Insights**: Controls the switching between "Safety", "Detergency", etc.
- **Portfolio Toggle**: Globally switches the context between "e-WASH" and "Super Sol" views.

### `product-cart.js`
- **Cart Operations**: `addToCart`, `setItemQty`, and `renderCart`.
- **Formatting**: `formatPHP` handles currency representation.
- **Animations**: `animateFlyToNav` manages the visual feedback when adding items.

### `data.js`
- **Legalities Gallery**: Updates the certificate viewer when a thumbnail is clicked.
- **Test Switcher**: A sophisticated toggle for switching between e-WASH and Super Sol lab tests, utilizing URL hashes (`#ewash`, `#supersol`) for deep linking.
- **Auto-Play Sliders**: Manages the certificate and trial image rotations using `setInterval`.

### `carousel.js`
- **Performance Tabs**: Manages the "Characteristics", "Functions", and "Core Values" tabs specifically for the product performance sections.

### `product-process.js`
- **Accordion Logic**: Manages the "Process" cards, ensuring only one card is expanded at a time for optimal vertical space usage.

## Event Handling Strategy

Most modules use a "Delegated Action" or "Initialization Loop" pattern:

```javascript
// Common pattern found in the codebase
cards.forEach(function (card) {
  card.addEventListener("click", function () {
    onActivate(card);
  });
});
```

This keeps the code easy to debug and decoupled from large frameworks.