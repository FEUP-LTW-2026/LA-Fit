<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'treinador') {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/users.php';
require_once __DIR__ . '/../database/nutrition.php';

$nome     = trim($_POST['nome'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');

if (!$nome) {
    header('Location: ../pages/perfil.php?erro=nutricao_campos#trainer-nutricao');
    exit;
}

$db      = getDatabaseConnection();
$trainer = getTrainerByUsername($db, $_SESSION['username']);

createNutritionPlan($db, (int)$trainer['id'], $nome, $descricao);
header('Location: ../pages/perfil.php?sucesso=plano_criado#trainer-nutricao');
exit;
