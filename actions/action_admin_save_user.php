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

$userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT) ?: 0;
$action = $_POST['_action'] ?? 'save';

if ($action === 'toggle') {
    $status = $_POST['status'] ?? '';
    if ($userId <= 0 || !in_array($status, ['ativo', 'inativo'], true)) {
        header('Location: ../pages/profile.php?erro=estado');
        exit;
    }
    $db = getDatabaseConnection();
    if (!setManagedUserStatus($db, $userId, $status)) {
        header('Location: ../pages/profile.php?erro=notfound');
        exit;
    }
    header('Location: ../pages/profile.php?sucesso=estado');
    exit;
}

if ($action === 'elevate') {
    if ($userId <= 0) {
        header('Location: ../pages/profile.php?erro=notfound');
        exit;
    }
    $db = getDatabaseConnection();
    if (!elevateUserToAdmin($db, $userId)) {
        header('Location: ../pages/profile.php?erro=notfound&edit=' . $userId);
        exit;
    }
    header('Location: ../pages/profile.php?sucesso=atualizado');
    exit;
}

$isEditing = $userId > 0;

function redirectSaveUser(string $type, string $code, int $userId = 0): void
{
    $edit = $userId > 0 ? '&edit=' . $userId : '';
    header('Location: ../pages/profile.php?' . $type . '=' . $code . $edit);
    exit;
}

$status    = $_POST['status'] ?? '';
$firstName = trim($_POST['first_name'] ?? '');
$lastName  = trim($_POST['last_name'] ?? '');
$username  = trim($_POST['username'] ?? '');
$email     = trim($_POST['email'] ?? '');
$password  = $_POST['password'] ?? '';

if (!in_array($status, ['ativo', 'inativo'], true)) {
    redirectSaveUser('erro', 'estado', $userId);
}

if ($firstName === '' || $lastName === '' || $username === '' || $email === '') {
    redirectSaveUser('erro', 'campos', $userId);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectSaveUser('erro', 'email', $userId);
}

$db = getDatabaseConnection();

if ($isEditing) {
    $role = $_POST['role'] ?? '';

    $currentUser = getUserById($db, $userId);
    if (!$currentUser || !in_array($currentUser['papel'], ['membro', 'treinador'], true)) {
        redirectSaveUser('erro', 'notfound');
    }

    if ($role !== $currentUser['papel']) {
        redirectSaveUser('erro', 'role', $userId);
    }

    if (usernameExistsForOtherUser($db, $username, $userId) || emailExistsForOtherUser($db, $email, $userId)) {
        redirectSaveUser('erro', 'existe', $userId);
    }

    try {
        updateManagedUser($db, $userId, [
            'role'            => $role,
            'status'          => $status,
            'first_name'      => $firstName,
            'last_name'       => $lastName,
            'username'        => $username,
            'email'           => $email,
            'password'        => $password,
            'plan_id'         => ($_POST['plan_id'] ?? '') === '' ? null : (int)$_POST['plan_id'],
            'gym_id'          => ($_POST['gym_id'] ?? '') === '' ? null : (int)$_POST['gym_id'],
            'bio'             => trim($_POST['bio'] ?? ''),
            'specializations' => trim($_POST['specializations'] ?? ''),
            'certifications'  => trim($_POST['certifications'] ?? ''),
        ]);
    } catch (Exception) {
        redirectSaveUser('erro', 'existe', $userId);
    }

    redirectSaveUser('sucesso', 'atualizado');
} else {
    $role = $_POST['role'] ?? '';

    if (!in_array($role, ['membro', 'treinador'], true)) {
        redirectSaveUser('erro', 'role');
    }

    if ($password === '') {
        redirectSaveUser('erro', 'campos');
    }

    if ($role === 'membro' && ($_POST['plan_id'] ?? '') === '') {
        redirectSaveUser('erro', 'plano');
    }

    try {
        createManagedUser($db, [
            'role'            => $role,
            'status'          => $status,
            'first_name'      => $firstName,
            'last_name'       => $lastName,
            'username'        => $username,
            'email'           => $email,
            'password'        => $password,
            'plan_id'         => ($_POST['plan_id'] ?? '') === '' ? null : (int)$_POST['plan_id'],
            'gym_id'          => ($_POST['gym_id'] ?? '') === '' ? null : (int)$_POST['gym_id'],
            'bio'             => trim($_POST['bio'] ?? ''),
            'specializations' => trim($_POST['specializations'] ?? ''),
            'certifications'  => trim($_POST['certifications'] ?? ''),
        ]);
    } catch (Exception) {
        redirectSaveUser('erro', 'existe');
    }

    redirectSaveUser('sucesso', 'criado');
}
