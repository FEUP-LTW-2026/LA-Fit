<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['role'] ?? 'membro';

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/users.php';
require_once __DIR__ . '/../database/reports.php';
require_once __DIR__ . '/../templates/common.php';
require_once __DIR__ . '/../templates/report.php';

$db = getDatabaseConnection();
$user = getUserByUsername($db, $_SESSION['username']);

if (!$user) {
    header('Location: login.php');
    exit;
}

if ($role === 'administrador') {
    $reports = getAllReports($db);

    $messages = [
        'success' => match ($_GET['sucesso'] ?? '') {
            'reporte_atualizado' => 'Reporte atualizado com sucesso.',
            default              => null,
        },
        'error' => match ($_GET['erro'] ?? '') {
            'reporte_notfound' => 'Reporte não encontrado.',
            'reporte_opcao'    => 'Escolhe um estado válido para o reporte.',
            default            => null,
        },
    ];

    drawHeader('Reportes - LAFit', 'report');
    drawAdminReportPage($reports, $messages);
    drawFooter();

} else {
    $userId = (int)($user['id'] ?? 0);

    if (!$userId) {
        header('Location: perfil.php');
        exit;
    }

    $reports = getMemberReports($db, $userId);

    $messages = [
        'success' => match ($_GET['sucesso'] ?? '') {
            'enviado' => 'Reporte enviado com sucesso. A administração irá analisar e responder em breve.',
            default   => null,
        },
        'error' => match ($_GET['erro'] ?? '') {
            'campos' => 'Preenche todos os campos obrigatórios.',
            'tipo'   => 'Escolhe um tipo de problema válido.',
            default  => null,
        },
    ];

    drawHeader('Reportar - LAFit', 'report');
    drawReportPage($reports, $messages);
    drawFooter();
}
