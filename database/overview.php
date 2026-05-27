<?php
function getSystemOverview(PDO $db): array
{
    $stats = $db->query(
        'SELECT
            (SELECT COUNT(*) FROM utilizadores WHERE papel = "membro"     AND estado = "ativo")   AS membros_ativos,
            (SELECT COUNT(*) FROM utilizadores WHERE papel = "treinador"  AND estado = "ativo")   AS treinadores_ativos,
            (SELECT COUNT(*) FROM utilizadores                            WHERE estado = "inativo") AS contas_inativas,
            (SELECT COUNT(*) FROM aulas        WHERE estado = "agendada")                         AS aulas_agendadas,
            (SELECT COUNT(*) FROM aulas        WHERE estado = "cancelada")                        AS aulas_canceladas,
            (SELECT COUNT(*) FROM relatorios   WHERE estado = "pendente")                         AS reportes_pendentes,
            (SELECT COUNT(*) FROM relatorios   WHERE estado = "em_analise")                       AS reportes_em_analise,
            (SELECT COUNT(*) FROM equipamentos WHERE estado = "manutencao")                       AS equipamentos_manutencao'
    )->fetch();

    $manutencao = $db->query(
        'SELECT id, nome, zona, quantidade
         FROM equipamentos
         WHERE estado = "manutencao"
         ORDER BY zona, nome'
    )->fetchAll();

    $inativas = $db->query(
        'SELECT id, nome, apelido, nome_utilizador, papel
         FROM utilizadores
         WHERE estado = "inativo"
         ORDER BY papel, nome, apelido'
    )->fetchAll();

    $canceladas = $db->query(
        'SELECT aulas.id, aulas.nome, aulas.dia_semana, aulas.inicio, aulas.fim,
                utilizadores.nome || " " || utilizadores.apelido AS treinador_nome
         FROM aulas
         JOIN treinadores ON treinadores.id = aulas.treinador_id
         JOIN utilizadores ON utilizadores.id = treinadores.utilizador_id
         WHERE aulas.estado = "cancelada"
         ORDER BY aulas.dia_semana, aulas.inicio'
    )->fetchAll();

    return [
        'stats'     => $stats,
        'manutencao' => $manutencao,
        'inativas'   => $inativas,
        'canceladas' => $canceladas,
    ];
}
