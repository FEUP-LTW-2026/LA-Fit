<?php
session_start();

require_once __DIR__ . '/database/connection.php';
require_once __DIR__ . '/database/plans.php';
require_once __DIR__ . '/database/gyms.php';
require_once __DIR__ . '/database/classes.php';

require_once __DIR__ . '/templates/common.php';
require_once __DIR__ . '/templates/home.php';

$db = getDatabaseConnection();
$plans = getAllPlans($db);
$gyms = getAllGyms($db);
$classes = getAllClasses($db);

output_header('LAFit', 'home');
output_home($plans, $gyms, $classes);
output_footer();
