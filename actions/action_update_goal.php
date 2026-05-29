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

$goalId     = filter_input(INPUT_POST, 'goal_id', FILTER_VALIDATE_INT);
$valorAtual = (float)($_POST['valor_atual'] ?? -1);

if (!$goalId || $valorAtual < 0) {
    header('Location: ../pages/profile.php?erro=objetivo_campos#perfil-progresso');
    exit;
}

updateGoalProgress($db, $goalId, (int)$member['id'], $valorAtual);

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if ($isAjax) {
    $stmt = $db->prepare('SELECT * FROM objetivos WHERE id = ? AND membro_id = ?');
    $stmt->execute([$goalId, (int)$member['id']]);
    $goal = $stmt->fetch();
    $pct  = $goal['valor_alvo'] > 0
        ? min(100, round($goal['valor_atual'] / $goal['valor_alvo'] * 100))
        : 0;
    header('Content-Type: application/json');
    echo json_encode([
        'success'     => true,
        'valor_atual' => (float)$goal['valor_atual'],
        'valor_alvo'  => (float)$goal['valor_alvo'],
        'pct'         => $pct,
        'concluido'   => (bool)$goal['concluido'],
        'unidade'     => $goal['unidade'],
    ]);
    exit;
}
header('Location: ../pages/profile.php?sucesso=objetivo_atualizado#perfil-progresso');
exit;
