<?php
function getWorkoutsForMember(PDO $db, int $memberId): array
{
    $stmt = $db->prepare(
        'SELECT id,
                data_treino,
                tipo,
                duracao_minutos,
                calorias,
                notas,
                criado_em
         FROM workouts
         WHERE membro_id = ?
         ORDER BY data_treino DESC, criado_em DESC
         LIMIT 12'
    );
    $stmt->execute([$memberId]);

    return $stmt->fetchAll();
}

function getGoalsForMember(PDO $db, int $memberId): array
{
    $stmt = $db->prepare(
        'SELECT id,
                membro_id,
                titulo,
                descricao,
                tipo,
                objetivo_valor,
                unidade,
                estado,
                data_inicio,
                data_limite,
                criado_em
         FROM metas
         WHERE membro_id = ?
           AND estado != "arquivado"
         ORDER BY estado DESC, criado_em DESC'
    );
    $stmt->execute([$memberId]);
    $goals = $stmt->fetchAll();

    foreach ($goals as &$goal) {
        $goal['progress'] = getGoalProgress($db, $goal);
    }

    return $goals;
}

function getGoalProgress(PDO $db, array $goal): array
{
    $memberId = $goal['membro_id'] ?? 0;
    $startDate = $goal['data_inicio'] ?? $goal['criado_em'] ?? date('Y-m-d');
    $target = (int)$goal['objetivo_valor'];

    switch ($goal['tipo']) {
        case 'minutos':
            $stmt = $db->prepare(
                'SELECT COALESCE(SUM(duracao_minutos), 0) AS valor
                 FROM workouts
                 WHERE membro_id = ?
                   AND date(data_treino) >= date(?)'
            );
            $stmt->execute([$memberId, $startDate]);
            $current = (int)$stmt->fetchColumn();
            $label = 'minutos';
            break;

        case 'calorias':
            $stmt = $db->prepare(
                'SELECT COALESCE(SUM(calorias), 0) AS valor
                 FROM workouts
                 WHERE membro_id = ?
                   AND date(data_treino) >= date(?)'
            );
            $stmt->execute([$memberId, $startDate]);
            $current = (int)$stmt->fetchColumn();
            $label = 'kcal';
            break;

        default:
            $stmt = $db->prepare(
                'SELECT COUNT(*) AS valor
                 FROM workouts
                 WHERE membro_id = ?
                   AND date(data_treino) >= date(?)'
            );
            $stmt->execute([$memberId, $startDate]);
            $current = (int)$stmt->fetchColumn();
            $label = 'treinos';
            break;
    }

    $percent = $target > 0 ? min(100, (int)floor(100 * $current / $target)) : 0;
    return [
        'current' => $current,
        'target' => $target,
        'percent' => $percent,
        'label' => $label,
        'complete' => $percent >= 100,
    ];
}

function getWorkoutSummaryForMember(PDO $db, int $memberId): array
{
    $stmt = $db->prepare(
        'SELECT
             COUNT(*) AS total_workouts,
             COALESCE(SUM(duracao_minutos), 0) AS total_minutes,
             COALESCE(SUM(calorias), 0) AS total_calories,
             COALESCE(AVG(duracao_minutos), 0) AS average_duration
         FROM workouts
         WHERE membro_id = ?
           AND date(data_treino) >= date("now", "-30 days")'
    );
    $stmt->execute([$memberId]);
    $stats = $stmt->fetch();

    $daily = getWorkoutDailyTotals($db, $memberId);

    return [
        'total_workouts' => (int)$stats['total_workouts'],
        'total_minutes' => (int)$stats['total_minutes'],
        'total_calories' => (int)$stats['total_calories'],
        'average_duration' => (int)round((float)$stats['average_duration']),
        'daily' => $daily,
    ];
}

function getWorkoutDailyTotals(PDO $db, int $memberId): array
{
    $days = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
    $counts = array_fill_keys($days, 0);

    $stmt = $db->prepare(
        'SELECT CASE strftime("%w", data_treino)
                 WHEN "0" THEN "domingo"
                 WHEN "1" THEN "segunda"
                 WHEN "2" THEN "terca"
                 WHEN "3" THEN "quarta"
                 WHEN "4" THEN "quinta"
                 WHEN "5" THEN "sexta"
                 WHEN "6" THEN "sabado"
               END AS dia,
               COUNT(*) AS total
         FROM workouts
         WHERE membro_id = ?
           AND date(data_treino) >= date("now", "-6 days")
         GROUP BY dia'
    );
    $stmt->execute([$memberId]);

    foreach ($stmt->fetchAll() as $row) {
        if (isset($counts[$row['dia']])) {
            $counts[$row['dia']] = (int)$row['total'];
        }
    }

    return $counts;
}

function logWorkout(PDO $db, int $memberId, array $data): bool
{
    $stmt = $db->prepare(
        'INSERT INTO workouts (membro_id, data_treino, tipo, duracao_minutos, calorias, notas)
         VALUES (?, ?, ?, ?, ?, ?)'
    );

    return $stmt->execute([
        $memberId,
        $data['date'],
        $data['type'],
        $data['duration'],
        $data['calories'],
        $data['notes'],
    ]);
}

function createGoal(PDO $db, int $memberId, array $data): bool
{
    $stmt = $db->prepare(
        'INSERT INTO metas (membro_id, titulo, descricao, tipo, objetivo_valor, unidade, estado, data_inicio, data_limite)
         VALUES (?, ?, ?, ?, ?, ?, "ativo", date("now"), ? )'
    );

    return $stmt->execute([
        $memberId,
        $data['title'],
        $data['description'],
        $data['type'],
        $data['target'],
        $data['unit'],
        $data['deadline'],
    ]);
}
