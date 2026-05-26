<?php
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'administrador') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/users.php';
require_once __DIR__ . '/../database/classes.php';
require_once __DIR__ . '/../database/plans.php';
require_once __DIR__ . '/../database/gyms.php';

require_once __DIR__ . '/../templates/common.php';
require_once __DIR__ . '/../templates/admin.php';

$db = getDatabaseConnection();
$users = getManageableUsers($db);
$classes = getManagedClasses($db);
$trainers = getAvailableClassTrainers($db);
$plans = getAllPlans($db);
$gyms = getAllGyms($db);
$editingUser = null;
$editingClass = null;
$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT) ?: 0;
$classEditId = filter_input(INPUT_GET, 'class_edit', FILTER_VALIDATE_INT) ?: 0;

if ($editId > 0) {
    foreach ($users as $user) {
        if ((int)$user['id'] === $editId) {
            $editingUser = $user;
            break;
        }
    }
}

if ($classEditId > 0) {
    foreach ($classes as $class) {
        if ((int)$class['id'] === $classEditId) {
            $editingClass = $class;
            break;
        }
    }
}

$messages = [
    'success' => match ($_GET['sucesso'] ?? '') {
        'criado' => 'Conta criada com sucesso.',
        'atualizado' => 'Conta atualizada com sucesso.',
        'estado' => 'Estado da conta atualizado com sucesso.',
        'aula_criada' => 'Aula criada com sucesso.',
        'aula_atualizada' => 'Aula atualizada com sucesso.',
        'aula_removida' => 'Aula removida do catálogo com sucesso.',
        default => null,
    },
    'error' => match ($_GET['erro'] ?? '') {
        'campos' => 'Preenche todos os campos obrigatórios.',
        'email' => 'Indica um email válido.',
        'role' => 'Escolhe um tipo de conta válido.',
        'estado' => 'Escolhe um estado válido.',
        'existe' => 'Já existe uma conta com esse username ou email.',
        'notfound' => 'Conta não encontrada.',
        'aula_campos' => 'Preenche todos os campos obrigatórios da aula.',
        'aula_hora' => 'A hora de fim deve ser posterior à hora de início.',
        'aula_notfound' => 'Aula não encontrada.',
        default => null,
    },
];

drawHeader('Admin - LAFit', 'admin');
drawAdminPage($users, $plans, $gyms, $editingUser, $classes, $trainers, $editingClass, $messages);
drawFooter();
