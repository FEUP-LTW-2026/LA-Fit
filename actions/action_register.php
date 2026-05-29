<?php
session_start();

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/users.php';

$requiredFields = ['first_name', 'last_name', 'username', 'password', 'email', 'plan_id', 'gym_id'];

foreach ($requiredFields as $field) {
    if (trim($_POST[$field] ?? '') === '') {
        header('Location: ../pages/enrollment.php?erro=campos');
        exit;
    }
}

if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    header('Location: ../pages/enrollment.php?erro=email');
    exit;
}

if (!isset($_POST['terms'])) {
    header('Location: ../pages/enrollment.php?erro=termos');
    exit;
}

$db = getDatabaseConnection();

try {
    createMemberUser($db, [
        'first_name' => trim($_POST['first_name']),
        'last_name' => trim($_POST['last_name']),
        'username' => trim($_POST['username']),
        'password' => $_POST['password'],
        'email' => trim($_POST['email']),
        'birth_date' => trim($_POST['birth_date'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'postal_code' => trim($_POST['postal_code'] ?? ''),
        'plan_id' => (int)$_POST['plan_id'],
        'gym_id' => (int)$_POST['gym_id'],
    ]);
} catch (Exception $exception) {
    header('Location: ../pages/enrollment.php?erro=existe');
    exit;
}

$user = getUserByUsername($db, trim($_POST['username']));
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['nome_utilizador'];
$_SESSION['role'] = $user['papel'];
$_SESSION['name'] = $user['nome'];

header('Location: ../pages/profile.php');
exit;
