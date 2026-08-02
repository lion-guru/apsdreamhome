<?php
foreach (glob('app/views/auth/*.php') as $f) {
    echo basename($f) . "\n";
}