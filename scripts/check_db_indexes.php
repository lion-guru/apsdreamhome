<?php
$pdo=new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome','root',getenv('DB_PASS')?:'2jcePXuNaOfEyo6I5wJVkG');
$noPK=$pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema='apsdreamhome' AND table_type='BASE TABLE' AND table_name NOT IN (SELECT table_name FROM information_schema.statistics WHERE table_schema='apsdreamhome' AND index_name='PRIMARY' GROUP BY table_name)")->fetchAll(PDO::FETCH_COLUMN);
echo "No PK: ".count($noPK)."\n"; foreach(array_slice($noPK,0,10) as $t) echo " - $t\n";
$noTenantIdx=$pdo->query("SELECT table_name FROM information_schema.columns WHERE table_schema='apsdreamhome' AND column_name='tenant_id' AND table_name NOT IN (SELECT table_name FROM information_schema.statistics WHERE table_schema='apsdreamhome' AND column_name='tenant_id')")->fetchAll(PDO::FETCH_COLUMN);
echo "tenant_id without index: ".count($noTenantIdx)."\n"; foreach(array_slice($noTenantIdx,0,10) as $t) echo " - $t\n";
$fkCount=$pdo->query("SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE constraint_schema='apsdreamhome'")->fetchColumn();
echo "FK constraints: $fkCount\n";
