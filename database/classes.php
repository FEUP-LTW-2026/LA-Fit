<?php
function getAllClasses(PDO $db): array
{
    return getFilteredClasses($db, []);
}

function getFilteredClasses(PDO $db, array $filters): array
{
    $where = ['aulas.estado = "agendada"'];
    $params = [];

    if (!empty($filters['type'])) {
        $where[] = 'aulas.tipo = ?';
        $params[] = $filters['type'];
    }

    if (!empty($filters['trainer'])) {
        $where[] = 'treinadores.id = ?';
        $params[] = (int)$filters['trainer'];
    }

    if (!empty($filters['day'])) {
        $where[] = 'aulas.dia_semana = ?';
        $params[] = $filters['day'];
    }

    if (!empty($filters['time'])) {
        $where[] = 'aulas.inicio = ?';
        $params[] = $filters['time'];
    }

    $stmt = $db->prepare(
        'SELECT aulas.*,
                ginasios.nome AS ginasio_nome,
                utilizadores.nome || " " || utilizadores.apelido AS treinador_nome,
                COALESCE(SUM(CASE WHEN inscricoes_aulas.estado = "inscrito" THEN 1 ELSE 0 END), 0) AS inscritos
         FROM aulas
         JOIN treinadores ON treinadores.id = aulas.treinador_id
         JOIN utilizadores ON utilizadores.id = treinadores.utilizador_id
         JOIN ginasios ON ginasios.id = aulas.ginasio_id
         LEFT JOIN inscricoes_aulas ON inscricoes_aulas.aula_id = aulas.id
         WHERE ' . implode(' AND ', $where) . '
         GROUP BY aulas.id
         ORDER BY
            CASE aulas.dia_semana
                WHEN "segunda" THEN 1
                WHEN "terca" THEN 2
                WHEN "quarta" THEN 3
                WHEN "quinta" THEN 4
                WHEN "sexta" THEN 5
                WHEN "sabado" THEN 6
                ELSE 7
            END,
            aulas.inicio'
    );
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function getClassFilterOptions(PDO $db): array
{
    return [
        'types' => fetchClassTypes($db),
        'trainers' => fetchClassTrainers($db),
        'times' => fetchClassTimes($db),
    ];
}

function fetchClassTypes(PDO $db): array
{
    $stmt = $db->prepare(
        'SELECT tipo, MIN(nome) AS nome
         FROM aulas
         WHERE estado = "agendada"
         GROUP BY tipo
         ORDER BY nome'
    );
    $stmt->execute();

    return $stmt->fetchAll();
}

function fetchClassTrainers(PDO $db): array
{
    $stmt = $db->prepare(
        'SELECT DISTINCT treinadores.id,
                utilizadores.nome || " " || utilizadores.apelido AS nome
         FROM aulas
         JOIN treinadores ON treinadores.id = aulas.treinador_id
         JOIN utilizadores ON utilizadores.id = treinadores.utilizador_id
         WHERE aulas.estado = "agendada"
         ORDER BY nome'
    );
    $stmt->execute();

    return $stmt->fetchAll();
}

function fetchClassTimes(PDO $db): array
{
    $stmt = $db->prepare(
        'SELECT DISTINCT inicio
         FROM aulas
         WHERE estado = "agendada"
         ORDER BY inicio'
    );
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getFeaturedClasses(PDO $db, int $limit = 3): array
{
    $classes = getAllClasses($db);

    return array_slice($classes, 0, $limit);
}

function getClassById(PDO $db, int $id): ?array
{
    $stmt = $db->prepare('SELECT * FROM aulas WHERE id = ? AND estado = "agendada"');
    $stmt->execute([$id]);
    $class = $stmt->fetch();

    return $class ?: null;
}

function getFilteredAdminClasses(PDO $db, array $filters): array
{
    $where  = ['1=1'];
    $params = [];

    if (!empty($filters['trainer'])) {
        $where[]  = 'treinadores.id = ?';
        $params[] = (int)$filters['trainer'];
    }

    if (!empty($filters['gym'])) {
        $where[]  = 'aulas.ginasio_id = ?';
        $params[] = (int)$filters['gym'];
    }

    if (!empty($filters['day'])) {
        $where[]  = 'aulas.dia_semana = ?';
        $params[] = $filters['day'];
    }

    if (!empty($filters['estado'])) {
        $where[]  = 'aulas.estado = ?';
        $params[] = $filters['estado'];
    }

    $stmt = $db->prepare(
        'SELECT aulas.*,
                ginasios.nome AS ginasio_nome,
                utilizadores.nome || " " || utilizadores.apelido AS treinador_nome,
                COALESCE(SUM(CASE WHEN inscricoes_aulas.estado = "inscrito" THEN 1 ELSE 0 END), 0) AS inscritos
         FROM aulas
         JOIN treinadores ON treinadores.id = aulas.treinador_id
         JOIN utilizadores ON utilizadores.id = treinadores.utilizador_id
         JOIN ginasios ON ginasios.id = aulas.ginasio_id
         LEFT JOIN inscricoes_aulas ON inscricoes_aulas.aula_id = aulas.id
         WHERE ' . implode(' AND ', $where) . '
         GROUP BY aulas.id
         ORDER BY
            CASE aulas.estado
                WHEN "agendada" THEN 1
                WHEN "concluida" THEN 2
                ELSE 3
            END,
            CASE aulas.dia_semana
                WHEN "segunda" THEN 1
                WHEN "terca" THEN 2
                WHEN "quarta" THEN 3
                WHEN "quinta" THEN 4
                WHEN "sexta" THEN 5
                WHEN "sabado" THEN 6
                ELSE 7
            END,
            aulas.inicio'
    );
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function getAdminClasses(PDO $db): array
{
    $stmt = $db->prepare(
        'SELECT aulas.*,
                ginasios.nome AS ginasio_nome,
                utilizadores.nome || " " || utilizadores.apelido AS treinador_nome,
                COALESCE(SUM(CASE WHEN inscricoes_aulas.estado = "inscrito" THEN 1 ELSE 0 END), 0) AS inscritos
         FROM aulas
         JOIN treinadores ON treinadores.id = aulas.treinador_id
         JOIN utilizadores ON utilizadores.id = treinadores.utilizador_id
         JOIN ginasios ON ginasios.id = aulas.ginasio_id
         LEFT JOIN inscricoes_aulas ON inscricoes_aulas.aula_id = aulas.id
         GROUP BY aulas.id
         ORDER BY
            CASE aulas.estado
                WHEN "agendada" THEN 1
                WHEN "concluida" THEN 2
                ELSE 3
            END,
            CASE aulas.dia_semana
                WHEN "segunda" THEN 1
                WHEN "terca" THEN 2
                WHEN "quarta" THEN 3
                WHEN "quinta" THEN 4
                WHEN "sexta" THEN 5
                WHEN "sabado" THEN 6
                ELSE 7
            END,
            aulas.inicio'
    );
    $stmt->execute();

    return $stmt->fetchAll();
}

function getAdminClassById(PDO $db, int $id): ?array
{
    $stmt = $db->prepare('SELECT * FROM aulas WHERE id = ?');
    $stmt->execute([$id]);
    $class = $stmt->fetch();

    return $class ?: null;
}

function getActiveTrainers(PDO $db): array
{
    $stmt = $db->prepare(
        'SELECT treinadores.id,
                utilizadores.nome || " " || utilizadores.apelido AS nome
         FROM treinadores
         JOIN utilizadores ON utilizadores.id = treinadores.utilizador_id
         WHERE utilizadores.estado = "ativo"
         ORDER BY nome'
    );
    $stmt->execute();

    return $stmt->fetchAll();
}

function createClass(PDO $db, array $data): int
{
    $stmt = $db->prepare(
        'INSERT INTO aulas
            (nome, tipo, descricao, treinador_id, ginasio_id, dia_semana, inicio, fim, lotacao, sala, estado)
         VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $data['name'],
        $data['type'],
        $data['description'],
        $data['trainer_id'],
        $data['gym_id'],
        $data['day'],
        $data['start'],
        $data['end'],
        $data['capacity'],
        $data['room'],
        $data['status'],
    ]);

    return (int)$db->lastInsertId();
}

function updateClass(PDO $db, int $classId, array $data): bool
{
    $stmt = $db->prepare(
        'UPDATE aulas
         SET nome = ?,
             tipo = ?,
             descricao = ?,
             treinador_id = ?,
             ginasio_id = ?,
             dia_semana = ?,
             inicio = ?,
             fim = ?,
             lotacao = ?,
             sala = ?,
             estado = ?
         WHERE id = ?'
    );

    return $stmt->execute([
        $data['name'],
        $data['type'],
        $data['description'],
        $data['trainer_id'],
        $data['gym_id'],
        $data['day'],
        $data['start'],
        $data['end'],
        $data['capacity'],
        $data['room'],
        $data['status'],
        $classId,
    ]) && $stmt->rowCount() > 0;
}

function getTrainerClassById(PDO $db, int $classId, int $trainerId): ?array
{
    $stmt = $db->prepare('SELECT * FROM aulas WHERE id = ? AND treinador_id = ?');
    $stmt->execute([$classId, $trainerId]);
    return $stmt->fetch() ?: null;
}

function updateTrainerClass(PDO $db, int $classId, int $trainerId, array $data): bool
{
    $stmt = $db->prepare(
        'UPDATE aulas
         SET nome = ?, tipo = ?, descricao = ?, dia_semana = ?,
             inicio = ?, fim = ?, sala = ?, estado = ?
         WHERE id = ? AND treinador_id = ?'
    );
    return $stmt->execute([
        $data['name'], $data['type'], $data['description'], $data['day'],
        $data['start'], $data['end'], $data['room'], $data['status'],
        $classId, $trainerId,
    ]) && $stmt->rowCount() > 0;
}

function removeClassFromCatalog(PDO $db, int $classId): bool
{
    $stmt = $db->prepare(
        'UPDATE aulas
         SET estado = "cancelada"
         WHERE id = ?'
    );

    return $stmt->execute([$classId]) && $stmt->rowCount() > 0;
}
