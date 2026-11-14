<?php
// Codemod: add aria-label to icon-only <a>/<button> in admin views.
$map = [
    'fa-eye' => 'View',
    'fa-edit' => 'Edit',
    'fa-pen' => 'Edit',
    'fa-pencil' => 'Edit',
    'fa-pen-to-square' => 'Edit',
    'fa-pen-nib' => 'Edit',
    'fa-trash' => 'Delete',
    'fa-trash-alt' => 'Delete',
    'fa-trash-can' => 'Delete',
    'fa-download' => 'Download',
    'fa-search' => 'Search',
    'fa-plus' => 'Add',
    'fa-times' => 'Close',
    'fa-xmark' => 'Close',
    'fa-times-circle' => 'Reject',
    'fa-print' => 'Print',
    'fa-external-link' => 'Open',
    'fa-external-link-alt' => 'Open',
    'fa-sync' => 'Refresh',
    'fa-sync-alt' => 'Refresh',
    'fa-rotate' => 'Refresh',
    'fa-rotate-right' => 'Refresh',
    'fa-refresh' => 'Refresh',
    'fa-columns' => 'Columns',
    'fa-table-columns' => 'Columns',
    'fa-undo' => 'Reset',
    'fa-ban' => 'Deactivate',
    'fa-ban-slash' => 'Deactivate',
    'fa-check' => 'Activate',
    'fa-check-circle' => 'Approve',
    'fa-circle-check' => 'Approve',
    'fa-file-csv' => 'Export CSV',
    'fa-file-excel' => 'Export Excel',
    'fa-file-pdf' => 'PDF',
    'fa-file-arrow-down' => 'Download',
    'fa-user' => 'User',
    'fa-user-pen' => 'Edit user',
    'fa-user-edit' => 'Edit user',
    'fa-save' => 'Save',
    'fa-floppy-disk' => 'Save',
    'fa-copy' => 'Copy',
    'fa-clone' => 'Copy',
    'fa-filter' => 'Filter',
    'fa-chevron-up' => 'Toggle',
    'fa-chevron-down' => 'Toggle',
    'fa-chevron-left' => 'Toggle',
    'fa-chevron-right' => 'Toggle',
    'fa-sort' => 'Sort',
    'fa-image' => 'Image',
    'fa-images' => 'Gallery',
    'fa-camera' => 'Image',
    'fa-paper-plane' => 'Send',
    'fa-bell' => 'Notify',
    'fa-bullhorn' => 'Broadcast',
    'fa-lock' => 'Lock',
    'fa-unlock' => 'Unlock',
    'fa-link' => 'Link',
    'fa-ellipsis' => 'More actions',
    'fa-ellipsis-h' => 'More actions',
    'fa-ellipsis-v' => 'More actions',
    'fa-calendar' => 'Calendar',
    'fa-calendar-days' => 'Calendar',
    'fa-envelope' => 'Email',
    'fa-phone' => 'Call',
    'fa-phone-alt' => 'Call',
    'fa-whatsapp' => 'WhatsApp',
    'fa-clipboard-check' => 'Checklist',
    'fa-sign-out' => 'Log out',
    'fa-right-from-bracket' => 'Log out',
    'fa-map-marker' => 'Map',
    'fa-location-dot' => 'Map',
    'fa-map-marked-alt' => 'Map',
    'fa-upload' => 'Upload',
    'fa-cloud-upload-alt' => 'Upload',
    'fa-cloud-arrow-up' => 'Upload',
    'fa-history' => 'History',
    'fa-clock' => 'History',
    'fa-undo-alt' => 'Reset',
    'fa-flag' => 'Flag',
    'fa-star' => 'Star',
    'fa-heart' => 'Like',
    'fa-share-alt' => 'Share',
    'fa-share' => 'Share',
    'fa-question-circle' => 'Help',
    'fa-circle-question' => 'Help',
    'fa-info-circle' => 'Details',
    'fa-circle-info' => 'Details',
    'fa-tasks' => 'Tasks',
    'fa-list' => 'List',
    'fa-toggle-on' => 'Toggle',
    'fa-toggle-off' => 'Toggle',
    'fa-power-off' => 'Deactivate',
    'fa-ban-clipboard' => 'Clear',
    'fa-eye-slash' => 'Hide',
    'fa-shield-alt' => 'Security',
    'fa-shield-halved' => 'Security',
    'fa-key' => 'Key',
    'fa-file-invoice' => 'Invoice',
    'fa-money-bill' => 'Payment',
    'fa-credit-card' => 'Payment',
    'fa-rupee-sign' => 'Amount',
    'fa-comments' => 'Chat',
    'fa-comment' => 'Chat',
    'fa-chart-line' => 'Analytics',
    'fa-chart-bar' => 'Analytics',
    'fa-chart-pie' => 'Analytics',
    'fa-sliders-h' => 'Settings',
    'fa-cog' => 'Settings',
    'fa-gear' => 'Settings',
    'fa-wrench' => 'Repair',
    'fa-magic' => 'Generate',
    'fa-wand-magic-sparkles' => 'Generate',
    'fa-bolt' => 'Action',
    'fa-plug' => 'Test connection',
    'fa-play' => 'Run',
    'fa-bars' => 'Toggle navigation',
    'fa-archive' => 'Archive',
    'fa-thumbs-up' => 'Helpful',
    'fa-thumbs-down' => 'Not helpful',
    'fa-tag' => 'Tag',
    'fa-facebook-f' => 'Facebook',
    'fa-twitter' => 'Twitter',
    'fa-instagram' => 'Instagram',
    'fa-linkedin-in' => 'LinkedIn',
    'fa-phone' => 'Call',
];

