<?php
function getAllGyms(PDO $db): array
{
    $stmt = $db->prepare('SELECT * FROM ginasios ORDER BY nome');
    $stmt->execute();

    return $stmt->fetchAll();
}

function getGymById(PDO $db, int $id): ?array
{
    $stmt = $db->prepare('SELECT * FROM ginasios WHERE id = ?');
    $stmt->execute([$id]);
    $gym = $stmt->fetch();

    return $gym ?: null;
}
