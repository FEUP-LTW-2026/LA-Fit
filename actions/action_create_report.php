<?php
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'membro') {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/users.php';
require_once __DIR__ . '/../database/reports.php';
require_once __DIR__ . '/../database/csrf.php';


if (!verifyCsrfToken()) {
    http_response_code(403);
    header('Location: ../pages/login.php');
    exit;
}

$db = getDatabaseConnection();
$user = getUserByUsername($db, $_SESSION['username']);
$userId = (int)($user['id'] ?? 0);

function redirectReport(string $param, string $value): never
{
    header("Location: ../pages/report.php?$param=$value");
    exit;
}

$tipo     = trim($_POST['tipo'] ?? '');
$assunto  = trim($_POST['assunto'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');

if ($assunto === '' || $descricao === '') {
    redirectReport('erro', 'campos');
}

$allowedTipos = ['equipamento', 'aula', 'outro'];
if (!in_array($tipo, $allowedTipos, true)) {
    redirectReport('erro', 'tipo');
}

createReport($db, $userId, $tipo, $assunto, $descricao);

redirectReport('sucesso', 'enviado');
