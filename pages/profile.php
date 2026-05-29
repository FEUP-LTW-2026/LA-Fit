<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['role'] ?? 'membro';

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/users.php';
require_once __DIR__ . '/../templates/common.php';

$db = getDatabaseConnection();
$user = getUserByUsername($db, $_SESSION['username']);

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

if ($role === 'administrador') {

    require_once __DIR__ . '/../database/plans.php';
    require_once __DIR__ . '/../database/gyms.php';
    require_once __DIR__ . '/../database/classes.php';
    require_once __DIR__ . '/../database/equipment.php';
    require_once __DIR__ . '/../database/overview.php';
    require_once __DIR__ . '/../templates/admin.php';

    $users          = getManageableUsers($db);
    $plans          = getAllPlans($db);
    $gyms           = getAllGyms($db);
    $classes        = getAdminClasses($db);
    $trainers       = getActiveTrainers($db);
    $equipment      = getAllEquipment($db);
    $overview       = getSystemOverview($db);
    $editingUser      = null;
    $editingClass     = null;
    $editingEquipment = null;

    $editId          = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT) ?: 0;
    $editClassId     = filter_input(INPUT_GET, 'edit_class', FILTER_VALIDATE_INT) ?: 0;
    $editEquipmentId = filter_input(INPUT_GET, 'edit_equipment', FILTER_VALIDATE_INT) ?: 0;

    if ($editId > 0) {
        foreach ($users as $u) {
            if ((int)$u['id'] === $editId) { $editingUser = $u; break; }
        }
    }
    if ($editClassId > 0) {
        foreach ($classes as $c) {
            if ((int)$c['id'] === $editClassId) { $editingClass = $c; break; }
        }
    }
    if ($editEquipmentId > 0) {
        $editingEquipment = getEquipmentById($db, $editEquipmentId);
    }

    $messages = [
        'success' => match ($_GET['sucesso'] ?? '') {
            'criado'                  => 'Conta criada com sucesso.',
            'atualizado'              => 'Conta atualizada com sucesso.',
            'estado'                  => 'Estado da conta atualizado com sucesso.',
            'aula_criada'             => 'Aula criada com sucesso.',
            'aula_atualizada'         => 'Aula atualizada com sucesso.',
            'aula_removida'           => 'Aula removida do catálogo com sucesso.',
            'equipamento_criado'      => 'Equipamento adicionado com sucesso.',
            'equipamento_atualizado'  => 'Equipamento atualizado com sucesso.',
            'equipamento_removido'    => 'Equipamento removido com sucesso.',
            default                   => null,
        },
        'error' => match ($_GET['erro'] ?? '') {
            'campos'                => 'Preenche todos os campos obrigatórios.',
            'email'                 => 'Indica um email válido.',
            'role'                  => 'Escolhe um tipo de conta válido.',
            'estado'                => 'Escolhe um estado válido.',
            'existe'                => 'Já existe uma conta com esse username ou email.',
            'notfound'              => 'Conta não encontrada.',
            'plano'                 => 'Um membro tem de ter um plano associado.',
            'aula_notfound'         => 'Aula não encontrada.',
            'aula_horario'          => 'Confirma a hora de início e fim da aula.',
            'aula_numero'           => 'A lotação tem de ser maior que zero.',
            'aula_opcao'            => 'Escolhe opções válidas para a aula.',
            'equipamento_campos'    => 'Preenche o nome e a zona do equipamento.',
            'equipamento_opcao'     => 'Escolhe um estado válido para o equipamento.',
            'equipamento_numero'    => 'A quantidade tem de ser maior que zero.',
            'equipamento_notfound'  => 'Equipamento não encontrado.',
            default                 => null,
        },
    ];

    drawHeader('Admin - LAFit', 'admin');
    drawAdminPage($users, $plans, $gyms, $classes, $trainers, $equipment, $editingUser, $editingClass, $editingEquipment, $messages, $overview);
    drawFooter();

} elseif ($role === 'treinador') {

    require_once __DIR__ . '/../database/classes.php';
    require_once __DIR__ . '/../database/nutrition.php';
    require_once __DIR__ . '/../templates/trainer.php';
    require_once __DIR__ . '/../templates/nutrition.php';

    $trainer          = getTrainerByUsername($db, $_SESSION['username']);
    $classes          = getFilteredClasses($db, ['trainer' => $trainer['id']]);
    $nutritionPlans   = getTrainerNutritionPlans($db, (int)$trainer['id']);
    $nutritionMembers = getMembersForAssignment($db);

    $messages = [
        'success' => match ($_GET['sucesso'] ?? '') {
            'sucesso'              => 'Perfil atualizado com sucesso.',
            'plano_criado'         => 'Plano nutricional criado com sucesso.',
            'plano_removido'       => 'Plano nutricional eliminado.',
            'refeicao_adicionada'  => 'Refeição adicionada ao plano.',
            'refeicao_removida'    => 'Refeição removida.',
            'plano_atribuido'      => 'Plano atribuído ao membro.',
            'plano_removido_membro'=> 'Membro removido do plano.',
            default                => isset($_GET['sucesso']) ? 'Perfil atualizado com sucesso.' : null,
        },
        'error'   => match ($_GET['erro'] ?? '') {
            'campos'          => 'Preenche todos os campos obrigatórios.',
            'email'           => 'Indica um email válido.',
            'email_existe'    => 'Esse email já está a ser usado.',
            'foto'            => 'Não foi possível guardar a fotografia.',
            'foto_tamanho'    => 'A fotografia não pode ter mais de 2 MB.',
            'foto_tipo'       => 'Usa uma fotografia JPG, PNG ou WebP.',
            'nutricao_campos' => 'Preenche todos os campos obrigatórios do plano.',
            'refeicao_campos' => 'Preenche o nome e tipo da refeição.',
            default           => null,
        },
    ];

    drawHeader('Área Treinador - LAFit', 'trainer');
    drawTrainerPage($user, $trainer, $classes, $messages, $nutritionPlans, $nutritionMembers);
    drawFooter();

} else {

    require_once __DIR__ . '/../database/enrollments.php';
    require_once __DIR__ . '/../database/equipment.php';
    require_once __DIR__ . '/../database/progress.php';
    require_once __DIR__ . '/../database/nutrition.php';
    require_once __DIR__ . '/../templates/profile.php';
    require_once __DIR__ . '/../templates/equipment.php';
    require_once __DIR__ . '/../templates/progress.php';
    require_once __DIR__ . '/../templates/nutrition.php';

    $member               = getMemberByUsername($db, $_SESSION['username']);
    $enrollments          = [];
    $equipmentByZone      = [];
    $equipmentSummary     = [];
    $equipmentFilters     = [];
    $equipmentFilterOptions = [];
    $workouts             = [];
    $goals                = [];
    $workoutStats         = ['mes_treinos' => 0, 'mes_minutos' => 0, 'mes_dias' => 0, 'total_treinos' => 0, 'semanal' => []];
    $nutritionPlans       = [];

    if ($member) {
        $allowedStates = ['disponivel', 'ocupado', 'manutencao'];
        $equipmentFilters = [
            'zona'   => trim($_GET['zona'] ?? ''),
            'estado' => in_array($_GET['estado'] ?? '', $allowedStates, true) ? $_GET['estado'] : '',
        ];
        $enrollments          = getEnrollmentsForUsername($db, $_SESSION['username']);
        $equipmentByZone      = getFilteredEquipmentByZone($db, $equipmentFilters);
        $equipmentSummary     = getEquipmentAvailabilitySummary($db);
        $equipmentFilterOptions = getEquipmentFilterOptions($db);
        $workouts             = getWorkoutsForMember($db, (int)$member['id']);
        $goals                = getGoalsForMember($db, (int)$member['id']);
        $workoutStats         = getWorkoutStats($db, (int)$member['id']);
        $nutritionPlans       = getMemberNutritionPlans($db, (int)$member['id']);
    }

    $messages = [
        'success' => match ($_GET['sucesso'] ?? '') {
            'sucesso'            => 'Perfil atualizado com sucesso.',
            'treino_registado'   => 'Treino registado com sucesso.',
            'treino_removido'    => 'Treino removido.',
            'objetivo_criado'    => 'Objetivo criado com sucesso.',
            'objetivo_atualizado'=> 'Progresso atualizado.',
            'objetivo_removido'  => 'Objetivo removido.',
            default              => isset($_GET['sucesso']) ? 'Perfil atualizado com sucesso.' : null,
        },
        'error'   => match ($_GET['erro'] ?? '') {
            'campos'          => 'Preenche todos os campos obrigatórios.',
            'email'           => 'Indica um email válido.',
            'username'        => 'Esse username já está a ser usado.',
            'email_existe'    => 'Esse email já está a ser usado.',
            'password'        => 'As palavras-passe não coincidem.',
            'foto'            => 'Não foi possível guardar a fotografia.',
            'foto_tamanho'    => 'A fotografia não pode ter mais de 2 MB.',
            'foto_tipo'       => 'Usa uma fotografia JPG, PNG ou WebP.',
            'treino_campos'   => 'Preenche todos os campos do treino.',
            'treino_tipo'     => 'Escolhe um tipo de treino válido.',
            'objetivo_campos' => 'Preenche a descrição e a meta do objetivo.',
            default           => null,
        },
    ];

    drawHeader('Perfil - LAFit', 'profile');
    drawProfilePage($user, $member, $enrollments, $equipmentByZone, $equipmentSummary, $equipmentFilters, $equipmentFilterOptions, $messages, $workouts, $goals, $workoutStats, $nutritionPlans);
    drawFooter();

}
