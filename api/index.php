<?php
// Vercel Serverless Function Bridge
ini_set('display_errors', '1');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

// Pastikan session path aman di serverless (/tmp)
if (function_exists('session_save_path')) {
    @session_save_path('/tmp');
}

chdir(dirname(__DIR__));

try {
    require __DIR__ . '/../index.php';
} catch (Throwable $e) {
    http_response_code(500);
    echo "<div style='font-family:system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif; max-width:850px; margin:40px auto; padding:24px; border:1px solid #f5c2c7; background:#fff8f8; color:#842029; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.08);'>";
    echo "<h3 style='margin-top:0; color:#dc3545;'>⚠️ Kendala Eksekusi Serverless PHP (Vercel)</h3>";
    echo "<p><strong>Pesan Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Berkas:</strong> <code>" . htmlspecialchars($e->getFile()) . "</code> (Baris: " . $e->getLine() . ")</p>";
    if (class_exists('Database')) {
        $dbErr = Database::getError();
        if (!empty($dbErr)) {
            echo "<div style='background:#ffebe9; padding:12px; border-radius:6px; margin:15px 0; border-left:4px solid #d9383a;'>";
            echo "<strong>Status Koneksi Database:</strong> " . htmlspecialchars($dbErr);
            echo "</div>";
        }
    }
    echo "<details style='margin-top:15px;'><summary style='cursor:pointer; font-weight:600;'>Lihat Stack Trace Lengkap</summary>";
    echo "<pre style='background:#f1f3f5; color:#212529; padding:12px; border-radius:6px; overflow-x:auto; font-size:12px; margin-top:8px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</details>";
    echo "</div>";
}
