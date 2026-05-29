<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/reports.php';
require_once __DIR__ . '/../database/csrf.php';

if (!verifyCsrfToken()) {
    http_response_code(403);
    header('Location: ../pages/login.php');
    exit;
}

$db   = getDatabaseConnection();
$role = $_SESSION['role'] ?? '';

if ($role === 'administrador') {
    $reportId = filter_input(INPUT_POST, 'report_id', FILTER_VALIDATE_INT);
    $estado   = trim($_POST['estado'] ?? '');
    $resposta = trim($_POST['resposta'] ?? '');

    if (!$reportId) {
        header('Location: ../pages/report.php?erro=reporte_notfound');
        exit;
    }

    $allowedEstados = ['pendente', 'em_analise', 'resolvido'];
    if (!in_array($estado, $allowedEstados, true)) {
        header('Location: ../pages/report.php?erro=reporte_opcao');
        exit;
    }

    respondToReport($db, $reportId, $estado, $resposta);
    header('Location: ../pages/report.php?sucesso=reporte_atualizado');
    exit;
}

if ($role === 'membro') {
    require_once __DIR__ . '/../database/users.php';
    $user   = getUserByUsername($db, $_SESSION['username']);
    $userId = (int)($user['id'] ?? 0);

    $tipo      = trim($_POST['tipo'] ?? '');
    $assunto   = trim($_POST['assunto'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    if ($assunto === '' || $descricao === '') {
        header('Location: ../pages/report.php?erro=campos');
        exit;
    }

    $allowedTipos = ['equipamento', 'aula', 'outro'];
    if (!in_array($tipo, $allowedTipos, true)) {
        header('Location: ../pages/report.php?erro=tipo');
        exit;
    }

    createReport($db, $userId, $tipo, $assunto, $descricao);
    header('Location: ../pages/report.php?sucesso=enviado');
    exit;
}

header('Location: ../pages/login.php');
exit;
