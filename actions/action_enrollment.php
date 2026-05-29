<?php
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'membro') {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/enrollments.php';
require_once __DIR__ . '/../database/users.php';
require_once __DIR__ . '/../database/csrf.php';

if (!verifyCsrfToken()) {
    http_response_code(403);
    header('Location: ../pages/login.php');
    exit;
}

function enrollmentReturnUrl(string $returnTo): string
{
    if (preg_match('/^(classes|profile)\.php(\?[A-Za-z0-9_=&%.-]*)?$/', $returnTo)) {
        return '../pages/' . $returnTo;
    }
    return '../pages/profile.php';
}

function enrollmentResult(string $url, string $result): string
{
    $sep = str_contains($url, '?') ? '&' : '?';
    return $url . $sep . $result . '=1';
}

$classId  = (int)($_POST['class_id'] ?? 0);
$returnTo = enrollmentReturnUrl($_POST['return_to'] ?? 'classes.php');
$action   = $_POST['_action'] ?? 'enroll';

if ($classId <= 0) {
    header('Location: ' . enrollmentResult($returnTo, 'erro'));
    exit;
}

$db = getDatabaseConnection();

if ($action === 'cancel') {
    $memberId = getMemberIdForUsername($db, $_SESSION['username']);
    if ($memberId) {
        cancelEnrollment($db, $memberId, $classId);
    }
    header('Location: ' . enrollmentResult($returnTo, 'sucesso'));
    exit;
}

$memberRow = getMemberByUsername($db, $_SESSION['username']);
$features  = getMemberPlanFeatures($memberRow['plano_nome'] ?? '');
if (!$features['classes']) {
    header('Location: ' . enrollmentResult($returnTo, 'erro'));
    exit;
}

$memberId = getMemberIdForUsername($db, $_SESSION['username']);
if (!$memberId || !enrollMemberInClass($db, $memberId, $classId)) {
    header('Location: ' . enrollmentResult($returnTo, 'erro'));
    exit;
}

header('Location: ' . enrollmentResult($returnTo, 'sucesso'));
exit;
