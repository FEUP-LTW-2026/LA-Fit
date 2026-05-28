<?php
function createNutritionPlan(PDO $db, int $trainerId, string $nome, string $descricao): int|false
{
    $stmt = $db->prepare(
        'INSERT INTO planos_nutricao (treinador_id, nome, descricao) VALUES (?, ?, ?)'
    );
    if ($stmt->execute([$trainerId, $nome, $descricao ?: null])) {
        return (int)$db->lastInsertId();
    }
    return false;
}

function deleteNutritionPlan(PDO $db, int $planId, int $trainerId): bool
{
    $stmt = $db->prepare('DELETE FROM planos_nutricao WHERE id = ? AND treinador_id = ?');
    return $stmt->execute([$planId, $trainerId]);
}

function addMealToPlan(PDO $db, int $planId, string $nome, string $tipo, int $calorias, float $proteinas, float $hidratos, float $gorduras, int $trainerId): bool
{
    $check = $db->prepare('SELECT id FROM planos_nutricao WHERE id = ? AND treinador_id = ?');
    $check->execute([$planId, $trainerId]);
    if (!$check->fetch()) return false;

    $stmt = $db->prepare(
        'INSERT INTO refeicoes (plano_id, nome, tipo, calorias, proteinas, hidratos, gorduras)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    return $stmt->execute([$planId, $nome, $tipo, $calorias, $proteinas, $hidratos, $gorduras]);
}

function deleteMeal(PDO $db, int $mealId, int $trainerId): bool
{
    $stmt = $db->prepare(
        'DELETE FROM refeicoes
         WHERE id = ? AND plano_id IN (SELECT id FROM planos_nutricao WHERE treinador_id = ?)'
    );
    return $stmt->execute([$mealId, $trainerId]);
}

function getTrainerNutritionPlans(PDO $db, int $trainerId): array
{
    $stmt = $db->prepare(
        'SELECT * FROM planos_nutricao WHERE treinador_id = ? ORDER BY criado_em DESC'
    );
    $stmt->execute([$trainerId]);
    $plans = $stmt->fetchAll();

    foreach ($plans as &$plan) {
        $s = $db->prepare('SELECT * FROM refeicoes WHERE plano_id = ? ORDER BY tipo, ordem, nome');
        $s->execute([$plan['id']]);
        $plan['refeicoes'] = $s->fetchAll();

        $s = $db->prepare(
            'SELECT pnm.id AS atribuicao_id, pnm.atribuido_em,
                    m.id AS membro_id, u.nome, u.apelido, u.nome_utilizador
             FROM planos_nutricao_membros pnm
             JOIN membros m ON m.id = pnm.membro_id
             JOIN utilizadores u ON u.id = m.utilizador_id
             WHERE pnm.plano_id = ?
             ORDER BY pnm.atribuido_em DESC'
        );
        $s->execute([$plan['id']]);
        $plan['atribuicoes'] = $s->fetchAll();
    }

    return $plans;
}

function getMembersForAssignment(PDO $db): array
{
    $stmt = $db->prepare(
        "SELECT m.id, u.nome, u.apelido, u.nome_utilizador
         FROM membros m
         JOIN utilizadores u ON u.id = m.utilizador_id
         WHERE u.estado = 'ativo'
         ORDER BY u.nome, u.apelido"
    );
    $stmt->execute();
    return $stmt->fetchAll();
}

function assignPlanToMember(PDO $db, int $planId, int $membroId, int $trainerId): bool
{
    $check = $db->prepare('SELECT id FROM planos_nutricao WHERE id = ? AND treinador_id = ?');
    $check->execute([$planId, $trainerId]);
    if (!$check->fetch()) return false;

    $stmt = $db->prepare(
        'INSERT OR IGNORE INTO planos_nutricao_membros (plano_id, membro_id) VALUES (?, ?)'
    );
    return $stmt->execute([$planId, $membroId]);
}

function unassignPlanFromMember(PDO $db, int $assignId, int $trainerId): bool
{
    $stmt = $db->prepare(
        'DELETE FROM planos_nutricao_membros
         WHERE id = ? AND plano_id IN (SELECT id FROM planos_nutricao WHERE treinador_id = ?)'
    );
    return $stmt->execute([$assignId, $trainerId]);
}

function getMemberNutritionPlans(PDO $db, int $membroId): array
{
    $stmt = $db->prepare(
        'SELECT pn.*, pnm.id AS atribuicao_id, pnm.atribuido_em,
                u.nome AS treinador_nome, u.apelido AS treinador_apelido
         FROM planos_nutricao_membros pnm
         JOIN planos_nutricao pn ON pn.id = pnm.plano_id
         JOIN treinadores t ON t.id = pn.treinador_id
         JOIN utilizadores u ON u.id = t.utilizador_id
         WHERE pnm.membro_id = ?
         ORDER BY pnm.atribuido_em DESC'
    );
    $stmt->execute([$membroId]);
    $plans = $stmt->fetchAll();

    foreach ($plans as &$plan) {
        $s = $db->prepare('SELECT * FROM refeicoes WHERE plano_id = ? ORDER BY tipo, ordem, nome');
        $s->execute([$plan['id']]);
        $plan['refeicoes'] = $s->fetchAll();
    }

    return $plans;
}
