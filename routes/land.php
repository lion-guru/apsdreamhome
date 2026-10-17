<?php
/**
 * Land-lane routes (Session 258, extracted S291 to de-tangle routes/web.php).
 *
 * Plot–Gata–Kisan Link Desk: joins plots.gata_number <-> land_parcels.khasra_no
 * <-> farmer_land_holdings <-> land_deals. Zero new tables. Loaded AFTER
 * routes/web.php (see public/index.php); paths are unique so order is free.
 *
 * Idempotent by construction: pure additive registrations, no overrides.
 */

// ============================================================
// PLOT-GATA-KISAN LINK DESK (reuses land stack, no new tables)
// ============================================================
$router->get('/admin/land-plot-link', 'App\\Http\\Controllers\\Admin\\FarmerAcquisitionController@plotGataLink');
$router->post('/admin/land-plot-link/owner', 'App\\Http\\Controllers\\Admin\\FarmerAcquisitionController@updateParcelOwner');
$router->post('/admin/land-plot-link/link-plot', 'App\\Http\\Controllers\\Admin\\FarmerAcquisitionController@linkPlotParcel');
