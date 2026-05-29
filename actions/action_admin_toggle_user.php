<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'administrador') {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/users.php';
require_once __DIR__ . '/../database/csrf.php';


if (!verifyCsrfToken()) {
    http_response_code(403);
    header('Location: ../pages/login.php');
    exit;
}

$userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT) ?: 0;
$status = $_POST['status'] ?? '';

if ($userId <= 0 || !in_array($status, ['ativo', 'inativo'], true)) {
    header('Location: ../pages/profile.php?erro=estado');
    exit;
}

$db = getDatabaseConnection();

if (!setManagedUserStatus($db, $userId, $status)) {
    header('Location: ../pages/profile.php?erro=notfound');
    exit;
}

header('Location: ../pages/profile.php?sucesso=estado');
exit;
