<?php
function getReviewableClassesForMember(PDO $db, int $memberId): array
{
    $stmt = $db->prepare(
        'SELECT aulas.tipo,
                MIN(aulas.id) AS id,
                MIN(aulas.nome) AS nome,
                (SELECT av.classificacao
                 FROM avaliacoes av
                 JOIN aulas a2 ON a2.id = av.aula_id
                 WHERE a2.tipo = aulas.tipo AND av.membro_id = inscricoes_aulas.membro_id
                 LIMIT 1) AS classificacao,
                (SELECT av.comentario
                 FROM avaliacoes av
                 JOIN aulas a2 ON a2.id = av.aula_id
                 WHERE a2.tipo = aulas.tipo AND av.membro_id = inscricoes_aulas.membro_id
                 LIMIT 1) AS comentario
         FROM inscricoes_aulas
         JOIN aulas ON aulas.id = inscricoes_aulas.aula_id
         WHERE inscricoes_aulas.membro_id = ?
           AND inscricoes_aulas.estado IN ("inscrito", "presente")
         GROUP BY aulas.tipo
         ORDER BY aulas.tipo'
    );
    $stmt->execute([$memberId]);

    return $stmt->fetchAll();
}

function memberCanReviewClass(PDO $db, int $memberId, int $classId): bool
{
    $stmt = $db->prepare(
        'SELECT 1
         FROM inscricoes_aulas
         JOIN aulas ON aulas.id = inscricoes_aulas.aula_id
         WHERE inscricoes_aulas.membro_id = ?
           AND aulas.tipo = (SELECT tipo FROM aulas WHERE id = ?)
           AND inscricoes_aulas.estado IN ("inscrito", "presente")
         LIMIT 1'
    );
    $stmt->execute([$memberId, $classId]);

    return (bool)$stmt->fetch();
}

function getClassReviews(PDO $db, int $classId): array
{
    $stmt = $db->prepare(
        'SELECT avaliacoes.classificacao,
                avaliacoes.comentario,
                avaliacoes.criada_em,
                utilizadores.nome,
                utilizadores.apelido
         FROM avaliacoes
         JOIN aulas ON aulas.id = avaliacoes.aula_id
         JOIN membros ON membros.id = avaliacoes.membro_id
         JOIN utilizadores ON utilizadores.id = membros.utilizador_id
         WHERE aulas.tipo = (SELECT tipo FROM aulas WHERE id = ?)
         ORDER BY avaliacoes.criada_em DESC'
    );
    $stmt->execute([$classId]);

    return $stmt->fetchAll();
}

function saveClassReview(PDO $db, int $memberId, int $classId, int $rating, string $comment): bool
{
    if ($rating < 1 || $rating > 10 || !memberCanReviewClass($db, $memberId, $classId)) {
        return false;
    }

    $existing = $db->prepare(
        'SELECT av.aula_id
         FROM avaliacoes av
         JOIN aulas ON aulas.id = av.aula_id
         WHERE av.membro_id = ?
           AND aulas.tipo = (SELECT tipo FROM aulas WHERE id = ?)
         LIMIT 1'
    );
    $existing->execute([$memberId, $classId]);
    $row = $existing->fetch();

    if ($row) {
        $stmt = $db->prepare(
            'UPDATE avaliacoes
             SET classificacao = ?, comentario = ?, criada_em = CURRENT_TIMESTAMP
             WHERE membro_id = ? AND aula_id = ?'
        );
        return $stmt->execute([$rating, $comment, $memberId, $row['aula_id']]);
    }

    $stmt = $db->prepare(
        'INSERT INTO avaliacoes (membro_id, aula_id, classificacao, comentario) VALUES (?, ?, ?, ?)'
    );
    return $stmt->execute([$memberId, $classId, $rating, $comment]);
}
