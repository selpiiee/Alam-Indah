<?php require __DIR__ . '/includes/bootstrap.php';
$_SESSION = [];
session_destroy();
session_start();
flash('Anda telah keluar.');
redirect('login.php');
