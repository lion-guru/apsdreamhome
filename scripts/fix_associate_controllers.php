<?php
// Fix CrmController
$crmFile = __DIR__ . '/../app/Http/Controllers/Associate/CrmController.php';
$crmContent = file_get_contents($crmFile);
$crmContent = str_replace('Database::getInstance()->getConnection()', 'Database::getInstance()', $crmContent);
file_put_contents($crmFile, $crmContent);
echo "CrmController updated.\n";

// Fix SiteVisitController
$svFile = __DIR__ . '/../app/Http/Controllers/Associate/SiteVisitController.php';
$svContent = file_get_contents($svFile);
$svContent = str_replace('Database::getInstance()->getConnection()', 'Database::getInstance()', $svContent);
// Fix $nextAction bug on line 164
$svContent = str_replace(
    '$params = array_merge([$feedback, $nextAction, $id, $userId], TenantContext::getId() > 1 ? [TenantContext::getId()] : []);',
    '$params = array_merge([$feedback, $id, $userId], TenantContext::getId() > 1 ? [TenantContext::getId()] : []);',
    $svContent
);
// Fix associate_id on line 248
$svContent = str_replace('WHERE id = ? AND associate_id = ?', 'WHERE id = ? AND agent_id = ?', $svContent);
file_put_contents($svFile, $svContent);
echo "SiteVisitController updated.\n";
