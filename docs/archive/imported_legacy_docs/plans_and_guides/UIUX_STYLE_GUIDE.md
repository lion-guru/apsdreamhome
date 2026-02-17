# APS Dream Home - Design System Style Guide

This style guide documents the design system components and usage patterns for the APS Dream Home platform.

## 1. Color Palette

Based on the [modern-design-system.css](file:///c:/xampp/htdocs/apsdreamhome/assets/css/modern-design-system.css).

### Primary Colors (Blues)
Used for branding, primary buttons, and active states.
- **Primary 500**: `#3b82f6` (Main Brand Color)
- **Primary 600**: `#2563eb` (Hover State)
- **Primary 700**: `#1d4ed8` (Pressed State)

### Secondary Colors (Slates)
Used for text, backgrounds, and neutral elements.
- **Secondary 900**: `#0f172a` (Headings)
- **Secondary 600**: `#475569` (Body Text)
- **Secondary 100**: `#f1f5f9` (Backgrounds)

### Semantic Colors
- **Success**: `#22c55e` (Completed, Verified)
- **Warning**: `#f59e0b` (Pending, Attention)
- **Error**: `#ef4444` (Alerts, Critical)

## 2. Typography

- **Headings**: `Plus Jakarta Sans` - Bold/ExtraBold for hierarchy.
- **Body**: `Inter` - Regular/Medium for readability.
- **Monospace**: `Fira Code` - Data, IDs, and Technical logs.

### Scale
- **Display**: `3rem` (Hero sections)
- **H1**: `2.25rem`
- **H2**: `1.875rem`
- **Base**: `1rem` (16px)
- **Small**: `0.875rem` (Captions, Metadata)

## 3. UI Components

### Buttons
- **Primary**: Solid background, white text, 8px border-radius.
- **Secondary**: Outlined or light background, primary text.
- **Ghost**: No background, primary text, subtle hover.

### Cards
- **Shadow**: `0 4px 6px -1px rgb(0 0 0 / 0.1)`
- **Border**: `1px solid var(--secondary-200)`
- **Padding**: `1.5rem` (24px)

### Forms
- **Input Height**: `48px`
- **Focus Ring**: `2px ring var(--primary-300)`
- **Labels**: Bold, 14px, secondary-700.

## 4. Layout & Grid
- **Container**: Max-width 1280px.
- **Spacing Unit**: 4px base (4, 8, 12, 16, 24, 32, 48, 64).
- **Gutter**: 24px (1.5rem).

## 5. Accessibility Guidelines
- **Contrast**: Minimum 4.5:1 for body text.
- **Focus**: Visible focus rings for keyboard navigation.
- **Semantic HTML**: Proper use of `<main>`, `<nav>`, `<article>`, etc.
- **Aria Labels**: Required for icon-only buttons.
