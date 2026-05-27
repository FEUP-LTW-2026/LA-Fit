<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'administrador') {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/equipment.php';

function redirectAdminCreateEquipment(string $type, string $code): void
{
    header('Location: ../pages/perfil.php?' . $type . '=' . $code);
    exit;
}

$allowedStates = ['disponivel', 'ocupado', 'manutencao'];

$nome     = trim($_POST['nome'] ?? '');
$zona     = trim($_POST['zona'] ?? '');
$estado   = $_POST['estado'] ?? '';
$quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT) ?: 0;

if ($nome === '' || $zona === '') {
    redirectAdminCreateEquipment('erro', 'equipamento_campos');
}

if (!in_array($estado, $allowedStates, true)) {
    redirectAdminCreateEquipment('erro', 'equipamento_opcao');
}

if ($quantidade <= 0) {
    redirectAdminCreateEquipment('erro', 'equipamento_numero');
}

$db = getDatabaseConnection();

try {
    createEquipment($db, [
        'nome'      => $nome,
        'zona'      => $zona,
        'estado'    => $estado,
        'quantidade' => $quantidade,
    ]);
} catch (Exception $exception) {
    redirectAdminCreateEquipment('erro', 'equipamento_opcao');
}

redirectAdminCreateEquipment('sucesso', 'equipamento_criado');