$root = 'C:/xampp/htdocs/apsdreamhome/app/views';
if (!is_dir($root)) { $root = __DIR__ . '/../../../htdocs/apsdreamhome/app/views'; }

// Bootstrap Icons additions
$biMap = [
    "bi-heart" => "Save to favorites",
    "bi-send" => "Send",
    "bi-zoom-in" => "Zoom in",
    "bi-zoom-out" => "Zoom out",
    "bi-arrows-fullscreen" => "Reset zoom",
    "bi-x" => "Close",
    "bi-x-lg" => "Close",
    "bi-expand" => "Expand",
    "bi-list" => "List",
    "bi-check" => "Confirm",
    "bi-trash" => "Delete",
    "bi-pencil" => "Edit",
    "bi-eye" => "View",
    "bi-plus" => "Add",
    "bi-search" => "Search",
    "bi-funnel" => "Filter",
    "bi-telephone" => "Call",
    "bi-telephone-outbound" => "Call",
    "bi-whatsapp" => "WhatsApp",
    "bi-envelope" => "Email",
    "bi-mic" => "Voice input",
    "bi-camera" => "Camera",
    "bi-image" => "Image",
    "bi-star" => "Favorite",
    "bi-share" => "Share",
    "bi-list-check" => "Steps",
    "bi-chevron-up" => "Toggle",
    "bi-chevron-down" => "Toggle",
    "bi-arrow-repeat" => "Refresh",
    "bi-arrow-clockwise" => "Refresh",
    "bi-check-circle" => "Approve",
    "bi-x-circle" => "Reject",
];
foreach ($biMap as $k => $v) { $map[$k] = $v; }

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
$dry = in_array('--dry', $argv, true);
$changed = [];
$unmatchedIcons = [];
foreach ($it as $file) {
    if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'php') continue;
    $src = file_get_contents($file);
    $out = $src;
    // icon-only <a ...> ... </a>  and  <button ...> ... </button>
    $pattern = '/(<(?:a|button)\b[^>]*>)\s*<(?:i|span)\b([^>]*)>\s*<\/(?:i|span)>\s*<\/(?:a|button)>/s';
    $out = preg_replace_callback($pattern, function ($m) use ($map, &$unmatchedIcons, $file) {
        $openTag = $m[1];
        $iconAttrs = $m[2];
        if (preg_match('/\b(title|aria-label|aria-labelledby)\s*=/i', $openTag)) {
            return $m[0]; // already named
        }
        if (preg_match_all('/\b((?:fa|bi)-[a-z0-9-]+)/i', $iconAttrs, $all)) {
            $label = null;
            $icon = null;
            foreach ($all[1] as $tok) {
                $t = strtolower($tok);
                if (isset($map[$t])) { $label = $map[$t]; $icon = $t; break; }
                if ($icon === null) { $icon = $t; }
            }
            if ($label === null) {
                $unmatchedIcons[$icon ?? 'unknown'] = true;
                return $m[0]; // leave for manual handling
            }
            // insert aria-label before the closing > of the opening tag
            $openTag = rtrim($openTag, '>');
            $openTag .= ' aria-label="' . $label . '">';
            return $openTag . '<i' . $iconAttrs . '></i></' . (strpos($m[0], '</button>') !== false ? 'button' : 'a') . '>';
        }
        return $m[0];
    }, $out);
    if ($out !== $src) {
        $changed[] = $file->getPathname();
        if (!$dry) file_put_contents($file, $out);
    }
}
echo ($dry ? "[DRY] " : ""), "changed files: ", count($changed), PHP_EOL;
echo "unmatched icons: ", json_encode(array_keys($unmatchedIcons)), PHP_EOL;
foreach ($changed as $c) echo "  M ", str_replace('C:/xampp/htdocs/apsdreamhome/', '', str_replace('\\', '/', $c)), PHP_EOL;

