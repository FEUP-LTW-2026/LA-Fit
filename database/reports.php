<?php
function createReport(PDO $db, int $userId, string $tipo, string $assunto, string $descricao): bool
{
    $stmt = $db->prepare(
        'INSERT INTO relatorios (utilizador_id, tipo, assunto, descricao)
         VALUES (?, ?, ?, ?)'
    );

    return $stmt->execute([$userId, $tipo, $assunto, $descricao]);
}

function getAllReports(PDO $db): array
{
    $stmt = $db->query(
        'SELECT relatorios.*,
                utilizadores.nome || " " || utilizadores.apelido AS membro_nome,
                utilizadores.nome_utilizador
         FROM relatorios
         JOIN utilizadores ON utilizadores.id = relatorios.utilizador_id
         ORDER BY
            CASE relatorios.estado WHEN "pendente" THEN 0 WHEN "em_analise" THEN 1 ELSE 2 END,
            relatorios.criado_em DESC'
    );

    return $stmt->fetchAll();
}

function getMemberReports(PDO $db, int $userId): array
{
    $stmt = $db->prepare(
        'SELECT * FROM relatorios
         WHERE utilizador_id = ?
         ORDER BY criado_em DESC'
    );
    $stmt->execute([$userId]);

    return $stmt->fetchAll();
}

function respondToReport(PDO $db, int $reportId, string $estado, string $resposta): bool
{
    $stmt = $db->prepare(
        'UPDATE relatorios
         SET estado = ?, resposta_admin = ?, atualizado_em = CURRENT_TIMESTAMP
         WHERE id = ?'
    );

    return $stmt->execute([$estado, $resposta, $reportId]);
}
