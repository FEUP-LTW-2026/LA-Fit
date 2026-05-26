<?php
function drawAdminPage(array $users, array $plans, array $gyms, ?array $editingUser, array $messages = []): void
{
    $isEditing = $editingUser !== null;
    $formAction = $isEditing ? '../actions/action_admin_update_user.php' : '../actions/action_admin_create_user.php';
    $selectedRole = $editingUser['papel'] ?? 'membro';
    $selectedStatus = $editingUser['estado'] ?? 'ativo';
?>
    <main class="pagina-admin">
        <section class="seccao">
            <div class="conteudo">
                <div class="titulo">
                    <p class="subtitulo">Administração</p>
                    <h1>Gestão de contas</h1>
                    <p>Cria, atualiza e desativa contas de membros e treinadores.</p>
                </div>

                <?php if (!empty($messages['success'])) { ?>
                    <p class="mensagem sucesso"><?= h($messages['success']) ?></p>
                <?php } ?>
                <?php if (!empty($messages['error'])) { ?>
                    <p class="mensagem erro"><?= h($messages['error']) ?></p>
                <?php } ?>

                <section class="painel painel-editar-perfil painel-admin-form">
                    <div class="cabecalho-painel">
                        <h2><?= $isEditing ? 'Editar conta' : 'Criar conta' ?></h2>
                        <?php if ($isEditing) { ?>
                            <a href="admin.php" class="botao claro-voltar">Nova conta</a>
                        <?php } ?>
                    </div>

                    <form action="<?= h($formAction) ?>" method="post">
                        <?php if ($isEditing) { ?>
                            <input type="hidden" name="user_id" value="<?= (int)$editingUser['id'] ?>">
                            <input type="hidden" id="role" name="role" value="<?= h($selectedRole) ?>">
                        <?php } ?>

                        <fieldset class="grupo">
                            <legend>Dados pessoais</legend>
                            <div class="campos">
                                <div class="campo">
                                    <label for="first_name">Primeiro nome</label>
                                    <input type="text" id="first_name" name="first_name" value="<?= h($editingUser['nome'] ?? '') ?>" required>
                                </div>
                                <div class="campo">
                                    <label for="last_name">Apelido</label>
                                    <input type="text" id="last_name" name="last_name" value="<?= h($editingUser['apelido'] ?? '') ?>" required>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="grupo">
                            <legend>Conta</legend>
                            <div class="campos">
                                <div class="campo">
                                    <label for="username">Username</label>
                                    <input type="text" id="username" name="username" value="<?= h($editingUser['nome_utilizador'] ?? '') ?>" required>
                                </div>
                                <div class="campo">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" value="<?= h($editingUser['email'] ?? '') ?>" required>
                                </div>
                                <div class="campo">
                                    <label for="password"><?= $isEditing ? 'Nova palavra-passe' : 'Palavra-passe' ?></label>
                                    <input type="password" id="password" name="password" <?= $isEditing ? '' : 'required' ?>>
                                </div>
                                <div class="campo">
                                    <label for="status">Estado</label>
                                    <select id="status" name="status" required>
                                        <option value="ativo" <?= $selectedStatus === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                        <option value="inativo" <?= $selectedStatus === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                                    </select>
                                </div>
                                <?php if (!$isEditing) { ?>
                                    <div class="campo">
                                        <label for="role">Tipo de conta</label>
                                        <select id="role" name="role" required>
                                            <option value="membro" <?= $selectedRole === 'membro' ? 'selected' : '' ?>>Membro</option>
                                            <option value="treinador" <?= $selectedRole === 'treinador' ? 'selected' : '' ?>>Treinador</option>
                                        </select>
                                    </div>
                                <?php } ?>
                            </div>
                        </fieldset>

                        <fieldset class="grupo" id="fieldset-membro">
                            <legend>Dados de membro</legend>
                            <div class="campos">
                                <div class="campo">
                                    <label for="plan_id">Plano</label>
                                    <select id="plan_id" name="plan_id" required>
                                        <option value="">Sem plano</option>
                                        <?php foreach ($plans as $plan) { ?>
                                            <option value="<?= (int)$plan['id'] ?>" <?= (int)($editingUser['plano_id'] ?? 0) === (int)$plan['id'] ? 'selected' : '' ?>>
                                                <?= h($plan['nome']) ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="campo">
                                    <label for="gym_id">Ginásio</label>
                                    <select id="gym_id" name="gym_id">
                                        <option value="">Sem ginásio</option>
                                        <?php foreach ($gyms as $gym) { ?>
                                            <option value="<?= (int)$gym['id'] ?>" <?= (int)($editingUser['ginasio_id'] ?? 0) === (int)$gym['id'] ? 'selected' : '' ?>>
                                                <?= h($gym['nome']) ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="grupo" id="fieldset-treinador">
                            <legend>Dados de treinador</legend>
                            <div class="campos">
                                <div class="campo campo-largo">
                                    <label for="bio">Biografia</label>
                                    <input type="text" id="bio" name="bio" value="<?= h($editingUser['biografia'] ?? '') ?>">
                                </div>
                                <div class="campo">
                                    <label for="specializations">Especializações</label>
                                    <input type="text" id="specializations" name="specializations" value="<?= h($editingUser['especializacoes'] ?? '') ?>">
                                </div>
                                <div class="campo">
                                    <label for="certifications">Certificações</label>
                                    <input type="text" id="certifications" name="certifications" value="<?= h($editingUser['certificacoes'] ?? '') ?>">
                                </div>
                            </div>
                        </fieldset>

                        <button type="submit" class="botao amarelo"><?= $isEditing ? 'Guardar alterações' : 'Criar conta' ?></button>
                    </form>

                </section>

                <section class="painel painel-admin-lista">
                    <h2>Membros e treinadores</h2>

                    <?php if (count($users) === 0) { ?>
                        <p>Ainda não existem contas para gerir.</p>
                    <?php } else { ?>
                        <div class="tabela-admin-wrap">
                            <table class="tabela-admin">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Detalhe</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user) { ?>
                                        <tr>
                                            <td><?= h($user['nome'] . ' ' . $user['apelido']) ?></td>
                                            <td><?= h($user['nome_utilizador']) ?></td>
                                            <td><?= h($user['email']) ?></td>
                                            <td><?= h($user['papel']) ?></td>
                                            <td><span class="estado-conta estado-conta-<?= h($user['estado']) ?>"><?= h($user['estado']) ?></span></td>
                                            <td>
                                                <?php if ($user['papel'] === 'membro') { ?>
                                                    <?= h($user['plano_nome'] ?? 'Sem plano') ?> · <?= h($user['ginasio_nome'] ?? 'Sem ginásio') ?>
                                                <?php } else { ?>
                                                    <?= h($user['especializacoes'] ?? 'Sem especializações') ?>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <div class="admin-acoes">
                                                    <a href="admin.php?edit=<?= (int)$user['id'] ?>" class="botao claro-voltar">Editar</a>
                                                    <form action="../actions/action_admin_toggle_user.php" method="post" data-confirm="<?= $user['estado'] === 'ativo' ? 'Tens a certeza que queres desativar esta conta?' : 'Tens a certeza que queres ativar esta conta?' ?>">
                                                        <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                                                        <input type="hidden" name="status" value="<?= $user['estado'] === 'ativo' ? 'inativo' : 'ativo' ?>">
                                                        <button type="submit" class="botao cliente">
                                                            <?= $user['estado'] === 'ativo' ? 'Desativar' : 'Ativar' ?>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                </section>
            </div>
        </section>
    </main>
<?php
}
