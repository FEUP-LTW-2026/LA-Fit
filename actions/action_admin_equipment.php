<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'administrador') {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/equipment.php';
require_once __DIR__ . '/../database/csrf.php';

if (!verifyCsrfToken()) {
    http_response_code(403);
    header('Location: ../pages/login.php');
    exit;
}

function redirectAdminEquipment(string $type, string $code, int $equipmentId = 0): void
{
    $edit = $equipmentId > 0 ? '&edit_equipment=' . $equipmentId : '';
    header('Location: ../pages/profile.php?' . $type . '=' . $code . $edit);
    exit;
}

$action      = $_POST['_action'] ?? 'save';
$equipmentId = filter_input(INPUT_POST, 'equipment_id', FILTER_VALIDATE_INT) ?: 0;

if ($action === 'delete') {
    if ($equipmentId <= 0) redirectAdminEquipment('erro', 'equipamento_notfound');
    $db = getDatabaseConnection();
    deleteEquipment($db, $equipmentId);
    redirectAdminEquipment('sucesso', 'equipamento_removido');
}

$isEditing     = $equipmentId > 0;
$allowedStates = ['disponivel', 'ocupado', 'manutencao'];

$nome       = trim($_POST['nome'] ?? '');
$zona       = trim($_POST['zona'] ?? '');
$estado     = $_POST['estado'] ?? '';
$quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT) ?: 0;

if ($nome === '' || $zona === '') redirectAdminEquipment('erro', 'equipamento_campos', $equipmentId);
if (!in_array($estado, $allowedStates, true)) redirectAdminEquipment('erro', 'equipamento_opcao', $equipmentId);
if ($quantidade <= 0) redirectAdminEquipment('erro', 'equipamento_numero', $equipmentId);

$data = compact('nome', 'zona', 'estado', 'quantidade');
$db   = getDatabaseConnection();

try {
    if ($isEditing) {
        if (!getEquipmentById($db, $equipmentId)) redirectAdminEquipment('erro', 'equipamento_notfound');
        updateEquipment($db, $equipmentId, $data);
        redirectAdminEquipment('sucesso', 'equipamento_atualizado');
    } else {
        createEquipment($db, $data);
        redirectAdminEquipment('sucesso', 'equipamento_criado');
    }
} catch (Exception) {
    redirectAdminEquipment('erro', 'equipamento_opcao', $equipmentId);
}
