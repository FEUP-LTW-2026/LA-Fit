<?php
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'membro') {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/enrollments.php';

$classId = (int)($_POST['class_id'] ?? 0);
$returnTo = getEnrollmentReturnUrl($_POST['return_to'] ?? 'classes.php');

if ($classId <= 0) {
    header('Location: ' . addEnrollmentResult($returnTo, 'erro'));
    exit;
}

$db = getDatabaseConnection();
$memberId = getMemberIdForUsername($db, $_SESSION['username']);

if (!$memberId || !enrollMemberInClass($db, $memberId, $classId)) {
    header('Location: ' . addEnrollmentResult($returnTo, 'erro'));
    exit;
}

header('Location: ' . addEnrollmentResult($returnTo, 'sucesso'));
exit;

function getEnrollmentReturnUrl(string $returnTo): string
{
    if (preg_match('/^aulas\.php(\?[A-Za-z0-9_=&%.-]*)?$/', $returnTo)) {
        return '../pages/' . $returnTo;
    }

    return '../pages/classes.php';
}

function addEnrollmentResult(string $url, string $result): string
{
    $separator = str_contains($url, '?') ? '&' : '?';

    return $url . $separator . $result . '=1';
}
