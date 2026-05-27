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
require_once __DIR__ . '/../database/classes.php';
require_once __DIR__ . '/../database/reports.php';
require_once __DIR__ . '/../database/equipment.php';

require_once __DIR__ . '/../templates/common.php';
require_once __DIR__ . '/../templates/admin.php';

$db = getDatabaseConnection();
$users = getManageableUsers($db);
$plans = getAllPlans($db);
$gyms = getAllGyms($db);
$classes = getAdminClasses($db);
$trainers = getActiveTrainers($db);
$reports = getAllReports($db);
$equipment = getAllEquipment($db);
$editingUser = null;
$editingClass = null;
$editingEquipment = null;
$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT) ?: 0;
$editClassId = filter_input(INPUT_GET, 'edit_class', FILTER_VALIDATE_INT) ?: 0;
$editEquipmentId = filter_input(INPUT_GET, 'edit_equipment', FILTER_VALIDATE_INT) ?: 0;

if ($editId > 0) {
    foreach ($users as $user) {
        if ((int)$user['id'] === $editId) {
            $editingUser = $user;
            break;
        }
    }
}

if ($editClassId > 0) {
    foreach ($classes as $class) {
        if ((int)$class['id'] === $editClassId) {
            $editingClass = $class;
            break;
        }
    }
}

if ($editEquipmentId > 0) {
    $editingEquipment = getEquipmentById($db, $editEquipmentId);
}

$messages = [
    'success' => match ($_GET['sucesso'] ?? '') {
        'criado'                => 'Conta criada com sucesso.',
        'atualizado'            => 'Conta atualizada com sucesso.',
        'estado'                => 'Estado da conta atualizado com sucesso.',
        'aula_criada'           => 'Aula criada com sucesso.',
        'aula_atualizada'       => 'Aula atualizada com sucesso.',
        'aula_removida'         => 'Aula removida do catálogo com sucesso.',
        'reporte_atualizado'    => 'Reporte atualizado com sucesso.',
        'equipamento_criado'    => 'Equipamento adicionado com sucesso.',
        'equipamento_atualizado' => 'Equipamento atualizado com sucesso.',
        'equipamento_removido'  => 'Equipamento removido com sucesso.',
        default                 => null,
    },
    'error' => match ($_GET['erro'] ?? '') {
        'campos'               => 'Preenche todos os campos obrigatórios.',
        'email'                => 'Indica um email válido.',
        'role'                 => 'Escolhe um tipo de conta válido.',
        'estado'               => 'Escolhe um estado válido.',
        'existe'               => 'Já existe uma conta com esse username ou email.',
        'notfound'             => 'Conta não encontrada.',
        'plano'                => 'Um membro tem de ter um plano associado.',
        'aula_notfound'        => 'Aula não encontrada.',
        'aula_horario'         => 'Confirma a hora de início e fim da aula.',
        'aula_numero'          => 'A lotação tem de ser maior que zero.',
        'aula_opcao'           => 'Escolhe opções válidas para a aula.',
        'reporte_notfound'     => 'Reporte não encontrado.',
        'reporte_opcao'        => 'Escolhe um estado válido para o reporte.',
        'equipamento_campos'   => 'Preenche o nome e a zona do equipamento.',
        'equipamento_opcao'    => 'Escolhe um estado válido para o equipamento.',
        'equipamento_numero'   => 'A quantidade tem de ser maior que zero.',
        'equipamento_notfound' => 'Equipamento não encontrado.',
        default                => null,
    },
];

drawHeader('Admin - LAFit', 'admin');
drawAdminPage($users, $plans, $gyms, $classes, $trainers, $reports, $equipment, $editingUser, $editingClass, $editingEquipment, $messages);
drawFooter();
