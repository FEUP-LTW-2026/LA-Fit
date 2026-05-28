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

$goalId     = filter_input(INPUT_POST, 'goal_id', FILTER_VALIDATE_INT);
$valorAtual = (float)($_POST['valor_atual'] ?? -1);

if (!$goalId || $valorAtual < 0) {
    header('Location: ../pages/perfil.php?erro=objetivo_campos#perfil-progresso');
    exit;
}

updateGoalProgress($db, $goalId, (int)$member['id'], $valorAtual);
header('Location: ../pages/perfil.php?sucesso=objetivo_atualizado#perfil-progresso');
exit;
