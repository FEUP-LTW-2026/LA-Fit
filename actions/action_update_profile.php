<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/users.php';
require_once __DIR__ . '/../database/csrf.php';

if (!verifyCsrfToken()) {
    http_response_code(403);
    header('Location: ../pages/login.php');
    exit;
}

function redirectUpdateProfile(string $status, string $code): void
{
    header('Location: ../pages/profile.php?' . $status . '=' . $code);
    exit;
}

function saveProfilePhoto(array $file, ?string $currentPhoto): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $currentPhoto;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        redirectUpdateProfile('erro', 'foto');
    }

    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        redirectUpdateProfile('erro', 'foto_tamanho');
    }

    $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
        redirectUpdateProfile('erro', 'foto_tipo');
    }

    $uploadDir = __DIR__ . '/../images/profiles';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $filename    = 'user-' . (int)$_SESSION['user_id'] . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
    $destination = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        redirectUpdateProfile('erro', 'foto');
    }

    return 'images/profiles/' . $filename;
}

$db          = getDatabaseConnection();
$userId      = (int)$_SESSION['user_id'];
$currentUser = getUserById($db, $userId);

if (!$currentUser) {
    session_destroy();
    header('Location: ../pages/login.php');
    exit;
}

$firstName = trim($_POST['first_name'] ?? '');
$lastName  = trim($_POST['last_name'] ?? '');
$email     = trim($_POST['email'] ?? '');

if ($firstName === '' || $lastName === '' || $email === '') {
    redirectUpdateProfile('erro', 'campos');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectUpdateProfile('erro', 'email');
}

if (emailExistsForOtherUser($db, $email, $userId)) {
    redirectUpdateProfile('erro', 'email_existe');
}

$photo = saveProfilePhoto($_FILES['photo'] ?? [], $currentUser['fotografia'] ?? null);

if (($_SESSION['role'] ?? '') === 'treinador') {
    $trainer = getTrainerByUsername($db, $_SESSION['username']);

    updateTrainerProfile($db, $userId, (int)$trainer['id'], [
        'first_name'      => $firstName,
        'last_name'       => $lastName,
        'email'           => $email,
        'photo'           => $photo,
        'bio'             => trim($_POST['bio'] ?? ''),
        'specializations' => implode(', ', $_POST['specializations'] ?? []),
        'certifications'  => trim($_POST['certifications'] ?? ''),
    ]);
} else {
    $username = trim($_POST['username'] ?? '');

    if ($username === '') {
        redirectUpdateProfile('erro', 'campos');
    }

    if (usernameExistsForOtherUser($db, $username, $userId)) {
        redirectUpdateProfile('erro', 'username');
    }

    $password             = $_POST['password'] ?? '';
    $passwordConfirmation = $_POST['password_confirmation'] ?? '';

    if ($password !== '' && $password !== $passwordConfirmation) {
        redirectUpdateProfile('erro', 'password');
    }

    updateUserProfile($db, $userId, [
        'first_name' => $firstName,
        'last_name'  => $lastName,
        'username'   => $username,
        'email'      => $email,
        'photo'      => $photo,
    ]);

    if ($password !== '') {
        updateUserPassword($db, $userId, $password);
    }

    $_SESSION['username'] = $username;
}

$_SESSION['name'] = $firstName;
redirectUpdateProfile('sucesso', 'perfil');
