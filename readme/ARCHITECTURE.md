# System Architecture

The platform follows a **Component-Based Vanilla Architecture**. Instead of a heavy framework, functionality is encapsulated in Immediately Invoked Function Expressions (IIFEs) mapped to specific DOM sectors.

## Technical Stack

- **Logic**: Vanilla JavaScript (ES6+).
- **Styling**: CSS3 with CSS Variables (Custom Properties) for theming.
- **Graphics**: SVG for dynamic connector lines in feature diagrams.
- **State Management**: Localized state using `Map` objects (Cart) and Closure-based indices (Carousels).

## Key Architectural Patterns

### 1. Dynamic SVG Interconnects
Found in `main.js`, this logic calculates the bounding rectangles of UI elements and draws SVG lines between "Feature Circles" and a "Center Hub". This ensures that even on window resize, the "connectors" remain visually anchored.

### 2. Map-Based Cart State
The `product-cart.js` module uses a `Map` to store cart items. This provides O(1) lookups for quantity updates while maintaining item insertion order for the UI.

### 3. Drawer & Modal Logic
The system uses a custom "Inline Drawer" pattern for technical interpretations. This is implemented via CSS transforms and JS-controlled classes (`.drawer-open`), avoiding the overhead of heavy UI libraries.

### 4. Responsive Data Visualization
Technical lab reports (JFRL/Intertek) are rendered through a combination of CSS Grid and overflow containers, ensuring that complex multi-column data remains readable on mobile devices.

## Data Flow: Cart Example

1.  **Event**: User clicks `.cart-btn`.
2.  **Extraction**: `getPayloadFromBtn()` reads `data-*` attributes.
3.  **State Update**: `addToCart()` modifies the internal `Map`.
4.  **UI Sync**: `renderCart()` re-renders the sidebar and updates the `.cart-badge`.
5.  **Animation**: `animateFlyToNav()` creates a temporary DOM clone and animates it toward the header.

## Performance Considerations
The site utilizes `prefers-reduced-motion` media queries to disable heavy fly-out animations for users with motion sensitivity.