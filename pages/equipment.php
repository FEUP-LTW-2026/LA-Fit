<?php
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'membro') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/equipment.php';

require_once __DIR__ . '/../templates/common.php';
require_once __DIR__ . '/../templates/equipment.php';

$db = getDatabaseConnection();

$allowedStates = ['disponivel', 'ocupado', 'manutencao'];
$filters = [
    'zona'   => trim($_GET['zona'] ?? ''),
    'estado' => in_array($_GET['estado'] ?? '', $allowedStates, true) ? $_GET['estado'] : '',
];

$equipmentByZone = getFilteredEquipmentByZone($db, $filters);
$summary = getEquipmentAvailabilitySummary($db);
$filterOptions = getEquipmentFilterOptions($db);

drawHeader('Equipamentos - LAFit', 'equipment');
drawEquipmentPage($equipmentByZone, $summary, $filters, $filterOptions);
drawFooter();
