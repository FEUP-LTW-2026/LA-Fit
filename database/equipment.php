<?php
function getEquipmentByZone(PDO $db): array
{
    $stmt = $db->prepare(
        'SELECT id, nome, zona, estado, quantidade, atualizado_em
         FROM equipamentos
         ORDER BY zona, nome'
    );
    $stmt->execute();

    $zones = [];

    foreach ($stmt->fetchAll() as $equipment) {
        $zone = $equipment['zona'];

        if (!isset($zones[$zone])) {
            $zones[$zone] = [];
        }

        $zones[$zone][] = $equipment;
    }

    return $zones;
}

function getFilteredEquipmentByZone(PDO $db, array $filters): array
{
    $conditions = [];
    $params = [];

    if (!empty($filters['zona'])) {
        $conditions[] = 'zona = :zona';
        $params[':zona'] = $filters['zona'];
    }

    if (!empty($filters['estado'])) {
        $conditions[] = 'estado = :estado';
        $params[':estado'] = $filters['estado'];
    }

    $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $stmt = $db->prepare(
        "SELECT id, nome, zona, estado, quantidade, atualizado_em
         FROM equipamentos
         $where
         ORDER BY zona, nome"
    );
    $stmt->execute($params);

    $zones = [];

    foreach ($stmt->fetchAll() as $equipment) {
        $zone = $equipment['zona'];

        if (!isset($zones[$zone])) {
            $zones[$zone] = [];
        }

        $zones[$zone][] = $equipment;
    }

    return $zones;
}

function getEquipmentFilterOptions(PDO $db): array
{
    $stmt = $db->prepare('SELECT DISTINCT zona FROM equipamentos ORDER BY zona');
    $stmt->execute();
    return ['zones' => $stmt->fetchAll(PDO::FETCH_COLUMN)];
}

function getEquipmentAvailabilitySummary(PDO $db): array
{
    $stmt = $db->prepare(
        'SELECT estado, COALESCE(SUM(quantidade), 0) AS total
         FROM equipamentos
         GROUP BY estado'
    );
    $stmt->execute();

    $summary = [
        'disponivel' => 0,
        'ocupado' => 0,
        'manutencao' => 0,
    ];

    foreach ($stmt->fetchAll() as $row) {
        if (isset($summary[$row['estado']])) {
            $summary[$row['estado']] = (int)$row['total'];
        }
    }

    return $summary;
}
