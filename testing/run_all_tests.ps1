# Run all tests end-to-end (Windows PowerShell) - native PS calls
Write-Host 'Starting test suite...'

# DB health
php testing/db_health_check.php

# Seed data (idempotent)
php tools/db_seed_testdata.php

# UI visual tests (header)
node testing/visual_tests/header_visual_test.js

# Admin tests
node testing/visual_tests/admin_login_smoke_test.js
node testing/visual_tests/admin_user_properties_access_test.js
node testing/visual_tests/admin_property_workflow_smoke_test.js

# End-to-end skeletons
node testing/visual_tests/e2e_user_flow.js
node testing/visual_tests/header_visual_test.js

Write-Host 'Test suite finished.'
