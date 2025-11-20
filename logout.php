<?php
session_start();

// Hapus semua variabel sesi yang terdaftar
session_unset(); 

// Hancurkan sesi
session_destroy();

// Redirect ke halaman login. Pastikan nama file ini benar (misal: login.php)
header("Location: login.php");
exit;
?>