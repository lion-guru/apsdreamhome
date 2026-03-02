<script src="/public/assets/js/location-bank-helper.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Placeholder functions if they don't exist in other scripts
        if (typeof loadBankingDetails !== 'function') window.loadBankingDetails = function() {};
        if (typeof loadAuditLogs !== 'function') window.loadAuditLogs = function() {};
        if (typeof checkKYCStatus !== 'function') window.checkKYCStatus = function() {};

        loadBankingDetails();
        loadAuditLogs();
        checkKYCStatus();

        // IFSC Verification is now handled by location-bank-helper.js
        // It automatically attaches to #verifyIFSC and populates fields.
    });
</script>