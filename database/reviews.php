<?php
function getReviewableClassesForMember(PDO $db, int $memberId): array
{
    $stmt = $db->prepare(
        'SELECT aulas.*,
                ginasios.nome AS ginasio_nome,
                utilizadores.nome || " " || utilizadores.apelido AS treinador_nome,
                avaliacoes.classificacao,
                avaliacoes.comentario
         FROM inscricoes_aulas
         JOIN aulas ON aulas.id = inscricoes_aulas.aula_id
         JOIN ginasios ON ginasios.id = aulas.ginasio_id
         JOIN treinadores ON treinadores.id = aulas.treinador_id
         JOIN utilizadores ON utilizadores.id = treinadores.utilizador_id
         LEFT JOIN avaliacoes
            ON avaliacoes.aula_id = aulas.id
           AND avaliacoes.membro_id = inscricoes_aulas.membro_id
         WHERE inscricoes_aulas.membro_id = ?
           AND inscricoes_aulas.estado = "presente"
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
    $stmt->execute([$memberId]);

    return $stmt->fetchAll();
}

function memberCanReviewClass(PDO $db, int $memberId, int $classId): bool
{
    $stmt = $db->prepare(
        'SELECT 1
         FROM inscricoes_aulas
         WHERE membro_id = ?
           AND aula_id = ?
           AND estado = "presente"
         LIMIT 1'
    );
    $stmt->execute([$memberId, $classId]);

    return (bool)$stmt->fetch();
}

function saveClassReview(PDO $db, int $memberId, int $classId, int $rating, string $comment): bool
{
    if ($rating < 1 || $rating > 10 || !memberCanReviewClass($db, $memberId, $classId)) {
        return false;
    }

    $stmt = $db->prepare(
        'INSERT INTO avaliacoes (membro_id, aula_id, classificacao, comentario)
         VALUES (?, ?, ?, ?)
         ON CONFLICT(membro_id, aula_id) DO UPDATE SET
            classificacao = excluded.classificacao,
            comentario = excluded.comentario,
            criada_em = CURRENT_TIMESTAMP'
    );

    return $stmt->execute([$memberId, $classId, $rating, $comment]);
}
