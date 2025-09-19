document.addEventListener('DOMContentLoaded', function() {
    // Migration Creation Form
    const migrationForm = document.querySelector('form[name="create_migration"]');
    const migrationNameInput = migrationForm.querySelector('input[name="migration_name"]');
    const migrationTypeSelect = migrationForm.querySelector('select[name="migration_type"]');

    // Real-time validation
    migrationNameInput.addEventListener('input', function() {
        const value = this.value.trim();
        const submitButton = migrationForm.querySelector('button[type="submit"]');
        
        if (value.length < 3) {
            this.classList.add('is-invalid');
            submitButton.disabled = true;
        } else {
            this.classList.remove('is-invalid');
            submitButton.disabled = false;
        }
    });

    // Apply Migrations Button Interaction
    const applyMigrationsForm = document.querySelector('form[name="apply_migrations"]');
    const applyMigrationsButton = applyMigrationsForm.querySelector('button[type="submit"]');
    
    applyMigrationsButton.addEventListener('click', function(e) {
        this.innerHTML = 'Applying Migrations...';
        this.disabled = true;
    });

    // Schema Diff Report Generation
    const diffReportButton = document.querySelector('button[name="generate_diff_report"]');
    diffReportButton.addEventListener('click', function(e) {
        this.innerHTML = 'Generating Report...';
        this.disabled = true;
    });

    // Periodic Migration Status Check
    function checkMigrationStatus() {
        fetch('ajax_migration_status.php')
            .then(response => response.json())
            .then(data => {
                const pendingMigrationsList = document.querySelector('.pending-migrations ul');
                
                if (data.pendingMigrations.length === 0) {
                    pendingMigrationsList.innerHTML = '<li class="list-group-item">No pending migrations</li>';
                    applyMigrationsButton.disabled = true;
                } else {
                    pendingMigrationsList.innerHTML = data.pendingMigrations
                        .map(migration => `<li class="list-group-item">${migration}</li>`)
                        .join('');
                    applyMigrationsButton.disabled = false;
                }
            })
            .catch(error => console.error('Error checking migration status:', error));
    }

    // Check migration status every 5 minutes
    setInterval(checkMigrationStatus, 5 * 60 * 1000);
});
