<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'treinador') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/users.php';
require_once __DIR__ . '/../database/classes.php';

require_once __DIR__ . '/../templates/common.php';
require_once __DIR__ . '/../templates/trainer.php';

$db = getDatabaseConnection();
$user = getUserByUsername($db, $_SESSION['username']);

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$trainer = getTrainerByUsername($db, $_SESSION['username']);
$classes = getFilteredClasses($db, ['trainer' => $trainer['id']]);

$messages = [
    'success' => isset($_GET['sucesso']) ? 'Perfil atualizado com sucesso.' : null,
    'error' => match ($_GET['erro'] ?? '') {
        'campos' => 'Preenche todos os campos obrigatórios.',
        'email' => 'Indica um email válido.',
        'email_existe' => 'Esse email já está a ser usado.',
        'foto' => 'Não foi possível guardar a fotografia.',
        'foto_tamanho' => 'A fotografia não pode ter mais de 2 MB.',
        'foto_tipo' => 'Usa uma fotografia JPG, PNG ou WebP.',
        default => null,
    },
];

drawHeader('Área Treinador - LAFit', 'trainer');
drawTrainerPage($user, $trainer, $classes, $messages);
drawFooter();
