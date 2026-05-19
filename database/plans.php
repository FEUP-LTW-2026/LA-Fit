<?php
function getAllPlans(PDO $db): array
{
    $stmt = $db->prepare('SELECT * FROM planos ORDER BY preco_mensal');
    $stmt->execute();

    return $stmt->fetchAll();
}

function getPlanById(PDO $db, int $id): ?array
{
    $stmt = $db->prepare('SELECT * FROM planos WHERE id = ?');
    $stmt->execute([$id]);
    $plan = $stmt->fetch();

    return $plan ?: null;
}
