<?php
session_start();

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/users.php';

$login = trim($_POST['login'] ?? '');
$password = $_POST['password'] ?? '';

if ($login === '' || $password === '') {
    header('Location: ../pages/login.php?erro=1');
    exit;
}

$db = getDatabaseConnection();
$user = getUserByLoginAndPassword($db, $login, $password);

if (!$user) {
    header('Location: ../pages/login.php?erro=1');
    exit;
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['nome_utilizador'];
$_SESSION['role'] = $user['papel'];
$_SESSION['name'] = $user['nome'];

if ($user['papel'] === 'treinador') {
    header('Location: ../pages/profile.php');
} elseif ($user['papel'] === 'administrador') {
    header('Location: ../pages/profile.php');
} else {
    header('Location: ../pages/profile.php');
}
exit;
