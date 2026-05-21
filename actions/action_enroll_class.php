<?php
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'membro') {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/enrollments.php';

$classId = (int)($_POST['class_id'] ?? 0);

if ($classId <= 0) {
    header('Location: ../pages/aulas.php?erro=1');
    exit;
}

$db = getDatabaseConnection();
$memberId = getMemberIdForUsername($db, $_SESSION['username']);

if (!$memberId || !enrollMemberInClass($db, $memberId, $classId)) {
    header('Location: ../pages/aulas.php?erro=1');
    exit;
}

header('Location: ../pages/aulas.php?sucesso=1');
exit;
