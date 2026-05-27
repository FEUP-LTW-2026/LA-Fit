<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'administrador') {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/equipment.php';

function redirectAdminUpdateEquipment(string $type, string $code, int $equipmentId = 0): void
{
    $edit = $equipmentId > 0 ? '&edit_equipment=' . $equipmentId : '';
    header('Location: ../pages/perfil.php?' . $type . '=' . $code . $edit);
    exit;
}

$allowedStates = ['disponivel', 'ocupado', 'manutencao'];

$equipmentId = filter_input(INPUT_POST, 'equipment_id', FILTER_VALIDATE_INT) ?: 0;

if ($equipmentId <= 0) {
    redirectAdminUpdateEquipment('erro', 'equipamento_notfound');
}

$nome     = trim($_POST['nome'] ?? '');
$zona     = trim($_POST['zona'] ?? '');
$estado   = $_POST['estado'] ?? '';
$quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT) ?: 0;

if ($nome === '' || $zona === '') {
    redirectAdminUpdateEquipment('erro', 'equipamento_campos', $equipmentId);
}

if (!in_array($estado, $allowedStates, true)) {
    redirectAdminUpdateEquipment('erro', 'equipamento_opcao', $equipmentId);
}

if ($quantidade <= 0) {
    redirectAdminUpdateEquipment('erro', 'equipamento_numero', $equipmentId);
}

$db = getDatabaseConnection();

if (!getEquipmentById($db, $equipmentId)) {
    redirectAdminUpdateEquipment('erro', 'equipamento_notfound');
}

try {
    updateEquipment($db, $equipmentId, [
        'nome'      => $nome,
        'zona'      => $zona,
        'estado'    => $estado,
        'quantidade' => $quantidade,
    ]);
} catch (Exception $exception) {
    redirectAdminUpdateEquipment('erro', 'equipamento_opcao', $equipmentId);
}

redirectAdminUpdateEquipment('sucesso', 'equipamento_atualizado');
