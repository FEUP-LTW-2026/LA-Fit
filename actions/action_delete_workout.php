<?php
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'membro') {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/users.php';
require_once __DIR__ . '/../database/progress.php';
require_once __DIR__ . '/../database/csrf.php';


if (!verifyCsrfToken()) {
    http_response_code(403);
    header('Location: ../pages/login.php');
    exit;
}

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

$workoutId = filter_input(INPUT_POST, 'workout_id', FILTER_VALIDATE_INT);

if (!$workoutId) {
    header('Location: ../pages/profile.php#perfil-progresso');
    exit;
}

$ok = deleteWorkout($db, $workoutId, (int)$member['id']);

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => $ok]);
    exit;
}
header('Location: ../pages/profile.php?sucesso=treino_removido#perfil-progresso');
exit;
