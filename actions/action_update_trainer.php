<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'treinador') {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/users.php';

function redirectTrainer(string $status, string $code): void
{
    header('Location: ../pages/perfil.php?' . $status . '=' . $code);
    exit;
}

function saveTrainerPhoto(array $file, ?string $currentPhoto): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $currentPhoto;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        redirectTrainer('erro', 'foto');
    }

    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        redirectTrainer('erro', 'foto_tamanho');
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions, true)) {
        redirectTrainer('erro', 'foto_tipo');
    }

    $uploadDir = __DIR__ . '/../images/profiles';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $filename = 'user-' . (int)$_SESSION['user_id'] . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
    $destination = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        redirectTrainer('erro', 'foto');
    }

    return 'images/profiles/' . $filename;
}

$db = getDatabaseConnection();
$userId = (int)$_SESSION['user_id'];
$currentUser = getUserById($db, $userId);

if (!$currentUser) {
    session_destroy();
    header('Location: ../pages/login.php');
    exit;
}

$trainer = getTrainerByUsername($db, $_SESSION['username']);

$firstName = trim($_POST['first_name'] ?? '');
$lastName  = trim($_POST['last_name'] ?? '');
$email     = trim($_POST['email'] ?? '');

if ($firstName === '' || $lastName === '' || $email === '') {
    redirectTrainer('erro', 'campos');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectTrainer('erro', 'email');
}

if (emailExistsForOtherUser($db, $email, $userId)) {
    redirectTrainer('erro', 'email_existe');
}

$photo = saveTrainerPhoto($_FILES['photo'] ?? [], $currentUser['fotografia'] ?? null);

updateTrainerProfile($db, $userId, (int)$trainer['id'], [
    'first_name'      => $firstName,
    'last_name'       => $lastName,
    'email'           => $email,
    'photo'           => $photo,
    'bio'             => trim($_POST['bio'] ?? ''),
    'specializations' => trim($_POST['specializations'] ?? ''),
    'certifications'  => trim($_POST['certifications'] ?? ''),
]);

$_SESSION['name'] = $firstName;

redirectTrainer('sucesso', 'perfil');
