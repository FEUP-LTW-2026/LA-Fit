<?php
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'membro') {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/enrollments.php';
require_once __DIR__ . '/../database/reviews.php';

$classId = (int)($_POST['class_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if ($classId <= 0 || $rating < 1 || $rating > 5) {
    header('Location: ../pages/perfil.php?erro=avaliacao');
    exit;
}

$db = getDatabaseConnection();
$memberId = getMemberIdForUsername($db, $_SESSION['username']);

if (!$memberId || !saveClassReview($db, $memberId, $classId, $rating, $comment)) {
    header('Location: ../pages/perfil.php?erro=avaliacao');
    exit;
}

header('Location: ../pages/perfil.php?sucesso=avaliacao');
exit;
