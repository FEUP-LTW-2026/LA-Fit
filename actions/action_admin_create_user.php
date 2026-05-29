<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'administrador') {
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

function redirectAdminCreate(string $type, string $code): void
{
    header('Location: ../pages/profile.php?' . $type . '=' . $code);
    exit;
}

$role = $_POST['role'] ?? '';
$status = $_POST['status'] ?? '';
$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!in_array($role, ['membro', 'treinador'], true)) {
    redirectAdminCreate('erro', 'role');
}

if (!in_array($status, ['ativo', 'inativo'], true)) {
    redirectAdminCreate('erro', 'estado');
}

if ($firstName === '' || $lastName === '' || $username === '' || $email === '' || $password === '') {
    redirectAdminCreate('erro', 'campos');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectAdminCreate('erro', 'email');
}

if ($role === 'membro' && ($_POST['plan_id'] ?? '') === '') {
    redirectAdminCreate('erro', 'plano');
}

$db = getDatabaseConnection();

try {
    createManagedUser($db, [
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
    redirectAdminCreate('erro', 'existe');
}

redirectAdminCreate('sucesso', 'criado');
