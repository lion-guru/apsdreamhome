<?php
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '2jcePXuNaOfEyo6I5wJVkG');

echo "=== PROPERTIES SCHEMA ===" . PHP_EOL;
$r = $db->query("DESCRIBE properties")->fetchAll(PDO::FETCH_COLUMN);
echo implode(', ', $r) . PHP_EOL;

echo PHP_EOL . "=== SAMPLE PROPERTIES ===" . PHP_EOL;
$r = $db->query("SELECT id, title, price, bedrooms, type, city, area_sqft FROM properties WHERE status='active' LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
foreach ($r as $row) echo "  ID={$row['id']} {$row['title']} type={$row['type']} price={$row['price']} city={$row['city']}" . PHP_EOL;

echo PHP_EOL . "=== COLONIES ===" . PHP_EOL;
$r = $db->query("SELECT id, slug, image_path, starting_price, total_plots, available_plots FROM colonies")->fetchAll(PDO::FETCH_ASSOC);
foreach ($r as $row) echo "  " . str_pad($row['slug'], 25) . " price={$row['starting_price']} plots={$row['available_plots']}/{$row['total_plots']} img=" . substr($row['image_path'] ?? 'null', 0, 60) . PHP_EOL;

echo PHP_EOL . "=== TEAM ===" . PHP_EOL;
$r = $db->query("SELECT id, name, position, photo FROM team_members WHERE status=1")->fetchAll(PDO::FETCH_ASSOC);
foreach ($r as $row) echo "  " . str_pad($row['name'], 25) . str_pad($row['position'] ?? '', 30) . "photo=" . ($row['photo'] ?: 'null') . PHP_EOL;

echo PHP_EOL . "=== TESTIMONIALS ===" . PHP_EOL;
$r = $db->query("DESCRIBE testimonials")->fetchAll(PDO::FETCH_COLUMN);
echo "testimonials cols: " . implode(', ', $r) . PHP_EOL;
$cols = implode(',', array_filter($r, fn($c) => !in_array($c, ['id','tenant_id','created_at','updated_at'])));
$r = $db->query("SELECT $cols FROM testimonials LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
foreach ($r as $row) echo "  " . json_encode($row) . PHP_EOL;
foreach ($r as $row) echo "  {$row['customer_name']} ({$row['rating']}*) " . $row['review'] . PHP_EOL;

echo PHP_EOL . "=== BLOG ===" . PHP_EOL;
$r = $db->query("SELECT id, title, featured_image, status FROM blog_posts ORDER BY id DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
foreach ($r as $row) echo "  [{$row['status']}] " . substr($row['title'], 0, 45) . " img=" . ($row['featured_image'] ?: 'null') . PHP_EOL;

echo PHP_EOL . "=== SITE CONTENT ===" . PHP_EOL;
$r = $db->query("SELECT content_key, LEFT(content_value, 100) as val FROM site_content LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
foreach ($r as $row) echo "  " . str_pad($row['content_key'], 35) . strip_tags($row['val']) . PHP_EOL;

echo PHP_EOL . "=== GALLERY ===" . PHP_EOL;
$r = $db->query("DESCRIBE gallery")->fetchAll(PDO::FETCH_COLUMN);
$gcols = implode(',', array_filter($r, fn($c) => !in_array($c, ['id','tenant_id','created_at','updated_at'])));
$r = $db->query("SELECT $gcols FROM gallery LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
foreach ($r as $row) echo "  " . json_encode($row) . PHP_EOL;

echo PHP_EOL . "=== CAREERS ===" . PHP_EOL;
$r = $db->query("DESCRIBE careers")->fetchAll(PDO::FETCH_COLUMN);
echo "careers cols: " . implode(', ', $r) . PHP_EOL;

echo PHP_EOL . "=== PROJECTS ===" . PHP_EOL;
$r = $db->query("DESCRIBE projects")->fetchAll(PDO::FETCH_COLUMN);
echo "projects cols: " . implode(', ', $r) . PHP_EOL;
