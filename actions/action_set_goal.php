<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/users.php';
require_once __DIR__ . '/../database/progress.php';

function redirectGoal(string $status, string $code): void
{
    header('Location: ../pages/perfil.php?' . $status . '=' . $code);
    exit;
}

$db = getDatabaseConnection();
$userId = (int)$_SESSION['user_id'];
$currentUser = getUserById($db, $userId);

if (!$currentUser) {
    session_destroy();
    header('Location: ../pages/login.php');
    exit;
}

$member = getMemberByUsername($db, $currentUser['nome_utilizador']);
if (!$member) {
    redirectGoal('erro', 'not_member');
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$type = trim($_POST['type'] ?? 'treinos');
$target = filter_input(INPUT_POST, 'target', FILTER_VALIDATE_INT);
$deadline = trim($_POST['deadline'] ?? '');
$unit = trim($_POST['unit'] ?? '');
$allowedTypes = ['treinos', 'minutos', 'calorias'];

if ($title === '' || $type === '' || $target === false || $target <= 0 || $unit === '' || !in_array($type, $allowedTypes, true)) {
    redirectGoal('erro', 'goal_campos');
}

if ($deadline !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $deadline)) {
    redirectGoal('erro', 'goal_data');
}

if (!createGoal($db, (int)$member['membro_id'], [
    'title' => $title,
    'description' => $description,
    'type' => $type,
    'target' => $target,
    'unit' => $unit,
    'deadline' => $deadline !== '' ? $deadline : null,
])) {
    redirectGoal('erro', 'goal_gravar');
}

redirectGoal('sucesso', 'goal');
