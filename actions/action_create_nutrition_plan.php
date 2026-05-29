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

$nome     = trim($_POST['nome'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');

if (!$nome) {
    header('Location: ../pages/profile.php?erro=nutricao_campos#trainer-nutricao');
    exit;
}

$db      = getDatabaseConnection();
$trainer = getTrainerByUsername($db, $_SESSION['username']);

createNutritionPlan($db, (int)$trainer['id'], $nome, $descricao);
header('Location: ../pages/profile.php?sucesso=plano_criado#trainer-nutricao');
exit;
