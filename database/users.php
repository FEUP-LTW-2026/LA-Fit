<?php
function getUserByLoginAndPassword(PDO $db, string $login, string $password): ?array
{
    $stmt = $db->prepare(
        'SELECT *
         FROM utilizadores
         WHERE (nome_utilizador = ? OR email = ?)
           AND palavra_passe = ?
           AND estado = "ativo"'
    );
    $stmt->execute([$login, $login, $password]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function getUserByUsername(PDO $db, string $username): ?array
{
    $stmt = $db->prepare(
        'SELECT *
         FROM utilizadores
         WHERE nome_utilizador = ?'
    );
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function getMemberByUsername(PDO $db, string $username): ?array
{
    $stmt = $db->prepare(
        'SELECT membros.id AS membro_id,
                membros.*,
                planos.nome AS plano_nome,
                planos.preco_mensal,
                ginasios.nome AS ginasio_nome
         FROM membros
         JOIN utilizadores ON utilizadores.id = membros.utilizador_id
         LEFT JOIN planos ON planos.id = membros.plano_id
         LEFT JOIN ginasios ON ginasios.id = membros.ginasio_id
         WHERE utilizadores.nome_utilizador = ?'
    );
    $stmt->execute([$username]);
    $member = $stmt->fetch();

    return $member ?: null;
}

function createMemberUser(PDO $db, array $data): int
{
    $db->beginTransaction();

    try {
        $stmt = $db->prepare(
            'INSERT INTO utilizadores
                (nome_utilizador, email, palavra_passe, nome, apelido, papel, estado)
             VALUES
                (?, ?, ?, ?, ?, "membro", "ativo")'
        );
        $stmt->execute([
            $data['username'],
            $data['email'],
            $data['password'],
            $data['first_name'],
            $data['last_name'],
        ]);

        $userId = (int)$db->lastInsertId();

        $stmt = $db->prepare(
            'INSERT INTO membros
                (utilizador_id, data_nascimento, telefone, morada, cidade, codigo_postal, plano_id, ginasio_id)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $data['birth_date'],
            $data['phone'],
            $data['address'],
            $data['city'],
            $data['postal_code'],
            $data['plan_id'],
            $data['gym_id'],
        ]);

        $db->commit();
        return $userId;
    } catch (Exception $exception) {
        $db->rollBack();
        throw $exception;
    }
}
