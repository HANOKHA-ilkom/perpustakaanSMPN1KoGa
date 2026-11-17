<?php
require_once __DIR__ . '/core/Database.php';
$db = Database::getConnection();
echo "--- Kategori ---\n";
foreach ($db->query("SELECT * FROM kategori")->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo $r['id_kategori'] . ': ' . $r['nama_kategori'] . "\n";
}
echo "--- Penerbit ---\n";
foreach ($db->query("SELECT * FROM penerbit")->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo $r['id_penerbit'] . ': ' . $r['nama_penerbit'] . "\n";
}

