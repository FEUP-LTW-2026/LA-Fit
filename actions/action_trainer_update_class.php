<?php
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'treinador') {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/users.php';
require_once __DIR__ . '/../database/classes.php';
require_once __DIR__ . '/../database/csrf.php';

if (!verifyCsrfToken()) {
    http_response_code(403);
    header('Location: ../pages/login.php');
    exit;
}

$classId = filter_input(INPUT_POST, 'class_id', FILTER_VALIDATE_INT) ?: 0;

if ($classId <= 0) {
    header('Location: ../pages/profile.php?erro=aula_notfound');
    exit;
}

$days     = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
$statuses = ['agendada', 'concluida', 'cancelada'];

$name        = trim($_POST['name'] ?? '');
$type        = trim($_POST['type'] ?? '');
$description = trim($_POST['description'] ?? '');
$day         = $_POST['day'] ?? '';
$start       = $_POST['start'] ?? '';
$end         = $_POST['end'] ?? '';
$room        = trim($_POST['room'] ?? '');
$status      = $_POST['status'] ?? '';

if ($name === '' || $type === '' || $start === '' || $end === '') {
    header('Location: ../pages/profile.php?erro=aula_campos&edit_class=' . $classId);
    exit;
}

if (!in_array($day, $days, true) || !in_array($status, $statuses, true)) {
    header('Location: ../pages/profile.php?erro=aula_opcao&edit_class=' . $classId);
    exit;
}

if ($start >= $end) {
    header('Location: ../pages/profile.php?erro=aula_horario&edit_class=' . $classId);
    exit;
}

$db      = getDatabaseConnection();
$trainer = getTrainerByUsername($db, $_SESSION['username']);

if (!getTrainerClassById($db, $classId, (int)$trainer['id'])) {
    header('Location: ../pages/profile.php?erro=aula_notfound');
    exit;
}

updateTrainerClass($db, $classId, (int)$trainer['id'], [
    'name'        => $name,
    'type'        => $type,
    'description' => $description,
    'day'         => $day,
    'start'       => $start,
    'end'         => $end,
    'room'        => $room,
    'status'      => $status,
]);

header('Location: ../pages/profile.php?sucesso=aula_atualizada#trainer-aulas');
exit;
