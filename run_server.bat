@echo off
title Server Perpustakaan SMP Negeri 1 Kota Gajah
echo ===============================================================
echo   Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
echo   Re-create Skripsi Sidik Prastio Utomo (NPM: 19430028)
echo ===============================================================
echo.
echo Menjalankan web server lokal pada port 8080...
echo Buka browser pada alamat: http://localhost:8080
echo Tekan Ctrl+C untuk menghentikan server.
echo.

if exist "C:\xampp\php\php.exe" (
    "C:\xampp\php\php.exe" -S localhost:8080
) else (
    php -S localhost:8080
)

pause

