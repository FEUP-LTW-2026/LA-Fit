<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/users.php';
require_once __DIR__ . '/../database/progress.php';

function redirectWorkout(string $status, string $code): void
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
    redirectWorkout('erro', 'not_member');
}

$date = trim($_POST['date'] ?? '');
$type = trim($_POST['type'] ?? '');
$duration = filter_input(INPUT_POST, 'duration', FILTER_VALIDATE_INT);
$calories = filter_input(INPUT_POST, 'calories', FILTER_VALIDATE_INT);
$notes = trim($_POST['notes'] ?? '');

if ($date === '' || $type === '' || $duration === false || $duration <= 0 || $calories === false || $calories < 0) {
    redirectWorkout('erro', 'workout_campos');
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    redirectWorkout('erro', 'workout_data');
}

if (!logWorkout($db, (int)$member['membro_id'], [
    'date' => $date,
    'type' => $type,
    'duration' => $duration,
    'calories' => $calories,
    'notes' => $notes,
])) {
    redirectWorkout('erro', 'workout_gravar');
}

redirectWorkout('sucesso', 'workout');
