<?php
/**
 * Include Wrapper - Footer
 * This file redirects to the actual layout footer
 * Created to fix include path issues in views
 */

// Include the actual footer from layouts
$footerPath = __DIR__ . '/../layouts/footer.php';
if (file_exists($footerPath)) {
    include $footerPath;
} else {
    // Fallback minimal footer
    ?>
    </div>
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> APS Dream Home. All rights reserved.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
}
