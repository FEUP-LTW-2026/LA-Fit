<?php
session_start();

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/classes.php';
require_once __DIR__ . '/../database/enrollments.php';

require_once __DIR__ . '/../templates/common.php';
require_once __DIR__ . '/../templates/classes.php';

$db = getDatabaseConnection();
$classes = getAllClasses($db);
$enrolledClassIds = [];

if (isset($_SESSION['username']) && ($_SESSION['role'] ?? '') === 'membro') {
    $enrolledClassIds = getEnrolledClassIdsForUsername($db, $_SESSION['username']);
}

drawHeader('Aulas - LAFit', 'aulas');
drawClassesPage($classes, $enrolledClassIds);
drawFooter();
