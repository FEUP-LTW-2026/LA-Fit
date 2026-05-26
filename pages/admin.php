<?php
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'administrador') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/users.php';
require_once __DIR__ . '/../database/plans.php';
require_once __DIR__ . '/../database/gyms.php';

require_once __DIR__ . '/../templates/common.php';
require_once __DIR__ . '/../templates/admin.php';

$db = getDatabaseConnection();
$users = getManageableUsers($db);
$plans = getAllPlans($db);
$gyms = getAllGyms($db);
$editingUser = null;
$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT) ?: 0;

if ($editId > 0) {
    foreach ($users as $user) {
        if ((int)$user['id'] === $editId) {
            $editingUser = $user;
            break;
        }
    }
}

$messages = [
    'success' => match ($_GET['sucesso'] ?? '') {
        'criado' => 'Conta criada com sucesso.',
        'atualizado' => 'Conta atualizada com sucesso.',
        'estado' => 'Estado da conta atualizado com sucesso.',
        default => null,
    },
    'error' => match ($_GET['erro'] ?? '') {
        'campos' => 'Preenche todos os campos obrigatórios.',
        'email' => 'Indica um email válido.',
        'role' => 'Escolhe um tipo de conta válido.',
        'estado' => 'Escolhe um estado válido.',
        'existe' => 'Já existe uma conta com esse username ou email.',
        'notfound' => 'Conta não encontrada.',
        default => null,
    },
];

drawHeader('Admin - LAFit', 'admin');
drawAdminPage($users, $plans, $gyms, $editingUser, $messages);
drawFooter();
