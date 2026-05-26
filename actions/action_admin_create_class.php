<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'administrador') {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/classes.php';

function redirectAdminClassCreate(string $type, string $code): void
{
    header('Location: ../pages/admin.php?' . $type . '=' . $code);
    exit;
}

$allowedDays = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
$allowedStatuses = ['agendada', 'cancelada', 'concluida'];

$name = trim($_POST['name'] ?? '');
$type = trim($_POST['type'] ?? '');
$description = trim($_POST['description'] ?? '');
$trainerId = filter_input(INPUT_POST, 'trainer_id', FILTER_VALIDATE_INT) ?: 0;
$gymId = filter_input(INPUT_POST, 'gym_id', FILTER_VALIDATE_INT) ?: 0;
$day = $_POST['day'] ?? '';
$start = trim($_POST['start'] ?? '');
$end = trim($_POST['end'] ?? '');
$capacity = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT) ?: 0;
$room = trim($_POST['room'] ?? '');
$status = $_POST['status'] ?? 'agendada';

if ($name === '' || $type === '' || $trainerId <= 0 || $gymId <= 0 || !in_array($day, $allowedDays, true) || $start === '' || $end === '' || $capacity <= 0 || $room === '' || !in_array($status, $allowedStatuses, true)) {
    redirectAdminClassCreate('erro', 'aula_campos');
}

if ($end <= $start) {
    redirectAdminClassCreate('erro', 'aula_hora');
}

$db = getDatabaseConnection();

try {
    createClass($db, [
        'name' => $name,
        'type' => $type,
        'description' => $description,
        'trainer_id' => $trainerId,
        'gym_id' => $gymId,
        'day' => $day,
        'start' => $start,
        'end' => $end,
        'capacity' => $capacity,
        'room' => $room,
        'status' => $status,
    ]);
} catch (Exception $exception) {
    redirectAdminClassCreate('erro', 'aula_campos');
}

redirectAdminClassCreate('sucesso', 'aula_criada');
