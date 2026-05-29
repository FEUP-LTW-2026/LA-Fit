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

$db     = getDatabaseConnection();
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

$action = $_POST['_action'] ?? 'create';

if ($action === 'delete') {
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
}

if ($action === 'update') {
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
}

$descricao  = trim($_POST['descricao'] ?? '');
$valorAlvo  = (float)($_POST['valor_alvo'] ?? 0);
$unidade    = trim($_POST['unidade'] ?? '');
$dataLimite = trim($_POST['data_limite'] ?? '');

if (!$descricao || $valorAlvo <= 0) {
    header('Location: ../pages/profile.php?erro=objetivo_campos#perfil-progresso');
    exit;
}

createGoal($db, (int)$member['id'], $descricao, $valorAlvo, $unidade, $dataLimite ?: null);
header('Location: ../pages/profile.php?sucesso=objetivo_criado#perfil-progresso');
exit;
