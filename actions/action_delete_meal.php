<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'treinador') {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/users.php';
require_once __DIR__ . '/../database/nutrition.php';

$mealId = filter_input(INPUT_POST, 'meal_id', FILTER_VALIDATE_INT);
if (!$mealId) {
    header('Location: ../pages/perfil.php?erro=notfound#trainer-nutricao');
    exit;
}

$db      = getDatabaseConnection();
$trainer = getTrainerByUsername($db, $_SESSION['username']);

deleteMeal($db, $mealId, (int)$trainer['id']);
header('Location: ../pages/perfil.php?sucesso=refeicao_removida#trainer-nutricao');
exit;
