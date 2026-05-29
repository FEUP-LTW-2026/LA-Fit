<?php
session_start();

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/classes.php';
require_once __DIR__ . '/../database/enrollments.php';

require_once __DIR__ . '/../templates/common.php';
require_once __DIR__ . '/../templates/classes.php';

$db = getDatabaseConnection();
$allowedDays = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
$filters = [
    'type' => trim($_GET['type'] ?? ''),
    'trainer' => filter_input(INPUT_GET, 'trainer', FILTER_VALIDATE_INT) ?: '',
    'day' => in_array($_GET['day'] ?? '', $allowedDays, true) ? $_GET['day'] : '',
    'time' => trim($_GET['time'] ?? ''),
];

$classes = getFilteredClasses($db, $filters);
$filterOptions = getClassFilterOptions($db);
$enrolledClassIds = [];

if (isset($_SESSION['username']) && ($_SESSION['role'] ?? '') === 'membro') {
    $enrolledClassIds = getEnrolledClassIdsForUsername($db, $_SESSION['username']);
}

drawHeader('Aulas - LAFit', 'classes');
drawClassesPage($classes, $enrolledClassIds, $filters, $filterOptions);
drawFooter();
