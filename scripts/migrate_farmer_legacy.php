<?php
require_once __DIR__ . '/../config/bootstrap.php';
$pdo = App\Core\Database\Database::getInstance()->getConnection();
$rows = $pdo->query("SELECT * FROM farmer_land_management")->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $r){
  $phone = $r['farmer_mobile'];
  $tid = $r['tenant_id'] ?? 1;
  // find farmer by phone
  $st=$pdo->prepare("SELECT id FROM farmers WHERE phone=? ORDER BY id ASC LIMIT 1");
  $st->execute([$phone]);
  $fid=$st->fetchColumn();
  if(!$fid){
    // create farmer (farmers has district/state, no city col)
    $pdo->prepare("INSERT INTO farmers (tenant_id, name, phone, district, state, status) VALUES (?,?,?,?,?, 'active')")
      ->execute([$tid, $r['farmer_name'], $phone, $r['district'], $r['state'] ?? 'Uttar Pradesh']);
    $fid=$pdo->lastInsertId();
    echo "Created farmer $fid {$r['farmer_name']} $phone\n";
  } else {
    echo "Found farmer $fid for $phone ({$r['farmer_name']})\n";
  }
  // check holding exists for this farmer + area + site
  $khasra = $r['gata_number'] ?: ('LEGACY-'.$r['id']);
  $chk=$pdo->prepare("SELECT id FROM farmer_land_holdings WHERE farmer_id=? AND khasra_number=? LIMIT 1");
  $chk->execute([$fid,$khasra]);
  if($chk->fetchColumn()){
    echo "  Holding exists for $khasra\n"; continue;
  }
  $acqStatus = $r['agreement_status']==='completed' ? 'acquired' : ($r['agreement_status']==='active' ? 'under_negotiation' : 'not_acquired');
  $payStatus = $r['amount_pending']==0 && $r['agreement_status']==='completed' ? 'completed' : ($r['total_paid_amount']>0 ? 'partial' : 'pending');
  $pdo->prepare("INSERT INTO farmer_land_holdings (tenant_id, farmer_id, khasra_number, land_area, land_area_unit, district, village, tehsil, location, land_value, acquisition_status, payment_status, payment_received, remarks) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
    ->execute([$tid,$fid,$khasra,$r['land_area']??0,'sqft',$r['district'],$r['gram']?:$r['city'],$r['tehsil'],$r['site_name'],$r['total_land_price'],$acqStatus,$payStatus,$r['total_paid_amount']??0,"Migrated from farmer_land_management id {$r['id']}"]);
  echo "  Inserted holding $khasra area {$r['land_area']} for farmer $fid\n";
}
echo "Done.\n";
