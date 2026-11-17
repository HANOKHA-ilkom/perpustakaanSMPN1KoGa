@echo off
title Setup Database Perpustakaan SMPN 1 Kota Gajah (Basis Data Lanjut)
cls
echo ======================================================================
echo  MENJALANKAN SETUP BASIS DATA LANJUT
echo  Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
echo ======================================================================
echo.

IF EXIST "C:\xampp\php\php.exe" (
    "C:\xampp\php\php.exe" "%~dp0setup_database.php"
) ELSE (
    php "%~dp0setup_database.php"
)

echo.
echo Tekan tombol apa saja untuk menutup jendela ini...
pause >nul

