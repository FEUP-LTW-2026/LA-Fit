<?php
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'administrador') {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/reports.php';

$db = getDatabaseConnection();

function redirectAdmin(string $param, string $value): never
{
    header("Location: ../pages/perfil.php?$param=$value");
    exit;
}

$reportId = filter_input(INPUT_POST, 'report_id', FILTER_VALIDATE_INT);
$estado   = trim($_POST['estado'] ?? '');
$resposta = trim($_POST['resposta'] ?? '');

if (!$reportId) {
    redirectAdmin('erro', 'reporte_notfound');
}

$allowedEstados = ['pendente', 'em_analise', 'resolvido'];
if (!in_array($estado, $allowedEstados, true)) {
    redirectAdmin('erro', 'reporte_opcao');
}

respondToReport($db, $reportId, $estado, $resposta);

redirectAdmin('sucesso', 'reporte_atualizado');
