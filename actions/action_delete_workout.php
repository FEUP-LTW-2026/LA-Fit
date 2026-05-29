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
    header('Location: ../pages/profile.php');
    exit;
}

$workoutId = filter_input(INPUT_POST, 'workout_id', FILTER_VALIDATE_INT);

if (!$workoutId) {
    header('Location: ../pages/profile.php#perfil-progresso');
    exit;
}

deleteWorkout($db, $workoutId, (int)$member['id']);
header('Location: ../pages/profile.php?sucesso=treino_removido#perfil-progresso');
exit;
