<?php
session_start();

$role = $_SESSION['role'] ?? '';

if (!isset($_SESSION['user_id']) || !in_array($role, ['administrador', 'treinador', 'membro'], true)) {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/csrf.php';

if (!verifyCsrfToken()) {
    http_response_code(403);
    header('Location: ../pages/login.php');
    exit;
}

if ($role === 'membro') {
    require_once __DIR__ . '/../database/enrollments.php';
    require_once __DIR__ . '/../database/reviews.php';

    $classId = (int)($_POST['class_id'] ?? 0);
    $rating  = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($classId <= 0 || $rating < 1 || $rating > 10) {
        header('Location: ../pages/review.php?erro=1');
        exit;
    }

    $db       = getDatabaseConnection();
    $memberId = getMemberIdForUsername($db, $_SESSION['username']);

    if (!$memberId || !saveClassReview($db, $memberId, $classId, $rating, $comment)) {
        header('Location: ../pages/review.php?erro=1');
        exit;
    }

    header('Location: ../pages/review.php?sucesso=1&class_id=' . $classId);
    exit;
}

require_once __DIR__ . '/../database/classes.php';

$classId = filter_input(INPUT_POST, 'class_id', FILTER_VALIDATE_INT) ?: 0;
$action  = $_POST['_action'] ?? 'save';

function redirectClass(string $type, string $code, int $classId = 0): void
{
    $edit = $classId > 0 ? '&edit_class=' . $classId : '';
    header('Location: ../pages/profile.php?' . $type . '=' . $code . $edit);
    exit;
}

if ($action === 'delete') {
    if ($role !== 'administrador') {
        header('Location: ../pages/login.php');
        exit;
    }
    if ($classId <= 0) redirectClass('erro', 'aula_notfound');
    $db = getDatabaseConnection();
    removeClassFromCatalog($db, $classId);
    redirectClass('sucesso', 'aula_removida');
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
    redirectClass('erro', 'aula_campos', $classId);
}
if (!in_array($day, $days, true) || !in_array($status, $statuses, true)) {
    redirectClass('erro', 'aula_opcao', $classId);
}
if ($start >= $end) {
    redirectClass('erro', 'aula_horario', $classId);
}

$db = getDatabaseConnection();

if ($role === 'treinador') {
    require_once __DIR__ . '/../database/users.php';
    $trainer = getTrainerByUsername($db, $_SESSION['username']);

    if ($classId <= 0 || !getTrainerClassById($db, $classId, (int)$trainer['id'])) {
        redirectClass('erro', 'aula_notfound');
    }

    updateTrainerClass($db, $classId, (int)$trainer['id'], compact(
        'name', 'type', 'description', 'day', 'start', 'end', 'room', 'status'
    ));

    header('Location: ../pages/profile.php?sucesso=aula_atualizada#trainer-aulas');
    exit;
}

$isEditing = $classId > 0;
$trainerId = filter_input(INPUT_POST, 'trainer_id', FILTER_VALIDATE_INT) ?: 0;
$gymId     = filter_input(INPUT_POST, 'gym_id', FILTER_VALIDATE_INT) ?: 0;
$capacity  = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT) ?: 0;

if ($trainerId <= 0 || $gymId <= 0) redirectClass('erro', 'campos', $classId);
if ($capacity <= 0) redirectClass('erro', 'aula_numero', $classId);

$data = compact('name', 'type', 'description', 'day', 'start', 'end', 'room', 'status') +
        ['trainer_id' => $trainerId, 'gym_id' => $gymId, 'capacity' => $capacity];

try {
    if ($isEditing) {
        if (!getAdminClassById($db, $classId)) redirectClass('erro', 'aula_notfound');
        updateClass($db, $classId, $data);
        redirectClass('sucesso', 'aula_atualizada');
    } else {
        createClass($db, $data);
        redirectClass('sucesso', 'aula_criada');
    }
} catch (Exception) {
    redirectClass('erro', 'aula_opcao', $classId);
}
