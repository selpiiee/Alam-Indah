<?php require __DIR__ . '/includes/bootstrap.php';
redirect(user() ? 'overview.php' : 'login.php');
