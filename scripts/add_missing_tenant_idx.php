<?php
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: 3306;
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : (getenv('DB_PASS') ?: '');
$db   = getenv('DB_DATABASE') ?: 'apsdreamhome';
$pdo = new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass);
$tables=['chat_history','gamification_user_badges','listing_packages','listing_settings','mlm_rank_benefits','property_agents','property_boost_orders','property_messages','visitor_page_views','visit_checklists','whatsapp_click_log','visitor_sessions','visit_feedback'];
foreach($tables as $t){
  try{
    $cols=$pdo->query("SHOW COLUMNS FROM `$t` LIKE 'tenant_id'")->fetchAll();
    if(!$cols){ echo "NO tenant_id $t skip\n"; continue; }
    $idx=$pdo->query("SHOW INDEX FROM `$t` WHERE Column_name='tenant_id'")->fetchAll();
    if($idx){ echo "ALREADY $t\n"; continue; }
    $pdo->exec("ALTER TABLE `$t` ADD INDEX idx_tenant_id (tenant_id)");
    echo "OK $t\n";
  } catch(Throwable $e){
    echo "SKIP $t: ".$e->getMessage()."\n";
  }
}
