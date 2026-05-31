<?php
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'administrador') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/users.php';

$db = getDatabaseConnection();

$validPapeis = ['membro', 'treinador'];
$validEstados = ['ativo', 'inativo'];
$validOrdenar = ['nome_az', 'nome_za', 'recente', 'antigo'];

$filters = [
    'nome'    => trim($_GET['nome'] ?? ''),
    'papel'   => in_array($_GET['papel']   ?? '', $validPapeis,  true) ? $_GET['papel']   : '',
    'estado'  => in_array($_GET['estado']  ?? '', $validEstados, true) ? $_GET['estado']  : '',
    'ordenar' => in_array($_GET['ordenar'] ?? '', $validOrdenar, true) ? $_GET['ordenar'] : '',
];

$users = getFilteredManageableUsers($db, $filters);

header('Content-Type: application/json');
echo json_encode(['users' => $users]);
