<?php
function getAllClasses(PDO $db): array
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
         WHERE aulas.estado = "agendada"
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
    $stmt->execute();

    return $stmt->fetchAll();
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
