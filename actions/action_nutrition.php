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

$db      = getDatabaseConnection();
$trainer = getTrainerByUsername($db, $_SESSION['username']);
$action  = $_POST['_action'] ?? 'create_plan';

function redirectNutrition(string $param, string $value): void
{
    header('Location: ../pages/profile.php?' . $param . '=' . $value . '#trainer-nutricao');
    exit;
}

switch ($action) {

    case 'delete_plan':
        $planId = filter_input(INPUT_POST, 'plan_id', FILTER_VALIDATE_INT);
        if (!$planId) redirectNutrition('erro', 'notfound');
        deleteNutritionPlan($db, $planId, (int)$trainer['id']);
        redirectNutrition('sucesso', 'plano_removido');

    case 'add_meal':
        $planId    = filter_input(INPUT_POST, 'plan_id', FILTER_VALIDATE_INT);
        $nome      = trim($_POST['nome'] ?? '');
        $tipo      = $_POST['tipo'] ?? 'outro';
        $calorias  = max(0, (int)filter_input(INPUT_POST, 'calorias', FILTER_VALIDATE_INT));
        $proteinas = max(0.0, (float)($_POST['proteinas'] ?? 0));
        $hidratos  = max(0.0, (float)($_POST['hidratos'] ?? 0));
        $gorduras  = max(0.0, (float)($_POST['gorduras'] ?? 0));
        $allowed   = ['pequeno_almoco', 'almoco', 'jantar', 'lanche', 'outro'];
        if (!$planId || !$nome || !in_array($tipo, $allowed, true)) {
            redirectNutrition('erro', 'refeicao_campos');
        }
        addMealToPlan($db, $planId, $nome, $tipo, $calorias, $proteinas, $hidratos, $gorduras, (int)$trainer['id']);
        redirectNutrition('sucesso', 'refeicao_adicionada');

    case 'delete_meal':
        $mealId = filter_input(INPUT_POST, 'meal_id', FILTER_VALIDATE_INT);
        if (!$mealId) redirectNutrition('erro', 'notfound');
        $ok = deleteMeal($db, $mealId, (int)$trainer['id']);
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => $ok]);
            exit;
        }
        redirectNutrition('sucesso', 'refeicao_removida');

    case 'assign':
        $planId   = filter_input(INPUT_POST, 'plan_id', FILTER_VALIDATE_INT);
        $membroId = filter_input(INPUT_POST, 'membro_id', FILTER_VALIDATE_INT);
        if (!$planId || !$membroId) redirectNutrition('erro', 'nutricao_campos');
        assignPlanToMember($db, $planId, $membroId, (int)$trainer['id']);
        redirectNutrition('sucesso', 'plano_atribuido');

    case 'unassign':
        $assignId = filter_input(INPUT_POST, 'assign_id', FILTER_VALIDATE_INT);
        if (!$assignId) redirectNutrition('erro', 'notfound');
        $ok = unassignPlanFromMember($db, $assignId, (int)$trainer['id']);
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => $ok]);
            exit;
        }
        redirectNutrition('sucesso', 'plano_removido_membro');

    default: 
        $nome      = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        if (!$nome) redirectNutrition('erro', 'nutricao_campos');
        createNutritionPlan($db, (int)$trainer['id'], $nome, $descricao);
        redirectNutrition('sucesso', 'plano_criado');
}
