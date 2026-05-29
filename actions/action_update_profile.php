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

function redirectProfile(string $status, string $code): void
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
        redirectProfile('erro', 'foto');
    }

    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        redirectProfile('erro', 'foto_tamanho');
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions, true)) {
        redirectProfile('erro', 'foto_tipo');
    }

    $uploadDir = __DIR__ . '/../images/profiles';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $filename = 'user-' . (int)$_SESSION['user_id'] . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
    $destination = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        redirectProfile('erro', 'foto');
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

$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$passwordConfirmation = $_POST['password_confirmation'] ?? '';

if ($firstName === '' || $lastName === '' || $username === '' || $email === '') {
    redirectProfile('erro', 'campos');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectProfile('erro', 'email');
}

if (usernameExistsForOtherUser($db, $username, $userId)) {
    redirectProfile('erro', 'username');
}

if (emailExistsForOtherUser($db, $email, $userId)) {
    redirectProfile('erro', 'email_existe');
}

if ($password !== '' && $password !== $passwordConfirmation) {
    redirectProfile('erro', 'password');
}

$photo = saveProfilePhoto($_FILES['photo'] ?? [], $currentUser['fotografia'] ?? null);

updateUserProfile($db, $userId, [
    'first_name' => $firstName,
    'last_name' => $lastName,
    'username' => $username,
    'email' => $email,
    'photo' => $photo,
]);

if ($password !== '') {
    updateUserPassword($db, $userId, $password);
}

$_SESSION['username'] = $username;
$_SESSION['name'] = $firstName;

redirectProfile('sucesso', 'perfil');
