# Frontend Skills

## CSS Organization
- Main CSS: `public/assets/css/style.css`
- Header CSS: `public/assets/css/header-fix.css`
- Responsive: Mobile-first approach

## Bootstrap 5 Classes
- Container: `.container`, `.container-fluid`
- Grid: `.row`, `.col-md-4`
- Buttons: `.btn`, `.btn-primary`, `.btn-sm`
- Cards: `.card`, `.card-body`

## Font Awesome 6
```html
<i class="fas fa-home"></i>
<i class="fab fa-facebook"></i>
```

## Common Fixes

### Fixed Header Issue
```css
body { padding-top: 70px; }
.premium-header { position: fixed; z-index: 1031; }
```

### Dropdown Menu
```css
.dropdown-menu {
    position: absolute;
    z-index: 1050;
}
```

### Navbar Collapse
```css
.navbar-collapse {
    flex-basis: auto;
    flex-grow: 0;
}
```

## JavaScript
- Header JS: `public/assets/js/premium-header.js`
- Bootstrap Bundle: CDN loaded in base.php

## Screenshot Testing
Use Playwright for browser preview
