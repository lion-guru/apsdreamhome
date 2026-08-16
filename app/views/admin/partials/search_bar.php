<?php

/**
 * Standard Search Bar Partial
 * Consistent search functionality across all admin pages
 */

// Get current URL for form submission
$currentUrl = $_SERVER['REQUEST_URI'] ?? '';
$searchValue = $_GET['search'] ?? '';
?>

<div class="row mb-3">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-search"></i>
            </span>
            <input type="text"
                class="form-control"
                name="search"
                placeholder="Search..."
                value="<?php echo htmlspecialchars($searchValue ?? ''); ?>"
                id="searchInput">
            <button class="btn btn-primary" type="submit">
                Search
            </button>
        </div>
    </div>
    <div class="col-md-6 text-end">
        <button class="btn btn-outline-secondary" onclick="location.href='<?php echo $currentUrl; ?>'">
            <i class="fas fa-sync-alt me-2"></i>Reset
        </button>
    </div>
</div>