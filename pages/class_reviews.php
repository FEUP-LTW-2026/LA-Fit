<?php
session_start();

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/reviews.php';
require_once __DIR__ . '/../database/classes.php';

require_once __DIR__ . '/../database/csrf.php';
require_once __DIR__ . '/../templates/common.php';
require_once __DIR__ . '/../templates/class_reviews.php';

$classId = (int)($_GET['aula'] ?? 0);

if ($classId <= 0) {
    header('Location: classes.php');
    exit;
}

$db = getDatabaseConnection();
$class = getClassById($db, $classId);

if (!$class) {
    header('Location: classes.php');
    exit;
}

$reviews = getClassReviews($db, $classId);

drawHeader(h($class['nome']) . ' - Opiniões - LAFit', 'class_reviews');
drawClassReviewsPage($class, $reviews);
drawFooter();
