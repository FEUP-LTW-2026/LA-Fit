<?php
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'membro') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/enrollments.php';
require_once __DIR__ . '/../database/reviews.php';

require_once __DIR__ . '/../templates/common.php';
require_once __DIR__ . '/../templates/reviews.php';

$db = getDatabaseConnection();
$memberId = getMemberIdForUsername($db, $_SESSION['username']);

if (!$memberId) {
    header('Location: perfil.php');
    exit;
}

$classes = getReviewableClassesForMember($db, $memberId);
$selectedClassId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;

drawHeader('Opinião - LAFit', 'aulas');
drawReviewPage($classes, $selectedClassId);
drawFooter();
