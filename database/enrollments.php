<?php
function getMemberIdForUsername(PDO $db, string $username): ?int
{
    $stmt = $db->prepare(
        'SELECT membros.id
         FROM membros
         JOIN utilizadores ON utilizadores.id = membros.utilizador_id
         WHERE utilizadores.nome_utilizador = ?'
    );
    $stmt->execute([$username]);
    $member = $stmt->fetch();

    return $member ? (int)$member['id'] : null;
}

function getEnrollmentsForUsername(PDO $db, string $username): array
{
    $stmt = $db->prepare(
        'SELECT inscricoes_aulas.id AS inscricao_id,
                inscricoes_aulas.estado AS inscricao_estado,
                aulas.*,
                ginasios.nome AS ginasio_nome,
                utilizadores.nome || " " || utilizadores.apelido AS treinador_nome
         FROM inscricoes_aulas
         JOIN membros ON membros.id = inscricoes_aulas.membro_id
         JOIN utilizadores membro_user ON membro_user.id = membros.utilizador_id
         JOIN aulas ON aulas.id = inscricoes_aulas.aula_id
         JOIN ginasios ON ginasios.id = aulas.ginasio_id
         JOIN treinadores ON treinadores.id = aulas.treinador_id
         JOIN utilizadores ON utilizadores.id = treinadores.utilizador_id
         WHERE membro_user.nome_utilizador = ?
           AND inscricoes_aulas.estado IN ("inscrito", "presente")
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
    $stmt->execute([$username]);

    return $stmt->fetchAll();
}

function getEnrolledClassIdsForUsername(PDO $db, string $username): array
{
    $enrollments = getEnrollmentsForUsername($db, $username);
    $ids = [];

    foreach ($enrollments as $enrollment) {
        $ids[] = (int)$enrollment['id'];
    }

    return $ids;
}

function getEnrolledMembersForClass(PDO $db, int $classId, int $trainerId): ?array
{
    $stmt = $db->prepare(
        'SELECT 1 FROM aulas
         JOIN treinadores ON treinadores.id = aulas.treinador_id
         WHERE aulas.id = ? AND treinadores.id = ?'
    );
    $stmt->execute([$classId, $trainerId]);

    if (!$stmt->fetch()) {
        return null;
    }

    $stmt = $db->prepare(
        'SELECT utilizadores.nome,
                utilizadores.apelido,
                utilizadores.nome_utilizador,
                planos.nome AS plano_nome,
                inscricoes_aulas.inscrito_em
         FROM inscricoes_aulas
         JOIN membros ON membros.id = inscricoes_aulas.membro_id
         JOIN utilizadores ON utilizadores.id = membros.utilizador_id
         LEFT JOIN planos ON planos.id = membros.plano_id
         WHERE inscricoes_aulas.aula_id = ?
           AND inscricoes_aulas.estado IN ("inscrito", "presente")
         ORDER BY utilizadores.nome, utilizadores.apelido'
    );
    $stmt->execute([$classId]);

    return $stmt->fetchAll();
}

function classHasAvailablePlace(PDO $db, int $classId): bool
{
    $stmt = $db->prepare(
        'SELECT aulas.lotacao - COALESCE(SUM(CASE WHEN inscricoes_aulas.estado = "inscrito" THEN 1 ELSE 0 END), 0) AS vagas
         FROM aulas
         LEFT JOIN inscricoes_aulas ON inscricoes_aulas.aula_id = aulas.id
         WHERE aulas.id = ?
           AND aulas.estado = "agendada"
         GROUP BY aulas.id'
    );
    $stmt->execute([$classId]);
    $class = $stmt->fetch();

    return $class && (int)$class['vagas'] > 0;
}

function enrollMemberInClass(PDO $db, int $memberId, int $classId): bool
{
    $db->beginTransaction();

    $stmt = $db->prepare(
        'SELECT aulas.lotacao,
                COALESCE(SUM(CASE WHEN inscricoes_aulas.estado = "inscrito" THEN 1 ELSE 0 END), 0) AS inscritos,
                MAX(CASE WHEN inscricoes_aulas.membro_id = ? THEN inscricoes_aulas.estado ELSE NULL END) AS estado_membro
         FROM aulas
         LEFT JOIN inscricoes_aulas ON inscricoes_aulas.aula_id = aulas.id
         WHERE aulas.id = ?
           AND aulas.estado = "agendada"
         GROUP BY aulas.id'
    );
    $stmt->execute([$memberId, $classId]);
    $class = $stmt->fetch();

    if (!$class) {
        $db->rollBack();
        return false;
    }

    if (($class['estado_membro'] ?? null) !== 'inscrito' && (int)$class['inscritos'] >= (int)$class['lotacao']) {
        $db->rollBack();
        return false;
    }

    $stmt = $db->prepare(
        'INSERT INTO inscricoes_aulas (membro_id, aula_id, estado, cancelado_em)
         VALUES (?, ?, "inscrito", NULL)
         ON CONFLICT(membro_id, aula_id) DO UPDATE SET
            estado = "inscrito",
            inscrito_em = CURRENT_TIMESTAMP,
            cancelado_em = NULL'
    );

    if (!$stmt->execute([$memberId, $classId])) {
        $db->rollBack();
        return false;
    }

    return $db->commit();
}

function cancelEnrollment(PDO $db, int $memberId, int $classId): bool
{
    $stmt = $db->prepare(
        'UPDATE inscricoes_aulas
         SET estado = "cancelado",
             cancelado_em = CURRENT_TIMESTAMP
         WHERE membro_id = ?
           AND aula_id = ?
           AND estado = "inscrito"
           AND EXISTS (
                SELECT 1
                FROM aulas
                WHERE aulas.id = inscricoes_aulas.aula_id
                  AND aulas.estado = "agendada"
           )'
    );

    return $stmt->execute([$memberId, $classId]);
}
