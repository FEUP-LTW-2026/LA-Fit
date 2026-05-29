<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'treinador') {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/users.php';
require_once __DIR__ . '/../database/nutrition.php';
require_once __DIR__ . '/../database/csrf.php';


if (!verifyCsrfToken()) {
    http_response_code(403);
    header('Location: ../pages/login.php');
    exit;
}

$assignId = filter_input(INPUT_POST, 'assign_id', FILTER_VALIDATE_INT);
if (!$assignId) {
    header('Location: ../pages/profile.php?erro=notfound#trainer-nutricao');
    exit;
}

$db      = getDatabaseConnection();
$trainer = getTrainerByUsername($db, $_SESSION['username']);

$ok = unassignPlanFromMember($db, $assignId, (int)$trainer['id']);

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => $ok]);
    exit;
}
header('Location: ../pages/profile.php?sucesso=plano_removido_membro#trainer-nutricao');
exit;
