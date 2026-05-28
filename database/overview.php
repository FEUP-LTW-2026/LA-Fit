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
        'stats'             => $stats,
        'manutencao'        => $manutencao,
        'inativas'          => $inativas,
        'canceladas'        => $canceladas,
        'popular_classes'   => getPopularClasses($db),
        'equipment_usage'   => getEquipmentUsage($db),
        'member_retention'  => getMemberRetentionOverview($db),
    ];
}

function getPopularClasses(PDO $db, int $limit = 3): array
{
    $stmt = $db->prepare(
        'SELECT aulas.id,
                aulas.nome,
                aulas.estado,
                ginasios.nome AS ginasio_nome,
                utilizadores.nome || " " || utilizadores.apelido AS treinador_nome,
                COALESCE(SUM(CASE WHEN inscricoes_aulas.estado IN ("inscrito", "presente") THEN 1 ELSE 0 END), 0) AS inscritos
         FROM aulas
         JOIN treinadores ON treinadores.id = aulas.treinador_id
         JOIN utilizadores ON utilizadores.id = treinadores.utilizador_id
         JOIN ginasios ON ginasios.id = aulas.ginasio_id
         LEFT JOIN inscricoes_aulas ON inscricoes_aulas.aula_id = aulas.id
         WHERE aulas.estado != "cancelada"
         GROUP BY aulas.id
         ORDER BY inscritos DESC, aulas.nome
         LIMIT ?'
    );
    $stmt->execute([$limit]);

    return $stmt->fetchAll();
}

function getEquipmentUsage(PDO $db): array
{
    $stmt = $db->prepare(
        'SELECT estado,
                COUNT(*) AS item_count,
                SUM(quantidade) AS total_quantity
         FROM equipamentos
         GROUP BY estado'
    );
    $stmt->execute();

    $usage = [
        'disponivel' => ['item_count' => 0, 'total_quantity' => 0],
        'ocupado' => ['item_count' => 0, 'total_quantity' => 0],
        'manutencao' => ['item_count' => 0, 'total_quantity' => 0],
    ];

    foreach ($stmt->fetchAll() as $row) {
        $usage[$row['estado']] = [
            'item_count' => (int)$row['item_count'],
            'total_quantity' => (int)$row['total_quantity'],
        ];
    }

    return $usage;
}

function getMemberRetentionOverview(PDO $db): array
{
    $activeMembers = (int)$db->query(
        'SELECT COUNT(*)
         FROM membros
         JOIN utilizadores ON utilizadores.id = membros.utilizador_id
         WHERE utilizadores.estado = "ativo"'
    )->fetchColumn();

    $participatingMembers = (int)$db->query(
        'SELECT COUNT(DISTINCT membros.id)
         FROM inscricoes_aulas
         JOIN membros ON membros.id = inscricoes_aulas.membro_id
         JOIN utilizadores ON utilizadores.id = membros.utilizador_id
         WHERE utilizadores.estado = "ativo"
           AND inscricoes_aulas.estado IN ("inscrito", "presente")'
    )->fetchColumn();

    $recentMembers = (int)$db->query(
        'SELECT COUNT(DISTINCT membros.id)
         FROM inscricoes_aulas
         JOIN membros ON membros.id = inscricoes_aulas.membro_id
         JOIN utilizadores ON utilizadores.id = membros.utilizador_id
         WHERE utilizadores.estado = "ativo"
           AND inscricoes_aulas.estado IN ("inscrito", "presente")
           AND inscricoes_aulas.inscrito_em >= datetime("now", "-30 days")'
    )->fetchColumn();

    $participationRate = $activeMembers > 0 ? round(100.0 * $participatingMembers / $activeMembers, 1) : 0;
    $retentionRate = $activeMembers > 0 ? round(100.0 * $recentMembers / $activeMembers, 1) : 0;

    return [
        'active_members' => $activeMembers,
        'participating_members' => $participatingMembers,
        'recent_members_30d' => $recentMembers,
        'participation_rate' => $participationRate,
        'retention_rate' => $retentionRate,
    ];
}
