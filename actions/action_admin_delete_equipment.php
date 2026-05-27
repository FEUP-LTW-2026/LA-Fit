<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'administrador') {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/equipment.php';

function redirectAdminDeleteEquipment(string $type, string $code): void
{
    header('Location: ../pages/perfil.php?' . $type . '=' . $code);
    exit;
}

$equipmentId = filter_input(INPUT_POST, 'equipment_id', FILTER_VALIDATE_INT) ?: 0;

if ($equipmentId <= 0) {
    redirectAdminDeleteEquipment('erro', 'equipamento_notfound');
}

$db = getDatabaseConnection();

if (!getEquipmentById($db, $equipmentId)) {
    redirectAdminDeleteEquipment('erro', 'equipamento_notfound');
}

if (!deleteEquipment($db, $equipmentId)) {
    redirectAdminDeleteEquipment('erro', 'equipamento_notfound');
}

redirectAdminDeleteEquipment('sucesso', 'equipamento_removido');
