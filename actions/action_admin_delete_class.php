<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'administrador') {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/classes.php';

function redirectAdminDeleteClass(string $type, string $code): void
{
    header('Location: ../pages/admin.php?' . $type . '=' . $code);
    exit;
}

$classId = filter_input(INPUT_POST, 'class_id', FILTER_VALIDATE_INT) ?: 0;

if ($classId <= 0) {
    redirectAdminDeleteClass('erro', 'aula_notfound');
}

$db = getDatabaseConnection();

if (!getAdminClassById($db, $classId)) {
    redirectAdminDeleteClass('erro', 'aula_notfound');
}

if (!removeClassFromCatalog($db, $classId)) {
    redirectAdminDeleteClass('erro', 'aula_notfound');
}

redirectAdminDeleteClass('sucesso', 'aula_removida');
