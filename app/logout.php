<?php
require __DIR__ . '/inc/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    logout();
}
header('Location: /login.php');
