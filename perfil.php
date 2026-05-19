<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/database/connection.php';
require_once __DIR__ . '/database/users.php';
require_once __DIR__ . '/database/enrollments.php';

require_once __DIR__ . '/templates/common.php';
require_once __DIR__ . '/templates/profile.php';

$db = getDatabaseConnection();
$user = getUserByUsername($db, $_SESSION['username']);

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$member = getMemberByUsername($db, $_SESSION['username']);
$enrollments = [];

if ($member) {
    $enrollments = getEnrollmentsForUsername($db, $_SESSION['username']);
}

output_header('Perfil - LAFit', 'perfil');
output_profile_page($user, $member, $enrollments);
output_footer();
