<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'treinador') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/users.php';
require_once __DIR__ . '/../database/enrollments.php';
require_once __DIR__ . '/../database/classes.php';

require_once __DIR__ . '/../templates/common.php';
require_once __DIR__ . '/../templates/class_roster.php';

$classId = (int)($_GET['aula'] ?? 0);

if ($classId <= 0) {
    header('Location: profile.php');
    exit;
}

$db = getDatabaseConnection();
$trainer = getTrainerByUsername($db, $_SESSION['username']);
$members = getEnrolledMembersForClass($db, $classId, (int)$trainer['id']);

if ($members === null) {
    header('Location: profile.php');
    exit;
}

$class = getClassById($db, $classId);

drawHeader('Inscritos - LAFit', 'trainer');
drawClassRosterPage($class, $members);
drawFooter();
