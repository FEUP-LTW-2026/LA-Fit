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

$planId = filter_input(INPUT_POST, 'plan_id', FILTER_VALIDATE_INT);
if (!$planId) {
    header('Location: ../pages/profile.php?erro=notfound#trainer-nutricao');
    exit;
}

$db      = getDatabaseConnection();
$trainer = getTrainerByUsername($db, $_SESSION['username']);

deleteNutritionPlan($db, $planId, (int)$trainer['id']);
header('Location: ../pages/profile.php?sucesso=plano_removido#trainer-nutricao');
exit;
