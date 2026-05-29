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

$planId    = filter_input(INPUT_POST, 'plan_id', FILTER_VALIDATE_INT);
$nome      = trim($_POST['nome'] ?? '');
$tipo      = $_POST['tipo'] ?? 'outro';
$calorias  = max(0, (int)filter_input(INPUT_POST, 'calorias', FILTER_VALIDATE_INT));
$proteinas = max(0.0, (float)($_POST['proteinas'] ?? 0));
$hidratos  = max(0.0, (float)($_POST['hidratos'] ?? 0));
$gorduras  = max(0.0, (float)($_POST['gorduras'] ?? 0));

$allowedTipos = ['pequeno_almoco', 'almoco', 'jantar', 'lanche', 'outro'];

if (!$planId || !$nome || !in_array($tipo, $allowedTipos, true)) {
    header('Location: ../pages/profile.php?erro=refeicao_campos#trainer-nutricao');
    exit;
}

$db      = getDatabaseConnection();
$trainer = getTrainerByUsername($db, $_SESSION['username']);

addMealToPlan($db, $planId, $nome, $tipo, $calorias, $proteinas, $hidratos, $gorduras, (int)$trainer['id']);
header('Location: ../pages/profile.php?sucesso=refeicao_adicionada#trainer-nutricao');
exit;
