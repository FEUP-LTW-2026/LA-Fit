<?php
session_start();

if (!isset($_SESSION['username'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/classes.php';

$db = getDatabaseConnection();

$filters = [
    'type'    => trim($_GET['type'] ?? ''),
    'trainer' => (int)($_GET['trainer'] ?? 0) ?: '',
    'day'     => trim($_GET['day'] ?? ''),
    'time'    => trim($_GET['time'] ?? ''),
];

$classes = getFilteredClasses($db, $filters);

$counts = [];
foreach ($classes as $class) {
    $counts[(int)$class['id']] = [
        'inscritos' => (int)$class['inscritos'],
        'lotacao'   => (int)$class['lotacao'],
        'vagas'     => (int)$class['lotacao'] - (int)$class['inscritos'],
    ];
}

header('Content-Type: application/json');
echo json_encode(['classes' => $counts]);
