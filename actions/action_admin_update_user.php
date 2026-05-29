<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'administrador') {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/users.php';

function redirectAdminUpdate(string $type, string $code, int $userId = 0): void
{
    $edit = $userId > 0 ? '&edit=' . $userId : '';
    header('Location: ../pages/profile.php?' . $type . '=' . $code . $edit);
    exit;
}

$userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT) ?: 0;
$role = $_POST['role'] ?? '';
$status = $_POST['status'] ?? '';
$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($userId <= 0) {
    redirectAdminUpdate('erro', 'notfound');
}

if (!in_array($status, ['ativo', 'inativo'], true)) {
    redirectAdminUpdate('erro', 'estado', $userId);
}

if ($firstName === '' || $lastName === '' || $username === '' || $email === '') {
    redirectAdminUpdate('erro', 'campos', $userId);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectAdminUpdate('erro', 'email', $userId);
}

$db = getDatabaseConnection();
$currentUser = getUserById($db, $userId);

if (!$currentUser || !in_array($currentUser['papel'], ['membro', 'treinador'], true)) {
    redirectAdminUpdate('erro', 'notfound');
}

if ($role !== $currentUser['papel']) {
    redirectAdminUpdate('erro', 'role', $userId);
}

if (usernameExistsForOtherUser($db, $username, $userId) || emailExistsForOtherUser($db, $email, $userId)) {
    redirectAdminUpdate('erro', 'existe', $userId);
}

try {
    updateManagedUser($db, $userId, [
        'role' => $role,
        'status' => $status,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'username' => $username,
        'email' => $email,
        'password' => $password,
        'plan_id' => ($_POST['plan_id'] ?? '') === '' ? null : (int)$_POST['plan_id'],
        'gym_id' => ($_POST['gym_id'] ?? '') === '' ? null : (int)$_POST['gym_id'],
        'bio' => trim($_POST['bio'] ?? ''),
        'specializations' => trim($_POST['specializations'] ?? ''),
        'certifications' => trim($_POST['certifications'] ?? ''),
    ]);
} catch (Exception $exception) {
    redirectAdminUpdate('erro', 'existe', $userId);
}

redirectAdminUpdate('sucesso', 'atualizado');
