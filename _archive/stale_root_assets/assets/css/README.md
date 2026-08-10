# APS Dream Home CSS Directory

This directory contains the CSS assets for the **APS Dream Home** project.

## ⚠️ Important: CSS Consolidation

For optimal production performance, the individual CSS files in the root of this folder have been consolidated into **4 optimized bundles** located in:
👉 `assets/css/consolidated/`

### The Consolidated Bundles:
1. **`aps-core.css`**: Design system foundation (variables, reset, typography, utilities).
   * Consolidates: `style.css`, `frontend.css`, `frontend-enhancements.css`
2. **`aps-components.css`**: Reusable UI components.
   * Consolidates: `customer-pages.css`, `notification-system.css`, `image-gallery.css`, `image-uploader.css`, `live-chat-widget.css`
3. **`aps-layout.css`**: Layout utilities (grid, flex, header, sidebar).
   * Consolidates: `header.css`, `mobile-responsive.css`, `modern-style.css`, `advanced-features.css`
4. **`aps-pages.css`**: Page-specific styles (deferred/non-blocking load).
   * Consolidates: `chatbot.css`, `ai-chat.css`, `ai-chat-enhanced.css`, `ai-features.css`, `employee.css`, and others.

---

## 🛠️ Developer Guidelines

1. **Production Layouts**: All core layouts (`base.php`, `customer.php`, `admin.php`, `agent.php`, `employee.php`) use the consolidated bundles. Do **NOT** link individual CSS files in production layouts.
2. **Legacy/Test Files**: The individual CSS files in this root directory are kept **only** for backwards compatibility with standalone stubs, legacy pages, and visual unit tests (e.g. `test_image_gallery.php`).
3. **Adding New Styles**: 
   * Do not edit the root individual CSS files if you want those styles in production.
   * Write new styles directly inside the appropriate consolidated bundle file in the `consolidated/` folder.
