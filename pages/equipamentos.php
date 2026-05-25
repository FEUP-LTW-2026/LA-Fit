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
$equipmentByZone = getEquipmentByZone($db);
$summary = getEquipmentAvailabilitySummary($db);

drawHeader('Equipamentos - LAFit', 'equipamentos');
drawEquipmentPage($equipmentByZone, $summary);
drawFooter();
