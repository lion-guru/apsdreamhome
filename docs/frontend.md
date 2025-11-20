# Frontend Build & Asset Pipeline

This guide consolidates the modern frontend workflow for APS Dream Home. It replaces the legacy `README_ENHANCED.md` and related performance notes by documenting how we manage JavaScript, CSS, and PWA assets with Vite.

## Current Status

- ✅ Vite-based development and production builds defined in `package.json`.
- ✅ Shared utilities centralized in `assets/js/utils.js` to eliminate duplication.
- ✅ Optional service worker and manifest ready for PWA capabilities.
- ⚠️ Legacy guides (`README_ENHANCED.md`, `ADVANCED_FEATURES_GUIDE.md`) pending archival once this document is complete.

## Build & Optimization Commands

```bash
# Development with hot reload (Vite dev server)
npm run dev

# Production bundle (minified, code-split)
npm run build

# Preview the production bundle locally
npm run preview

# Serve PHP backend and Vite dev server together
npm run start

# Execute CSS/JS optimization helper
npm run optimize

# PWA-oriented build
npm run build:pwa

# Lint and format utilities
npm run lint
npm run format

# Placeholder test runner (extend with real suites)
npm run test
```

## Feature Highlights

### ES6 Modules & Shared Utilities

- Tree-shaken, code-split bundles produced by Vite.
- `assets/js/utils.js` hosts shared helpers importable via standard ES modules.

### CSS Strategy

- Route-based code splitting improves caching and first load experience.
- Critical CSS supported through Vite/PostCSS configuration.
- CSS custom properties standardize theming; Autoprefixer handles vendor prefixes.

### Progressive Web App Support

- Service worker scaffold (Workbox-based) enables offline mode, background sync, and push notifications.
- Manifest includes 192x192 and 512x512 icons plus theme colors for install prompts.
- `npm run build:pwa` generates Lighthouse-friendly assets; ensure HTTPS and proper cache headers in production.

### Performance Tooling

- `npm run performance` (legacy helper) runs Lighthouse/bundle analysis scripts.
- Lazy loaded modules keep initial payload small; prefer dynamic `import()` for heavy widgets.
- Enable gzip/Brotli compression at the web server to maximize hashed asset caching.

## Developer Tooling

| Command | Purpose |
| ------- | ------- |
| `npm run lint` | ESLint across `assets/js/**/*.js`. |
| `npm run format` | Prettier formatting for JS/CSS (use `--check` in CI). |
| `node test-system.js` | Legacy end-to-end smoke tests—update or replace as suites mature. |
| `npm run bundle:analyze` | Visualize bundle composition via the analyzer plugin. |

> Configure your editor to run ESLint/Prettier on save to avoid formatting noise in commits.

## Monitoring & Debugging

- Lighthouse audits: run `npm run performance` or use Chrome DevTools for ad-hoc checks.
- Bundle analyzer: `npm run bundle:analyze` opens a treemap highlighting heavy dependencies.
- Vite dev overlay surfaces runtime errors with actionable stack traces.
- Pair browser console logs with backend Monolog logs for end-to-end tracing.

## Project Structure (Frontend Artifacts)

```text
apsdreamhome/
├── assets/
│   ├── css/        # Consolidated stylesheets (~6 core files)
│   ├── js/         # Modular JS, including utils + PWA scripts
│   └── img/        # Optimized images and icons
├── package.json    # npm scripts and dependencies
├── vite.config.js  # Build, alias, and PWA configuration
└── optimize.js     # Custom post-build optimizer (optional)
```

## Implementation Notes from Advanced Features Guide

- **PWA**: Register the service worker only in production builds; ensure HTTPS and cache-busting headers to prevent stale assets.
- **Virtual Tours & Rich Media**: Load heavy panorama/AR bundles lazily to protect initial load times.
- **Social & Localization**: Dynamically import locale dictionaries and social widgets per route to avoid bloating the base bundle.
- **Security Headers**: Align CSP rules with hashed asset names and the service worker scope.

## Integration Checklist

1. Confirm Apache/Nginx rewrites permit serving Vite’s `dist/` assets.
2. Reference generated bundles (e.g., `dist/assets/*.js`) in layout templates such as `includes/enhanced_universal_template.php`.
3. Configure caching headers: long-lived for hashed assets, shorter for service worker/manifest files.
4. Use `npm run start` during development to run PHP (`php -S`) and Vite via `run-p` for seamless integration.

## Next Actions

1. Update documentation links to point to this guide instead of `README_ENHANCED.md` / `ADVANCED_FEATURES_GUIDE.md`.
2. Move superseded frontend docs into `docs/archive/` once stakeholders confirm migration is complete.
3. Extend this guide with troubleshooting tips (cache busting, dependency updates, service worker pitfalls).
4. Add concrete code snippets showing module imports and service worker registration when available.
