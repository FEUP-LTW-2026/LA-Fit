<?php
session_start();

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/users.php';
require_once __DIR__ . '/../database/classes.php';

require_once __DIR__ . '/../templates/common.php';
require_once __DIR__ . '/../templates/profile_view.php';

$trainerId = (int)($_GET['id'] ?? 0);

if ($trainerId <= 0) {
    header('Location: classes.php');
    exit;
}

$db = getDatabaseConnection();
$trainer = getTrainerById($db, $trainerId);

if (!$trainer) {
    header('Location: classes.php');
    exit;
}

$classes = getFilteredClasses($db, ['trainer' => $trainerId]);

drawHeader(h($trainer['nome'] . ' ' . $trainer['apelido']) . ' - LAFit', 'classes');
drawTrainerProfilePage($trainer, $classes);
drawFooter();
