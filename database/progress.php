<?php
function getWorkoutsForMember(PDO $db, int $membroId): array
{
    $stmt = $db->prepare(
        'SELECT * FROM treinos WHERE membro_id = ? ORDER BY data DESC, criado_em DESC LIMIT 50'
    );
    $stmt->execute([$membroId]);
    return $stmt->fetchAll();
}

function logWorkout(PDO $db, int $membroId, string $data, string $tipo, int $duracao, string $notas): bool
{
    $stmt = $db->prepare(
        'INSERT INTO treinos (membro_id, data, tipo, duracao_minutos, notas) VALUES (?, ?, ?, ?, ?)'
    );
    return $stmt->execute([$membroId, $data, $tipo, $duracao, $notas]);
}

function deleteWorkout(PDO $db, int $workoutId, int $membroId): bool
{
    $stmt = $db->prepare('DELETE FROM treinos WHERE id = ? AND membro_id = ?');
    return $stmt->execute([$workoutId, $membroId]);
}

function getGoalsForMember(PDO $db, int $membroId): array
{
    $stmt = $db->prepare(
        'SELECT * FROM objetivos WHERE membro_id = ? ORDER BY concluido ASC, criado_em DESC'
    );
    $stmt->execute([$membroId]);
    return $stmt->fetchAll();
}

function createGoal(PDO $db, int $membroId, string $descricao, float $valorAlvo, string $unidade, ?string $dataLimite): bool
{
    $stmt = $db->prepare(
        'INSERT INTO objetivos (membro_id, descricao, valor_alvo, unidade, data_limite) VALUES (?, ?, ?, ?, ?)'
    );
    return $stmt->execute([$membroId, $descricao, $valorAlvo, $unidade, $dataLimite ?: null]);
}

function updateGoalProgress(PDO $db, int $goalId, int $membroId, float $valorAtual): bool
{
    $stmt = $db->prepare(
        'UPDATE objetivos
         SET valor_atual = ?,
             concluido = CASE WHEN ? >= valor_alvo THEN 1 ELSE 0 END
         WHERE id = ? AND membro_id = ?'
    );
    return $stmt->execute([$valorAtual, $valorAtual, $goalId, $membroId]);
}

function deleteGoal(PDO $db, int $goalId, int $membroId): bool
{
    $stmt = $db->prepare('DELETE FROM objetivos WHERE id = ? AND membro_id = ?');
    return $stmt->execute([$goalId, $membroId]);
}

function getWorkoutStats(PDO $db, int $membroId): array
{
    $mesAtual = date('Y-m');

    $stmt = $db->prepare(
        "SELECT
            COUNT(*) AS total_treinos,
            COALESCE(SUM(duracao_minutos), 0) AS total_minutos,
            COUNT(DISTINCT data) AS dias_ativos
         FROM treinos
         WHERE membro_id = ? AND strftime('%Y-%m', data) = ?"
    );
    $stmt->execute([$membroId, $mesAtual]);
    $mes = $stmt->fetch();

    $stmt = $db->prepare(
        "SELECT COUNT(*) AS total FROM treinos WHERE membro_id = ?"
    );
    $stmt->execute([$membroId]);
    $total = $stmt->fetch();

    $stmt = $db->prepare(
        "SELECT strftime('%W-%Y', data) AS semana, COUNT(*) AS treinos
         FROM treinos
         WHERE membro_id = ? AND data >= date('now', '-8 weeks')
         GROUP BY semana
         ORDER BY semana ASC"
    );
    $stmt->execute([$membroId]);
    $semanal = $stmt->fetchAll();

    return [
        'mes_treinos'   => (int)$mes['total_treinos'],
        'mes_minutos'   => (int)$mes['total_minutos'],
        'mes_dias'      => (int)$mes['dias_ativos'],
        'total_treinos' => (int)$total['total'],
        'semanal'       => $semanal,
    ];
}
