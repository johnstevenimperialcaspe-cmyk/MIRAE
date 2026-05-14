# UI and Styling Guide

The design system uses a **Glassmorphism** aesthetic combined with a technical, clean "Eco-Green" color palette.

## Design Tokens (CSS Variables)

- `--green`: `#7db68b` (Main brand color)
- `--green-rgb`: `125, 182, 139` (Used for rgba/alpha effects)
- `--text-main`: `#222222`
- `--radius-lg`: `18px`
- `--shadow-soft`: `0 18px 40px rgba(0, 0, 0, 0.12)`

## Key UI Components

### 1. Glass Cards
Used in certifications and product highlights.
```css
.glass {
  background: rgba(255, 255, 255, 0.16);
  backdrop-filter: blur(2px);
  border: 1px solid rgba(255, 255, 255, 0.3);
}
```

### 2. Technical Tables
Located in `data.css`. These use `sticky` headers and custom scrollbars to maintain context during long data reads.

### 3. Responsive Layouts
- **Desktop**: 4-column grids for products.
- **Tablet/Mobile**: Switches to 2-column or 1-column stacks with centered text for better ergonomics.