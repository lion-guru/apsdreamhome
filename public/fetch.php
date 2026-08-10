<?php
$css = file_get_contents("https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css");
$js = file_get_contents("https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js");
file_put_contents(__DIR__ . "/assets/css/bootstrap.min.css", $css);
file_put_contents(__DIR__ . "/assets/js/bootstrap.bundle.min.js", $js);
echo "Fetched successfully. CSS length: " . strlen($css) . ", JS length: " . strlen($js);
