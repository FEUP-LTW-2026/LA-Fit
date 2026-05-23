<?php
function getReviewsForMember(PDO $db, int $memberId): array
{
    $stmt = $db->prepare(
        'SELECT *
         FROM avaliacoes
         WHERE membro_id = ?'
    );
    $stmt->execute([$memberId]);

    $reviews = [];
    foreach ($stmt->fetchAll() as $review) {
        $reviews[(int)$review['aula_id']] = $review;
    }

    return $reviews;
}

function memberCanReviewClass(PDO $db, int $memberId, int $classId): bool
{
    $stmt = $db->prepare(
        'SELECT 1
         FROM inscricoes_aulas
         WHERE membro_id = ?
           AND aula_id = ?
           AND estado IN ("inscrito", "presente")
         LIMIT 1'
    );
    $stmt->execute([$memberId, $classId]);

    return (bool)$stmt->fetch();
}

function saveClassReview(PDO $db, int $memberId, int $classId, int $rating, string $comment): bool
{
    if ($rating < 1 || $rating > 5 || !memberCanReviewClass($db, $memberId, $classId)) {
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
