<?php
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'membro') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/equipment.php';

$db = getDatabaseConnection();

$allowedStates = ['disponivel', 'ocupado', 'manutencao'];
$filters = [
    'nome'   => trim($_GET['nome'] ?? ''),
    'zona'   => trim($_GET['zona'] ?? ''),
    'estado' => in_array($_GET['estado'] ?? '', $allowedStates, true) ? $_GET['estado'] : '',
];

$equipmentByZone = getFilteredEquipmentByZone($db, $filters);
$summary = getEquipmentAvailabilitySummary($db);

header('Content-Type: application/json');
echo json_encode([
    'equipmentByZone' => $equipmentByZone,
    'summary'         => $summary,
]);
