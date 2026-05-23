<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/users.php';
require_once __DIR__ . '/../database/enrollments.php';
require_once __DIR__ . '/../database/reviews.php';

require_once __DIR__ . '/../templates/common.php';
require_once __DIR__ . '/../templates/profile.php';

$db = getDatabaseConnection();
$user = getUserByUsername($db, $_SESSION['username']);

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$member = getMemberByUsername($db, $_SESSION['username']);
$enrollments = [];
$reviews = [];

if ($member) {
    $enrollments = getEnrollmentsForUsername($db, $_SESSION['username']);
    $reviews = getReviewsForMember($db, (int)$member['id']);
}

$messages = [
    'success' => match ($_GET['sucesso'] ?? '') {
        'avaliacao' => 'Avaliação guardada com sucesso.',
        default => isset($_GET['sucesso']) ? 'Perfil atualizado com sucesso.' : null,
    },
    'error' => match ($_GET['erro'] ?? '') {
        'campos' => 'Preenche todos os campos obrigatórios.',
        'email' => 'Indica um email válido.',
        'username' => 'Esse username já está a ser usado.',
        'email_existe' => 'Esse email já está a ser usado.',
        'password' => 'As palavras-passe não coincidem.',
        'avaliacao' => 'Não foi possível guardar essa avaliação.',
        'foto' => 'Não foi possível guardar a fotografia.',
        'foto_tamanho' => 'A fotografia não pode ter mais de 2 MB.',
        'foto_tipo' => 'Usa uma fotografia JPG, PNG ou WebP.',
        default => null,
    },
];

drawHeader('Perfil - LAFit', 'perfil');
drawProfilePage($user, $member, $enrollments, $reviews, $messages);
drawFooter();
