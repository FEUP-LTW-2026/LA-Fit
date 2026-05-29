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
$user = getUserByUsername($db, $_SESSION['username']);
$member = getMemberByUsername($db, $_SESSION['username']);

if (!$user || !$member) {
    header('Location: ../pages/profile.php');
    exit;
}

$membroId = (int)$member['id'];

$tiposValidos = ['musculacao', 'cardio', 'funcional', 'yoga', 'outro'];
$data   = trim($_POST['data'] ?? '');
$tipo   = trim($_POST['tipo'] ?? '');
$duracao = (int)($_POST['duracao'] ?? 0);
$notas  = trim($_POST['notas'] ?? '');

if (!$data || !$tipo || $duracao <= 0) {
    header('Location: ../pages/profile.php?erro=treino_campos#perfil-progresso');
    exit;
}

if (!in_array($tipo, $tiposValidos, true)) {
    header('Location: ../pages/profile.php?erro=treino_tipo#perfil-progresso');
    exit;
}

logWorkout($db, $membroId, $data, $tipo, $duracao, $notas);
header('Location: ../pages/profile.php?sucesso=treino_registado#perfil-progresso');
exit;
