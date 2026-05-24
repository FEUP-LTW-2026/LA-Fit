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

function getUserById(PDO $db, int $userId): ?array
{
    $stmt = $db->prepare(
        'SELECT *
         FROM utilizadores
         WHERE id = ?'
    );
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function usernameExistsForOtherUser(PDO $db, string $username, int $userId): bool
{
    $stmt = $db->prepare(
        'SELECT 1
         FROM utilizadores
         WHERE nome_utilizador = ?
           AND id != ?'
    );
    $stmt->execute([$username, $userId]);

    return (bool)$stmt->fetchColumn();
}

function emailExistsForOtherUser(PDO $db, string $email, int $userId): bool
{
    $stmt = $db->prepare(
        'SELECT 1
         FROM utilizadores
         WHERE email = ?
           AND id != ?'
    );
    $stmt->execute([$email, $userId]);

    return (bool)$stmt->fetchColumn();
}

function updateUserProfile(PDO $db, int $userId, array $data): bool
{
    $stmt = $db->prepare(
        'UPDATE utilizadores
         SET nome_utilizador = ?,
             email = ?,
             nome = ?,
             apelido = ?,
             fotografia = ?
         WHERE id = ?'
    );

    return $stmt->execute([
        $data['username'],
        $data['email'],
        $data['first_name'],
        $data['last_name'],
        $data['photo'],
        $userId,
    ]);
}

function updateUserPassword(PDO $db, int $userId, string $password): bool
{
    $stmt = $db->prepare(
        'UPDATE utilizadores
         SET palavra_passe = ?
         WHERE id = ?'
    );

    return $stmt->execute([$password, $userId]);
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

function getTrainerByUsername(PDO $db, string $username): ?array
{
    $stmt = $db->prepare(
        'SELECT treinadores.*,
                utilizadores.nome,
                utilizadores.apelido,
                utilizadores.email,
                utilizadores.nome_utilizador,
                utilizadores.fotografia
         FROM treinadores
         JOIN utilizadores ON utilizadores.id = treinadores.utilizador_id
         WHERE utilizadores.nome_utilizador = ?'
    );
    $stmt->execute([$username]);
    $trainer = $stmt->fetch();

    return $trainer ?: null;
}

function updateTrainerProfile(PDO $db, int $userId, int $trainerId, array $data): void
{
    $db->prepare(
        'UPDATE utilizadores
         SET nome = ?, apelido = ?, email = ?, fotografia = ?
         WHERE id = ?'
    )->execute([$data['first_name'], $data['last_name'], $data['email'], $data['photo'], $userId]);

    $db->prepare(
        'UPDATE treinadores
         SET biografia = ?, especializacoes = ?, certificacoes = ?
         WHERE id = ?'
    )->execute([$data['bio'], $data['specializations'], $data['certifications'], $trainerId]);
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
