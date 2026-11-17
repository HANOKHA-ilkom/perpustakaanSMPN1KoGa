<?php
// Vercel Serverless Function Bridge
// Mengatur working directory ke root proyek agar pemanggilan berkas relatif berjalan normal
chdir(dirname(__DIR__));
require __DIR__ . '/../index.php';
