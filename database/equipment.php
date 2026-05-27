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

function getAllEquipment(PDO $db): array
{
    $stmt = $db->prepare(
        'SELECT id, nome, zona, estado, quantidade, atualizado_em
         FROM equipamentos
         ORDER BY zona, nome'
    );
    $stmt->execute();

    return $stmt->fetchAll();
}

function getEquipmentById(PDO $db, int $id): ?array
{
    $stmt = $db->prepare('SELECT * FROM equipamentos WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function createEquipment(PDO $db, array $data): int
{
    $stmt = $db->prepare(
        'INSERT INTO equipamentos (nome, zona, estado, quantidade)
         VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([
        $data['nome'],
        $data['zona'],
        $data['estado'],
        $data['quantidade'],
    ]);

    return (int)$db->lastInsertId();
}

function updateEquipment(PDO $db, int $id, array $data): bool
{
    $stmt = $db->prepare(
        'UPDATE equipamentos
         SET nome = ?, zona = ?, estado = ?, quantidade = ?, atualizado_em = CURRENT_TIMESTAMP
         WHERE id = ?'
    );

    return $stmt->execute([
        $data['nome'],
        $data['zona'],
        $data['estado'],
        $data['quantidade'],
        $id,
    ]) && $stmt->rowCount() > 0;
}

function deleteEquipment(PDO $db, int $id): bool
{
    $stmt = $db->prepare('DELETE FROM equipamentos WHERE id = ?');

    return $stmt->execute([$id]) && $stmt->rowCount() > 0;
}
