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

function getManageableUsers(PDO $db): array
{
    $stmt = $db->prepare(
        'SELECT utilizadores.*,
                membros.id AS membro_id,
                membros.plano_id,
                membros.ginasio_id,
                planos.nome AS plano_nome,
                ginasios.nome AS ginasio_nome,
                treinadores.id AS treinador_id,
                treinadores.biografia,
                treinadores.especializacoes,
                treinadores.certificacoes
         FROM utilizadores
         LEFT JOIN membros ON membros.utilizador_id = utilizadores.id
         LEFT JOIN planos ON planos.id = membros.plano_id
         LEFT JOIN ginasios ON ginasios.id = membros.ginasio_id
         LEFT JOIN treinadores ON treinadores.utilizador_id = utilizadores.id
         WHERE utilizadores.papel IN ("membro", "treinador")
         ORDER BY utilizadores.papel, utilizadores.nome, utilizadores.apelido'
    );
    $stmt->execute();

    return $stmt->fetchAll();
}

function createManagedUser(PDO $db, array $data): int
{
    $db->beginTransaction();

    try {
        $stmt = $db->prepare(
            'INSERT INTO utilizadores
                (nome_utilizador, email, palavra_passe, nome, apelido, papel, estado)
             VALUES
                (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['username'],
            $data['email'],
            $data['password'],
            $data['first_name'],
            $data['last_name'],
            $data['role'],
            $data['status'],
        ]);

        $userId = (int)$db->lastInsertId();

        if ($data['role'] === 'membro') {
            $stmt = $db->prepare(
                'INSERT INTO membros (utilizador_id, plano_id, ginasio_id)
                 VALUES (?, ?, ?)'
            );
            $stmt->execute([$userId, $data['plan_id'], $data['gym_id']]);
        } elseif ($data['role'] === 'treinador') {
            $stmt = $db->prepare(
                'INSERT INTO treinadores (utilizador_id, biografia, especializacoes, certificacoes)
                 VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([
                $userId,
                $data['bio'],
                $data['specializations'],
                $data['certifications'],
            ]);
        }

        $db->commit();
        return $userId;
    } catch (Exception $exception) {
        $db->rollBack();
        throw $exception;
    }
}

function updateManagedUser(PDO $db, int $userId, array $data): bool
{
    $db->beginTransaction();

    try {
        $stmt = $db->prepare(
            'UPDATE utilizadores
             SET nome_utilizador = ?,
                 email = ?,
                 nome = ?,
                 apelido = ?,
                 estado = ?
             WHERE id = ?
               AND papel IN ("membro", "treinador")'
        );
        $stmt->execute([
            $data['username'],
            $data['email'],
            $data['first_name'],
            $data['last_name'],
            $data['status'],
            $userId,
        ]);

        if (!empty($data['password'])) {
            updateUserPassword($db, $userId, $data['password']);
        }

        if ($data['role'] === 'membro') {
            $stmt = $db->prepare(
                'UPDATE membros
                 SET plano_id = ?, ginasio_id = ?
                 WHERE utilizador_id = ?'
            );
            $stmt->execute([$data['plan_id'], $data['gym_id'], $userId]);
        } elseif ($data['role'] === 'treinador') {
            $stmt = $db->prepare(
                'UPDATE treinadores
                 SET biografia = ?, especializacoes = ?, certificacoes = ?
                 WHERE utilizador_id = ?'
            );
            $stmt->execute([
                $data['bio'],
                $data['specializations'],
                $data['certifications'],
                $userId,
            ]);
        }

        $db->commit();
        return true;
    } catch (Exception $exception) {
        $db->rollBack();
        throw $exception;
    }
}

function setManagedUserStatus(PDO $db, int $userId, string $status): bool
{
    $stmt = $db->prepare(
        'UPDATE utilizadores
         SET estado = ?
         WHERE id = ?
           AND papel IN ("membro", "treinador")'
    );

    return $stmt->execute([$status, $userId]) && $stmt->rowCount() > 0;
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

function getTrainerById(PDO $db, int $trainerId): ?array
{
    $stmt = $db->prepare(
        'SELECT treinadores.*,
                utilizadores.nome,
                utilizadores.apelido,
                utilizadores.fotografia
         FROM treinadores
         JOIN utilizadores ON utilizadores.id = treinadores.utilizador_id
         WHERE treinadores.id = ?'
    );
    $stmt->execute([$trainerId]);
    $trainer = $stmt->fetch();

    return $trainer ?: null;
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

function elevateUserToAdmin(PDO $db, int $userId): bool
{
    $db->beginTransaction();

    try {
        $stmt = $db->prepare(
            'UPDATE utilizadores
             SET papel = "administrador"
             WHERE id = ?
               AND papel IN ("membro", "treinador")'
        );
        $stmt->execute([$userId]);

        if ($stmt->rowCount() === 0) {
            $db->rollBack();
            return false;
        }

        $db->prepare(
            'INSERT OR IGNORE INTO administradores (utilizador_id) VALUES (?)'
        )->execute([$userId]);

        $db->commit();
        return true;
    } catch (Exception $exception) {
        $db->rollBack();
        throw $exception;
    }
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
