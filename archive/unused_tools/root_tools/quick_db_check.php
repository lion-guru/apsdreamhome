<?php
require __DIR__ . '/../includes/db_config.php';

$c = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($c->connect_error) {
    die("❌ Connect error: " . $c->connect_error);
}

$res = $c->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE()");
$tbl = $res->fetch_row()[0];

echo "✅ Connected. Tables: $tbl\n";
$c->close();