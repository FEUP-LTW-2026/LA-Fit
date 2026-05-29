<?php
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'membro') {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/users.php';
require_once __DIR__ . '/../database/progress.php';

$db = getDatabaseConnection();
$member = getMemberByUsername($db, $_SESSION['username']);

if (!$member) {
    header('Location: ../pages/profile.php');
    exit;
}

$features = getMemberPlanFeatures($member['plano_nome'] ?? '');
if (!$features['progress']) {
    header('Location: ../pages/profile.php');
    exit;
}

$goalId = filter_input(INPUT_POST, 'goal_id', FILTER_VALIDATE_INT);

if (!$goalId) {
    header('Location: ../pages/profile.php#perfil-progresso');
    exit;
}

$ok = deleteGoal($db, $goalId, (int)$member['id']);

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => $ok]);
    exit;
}
header('Location: ../pages/profile.php?sucesso=objetivo_removido#perfil-progresso');
exit;
