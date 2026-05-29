<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'administrador') {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/classes.php';
require_once __DIR__ . '/../database/csrf.php';


if (!verifyCsrfToken()) {
    http_response_code(403);
    header('Location: ../pages/login.php');
    exit;
}

function redirectAdminUpdateClass(string $type, string $code, int $classId = 0): void
{
    $edit = $classId > 0 ? '&edit_class=' . $classId : '';
    header('Location: ../pages/profile.php?' . $type . '=' . $code . $edit);
    exit;
}

function readClassUpdateFormOrRedirect(int $classId): array
{
    $days = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
    $statuses = ['agendada', 'concluida', 'cancelada'];

    $name = trim($_POST['name'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $trainerId = filter_input(INPUT_POST, 'trainer_id', FILTER_VALIDATE_INT) ?: 0;
    $gymId = filter_input(INPUT_POST, 'gym_id', FILTER_VALIDATE_INT) ?: 0;
    $day = $_POST['day'] ?? '';
    $start = $_POST['start'] ?? '';
    $end = $_POST['end'] ?? '';
    $capacity = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT) ?: 0;
    $room = trim($_POST['room'] ?? '');
    $status = $_POST['status'] ?? '';

    if ($name === '' || $type === '' || $trainerId <= 0 || $gymId <= 0 || $start === '' || $end === '') {
        redirectAdminUpdateClass('erro', 'campos', $classId);
    }

    if (!in_array($day, $days, true) || !in_array($status, $statuses, true)) {
        redirectAdminUpdateClass('erro', 'aula_opcao', $classId);
    }

    if ($capacity <= 0) {
        redirectAdminUpdateClass('erro', 'aula_numero', $classId);
    }

    if ($start >= $end) {
        redirectAdminUpdateClass('erro', 'aula_horario', $classId);
    }

    return [
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
    ];
}

$classId = filter_input(INPUT_POST, 'class_id', FILTER_VALIDATE_INT) ?: 0;

if ($classId <= 0) {
    redirectAdminUpdateClass('erro', 'aula_notfound');
}

$db = getDatabaseConnection();

if (!getAdminClassById($db, $classId)) {
    redirectAdminUpdateClass('erro', 'aula_notfound');
}

try {
    updateClass($db, $classId, readClassUpdateFormOrRedirect($classId));
} catch (Exception $exception) {
    redirectAdminUpdateClass('erro', 'aula_opcao', $classId);
}

redirectAdminUpdateClass('sucesso', 'aula_atualizada');
