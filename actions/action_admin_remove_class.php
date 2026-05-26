<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'administrador') {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/classes.php';

$classId = filter_input(INPUT_POST, 'class_id', FILTER_VALIDATE_INT) ?: 0;

if ($classId <= 0) {
    header('Location: ../pages/admin.php?erro=aula_notfound');
    exit;
}

$db = getDatabaseConnection();

if (!removeClassFromCatalog($db, $classId)) {
    header('Location: ../pages/admin.php?erro=aula_notfound');
    exit;
}

header('Location: ../pages/admin.php?sucesso=aula_removida');
exit;
