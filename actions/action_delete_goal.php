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
    header('Location: ../pages/perfil.php');
    exit;
}

$goalId = filter_input(INPUT_POST, 'goal_id', FILTER_VALIDATE_INT);

if (!$goalId) {
    header('Location: ../pages/perfil.php#perfil-progresso');
    exit;
}

deleteGoal($db, $goalId, (int)$member['id']);
header('Location: ../pages/perfil.php?sucesso=objetivo_removido#perfil-progresso');
exit;
