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

$descricao  = trim($_POST['descricao'] ?? '');
$valorAlvo  = (float)($_POST['valor_alvo'] ?? 0);
$unidade    = trim($_POST['unidade'] ?? '');
$dataLimite = trim($_POST['data_limite'] ?? '');

if (!$descricao || $valorAlvo <= 0) {
    header('Location: ../pages/perfil.php?erro=objetivo_campos#perfil-progresso');
    exit;
}

createGoal($db, (int)$member['id'], $descricao, $valorAlvo, $unidade, $dataLimite ?: null);
header('Location: ../pages/perfil.php?sucesso=objetivo_criado#perfil-progresso');
exit;
