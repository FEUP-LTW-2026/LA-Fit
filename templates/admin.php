<?php
function drawAdminPage(array $users, array $plans, array $gyms, ?array $editingUser, array $classes, array $trainers, ?array $editingClass, array $messages = []): void
{
    $isEditing = $editingUser !== null;
    $isEditingClass = $editingClass !== null;
    $formAction = $isEditing ? '../actions/action_admin_update_user.php' : '../actions/action_admin_create_user.php';
    $classFormAction = $isEditingClass ? '../actions/action_admin_update_class.php' : '../actions/action_admin_create_class.php';
    $selectedRole = $editingUser['papel'] ?? 'membro';
    $selectedStatus = $editingUser['estado'] ?? 'ativo';
    $selectedClassStatus = $editingClass['estado'] ?? 'agendada';
    $selectedDay = $editingClass['dia_semana'] ?? 'segunda';
    $days = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
?>
    <main class="pagina-admin">
        <section class="seccao">
            <div class="conteudo">
                <div class="titulo">
                    <p class="subtitulo">Administração</p>
                    <h1>Gestão do sistema</h1>
                    <p>Gere contas, aulas, treinadores atribuídos e o catálogo público.</p>
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
                            <input type="hidden" name="role" value="<?= h($selectedRole) ?>">
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

                        <fieldset class="grupo">
                            <legend>Dados de membro</legend>
                            <div class="campos">
                                <div class="campo">
                                    <label for="plan_id">Plano</label>
                                    <select id="plan_id" name="plan_id">
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

                        <fieldset class="grupo">
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
                                                    <form action="../actions/action_admin_toggle_user.php" method="post">
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

                <section class="painel painel-editar-perfil painel-admin-form" id="aulas-admin">
                    <div class="cabecalho-painel">
                        <h2><?= $isEditingClass ? 'Editar aula' : 'Criar aula' ?></h2>
                        <?php if ($isEditingClass) { ?>
                            <a href="admin.php#aulas-admin" class="botao claro-voltar">Nova aula</a>
                        <?php } ?>
                    </div>

                    <form action="<?= h($classFormAction) ?>" method="post">
                        <?php if ($isEditingClass) { ?>
                            <input type="hidden" name="class_id" value="<?= (int)$editingClass['id'] ?>">
                        <?php } ?>

                        <fieldset class="grupo">
                            <legend>Dados da aula</legend>
                            <div class="campos">
                                <div class="campo">
                                    <label for="class_name">Nome</label>
                                    <input type="text" id="class_name" name="name" value="<?= h($editingClass['nome'] ?? '') ?>" required>
                                </div>
                                <div class="campo">
                                    <label for="class_type">Tipo</label>
                                    <input type="text" id="class_type" name="type" value="<?= h($editingClass['tipo'] ?? '') ?>" required>
                                </div>
                                <div class="campo campo-largo">
                                    <label for="class_description">Descrição</label>
                                    <input type="text" id="class_description" name="description" value="<?= h($editingClass['descricao'] ?? '') ?>">
                                </div>
                                <div class="campo">
                                    <label for="class_trainer">Treinador</label>
                                    <select id="class_trainer" name="trainer_id" required>
                                        <option value="">Escolher treinador</option>
                                        <?php foreach ($trainers as $trainer) { ?>
                                            <option value="<?= (int)$trainer['id'] ?>" <?= (int)($editingClass['treinador_id'] ?? 0) === (int)$trainer['id'] ? 'selected' : '' ?>>
                                                <?= h($trainer['nome']) ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="campo">
                                    <label for="class_gym">Ginásio</label>
                                    <select id="class_gym" name="gym_id" required>
                                        <option value="">Escolher ginásio</option>
                                        <?php foreach ($gyms as $gym) { ?>
                                            <option value="<?= (int)$gym['id'] ?>" <?= (int)($editingClass['ginasio_id'] ?? 0) === (int)$gym['id'] ? 'selected' : '' ?>>
                                                <?= h($gym['nome']) ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="grupo">
                            <legend>Horário</legend>
                            <div class="campos">
                                <div class="campo">
                                    <label for="class_day">Dia</label>
                                    <select id="class_day" name="day" required>
                                        <?php foreach ($days as $day) { ?>
                                            <option value="<?= h($day) ?>" <?= $selectedDay === $day ? 'selected' : '' ?>>
                                                <?= h(formatClassDay($day)) ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="campo">
                                    <label for="class_start">Início</label>
                                    <input type="time" id="class_start" name="start" value="<?= h($editingClass['inicio'] ?? '') ?>" required>
                                </div>
                                <div class="campo">
                                    <label for="class_end">Fim</label>
                                    <input type="time" id="class_end" name="end" value="<?= h($editingClass['fim'] ?? '') ?>" required>
                                </div>
                                <div class="campo">
                                    <label for="class_capacity">Lotação</label>
                                    <input type="number" id="class_capacity" name="capacity" min="1" value="<?= h($editingClass['lotacao'] ?? '') ?>" required>
                                </div>
                                <div class="campo">
                                    <label for="class_room">Sala</label>
                                    <input type="text" id="class_room" name="room" value="<?= h($editingClass['sala'] ?? '') ?>" required>
                                </div>
                                <div class="campo">
                                    <label for="class_status">Estado</label>
                                    <select id="class_status" name="status" required>
                                        <option value="agendada" <?= $selectedClassStatus === 'agendada' ? 'selected' : '' ?>>Agendada</option>
                                        <option value="cancelada" <?= $selectedClassStatus === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                                        <option value="concluida" <?= $selectedClassStatus === 'concluida' ? 'selected' : '' ?>>Concluída</option>
                                    </select>
                                </div>
                            </div>
                        </fieldset>

                        <button type="submit" class="botao amarelo"><?= $isEditingClass ? 'Guardar aula' : 'Criar aula' ?></button>
                    </form>
                </section>

                <section class="painel painel-admin-lista">
                    <h2>Catálogo de aulas</h2>

                    <?php if (count($classes) === 0) { ?>
                        <p>Ainda não existem aulas no catálogo.</p>
                    <?php } else { ?>
                        <div class="tabela-admin-wrap">
                            <table class="tabela-admin tabela-admin-aulas">
                                <thead>
                                    <tr>
                                        <th>Aula</th>
                                        <th>Treinador</th>
                                        <th>Ginásio</th>
                                        <th>Horário</th>
                                        <th>Lotação</th>
                                        <th>Estado</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($classes as $class) { ?>
                                        <tr>
                                            <td>
                                                <strong><?= h($class['nome']) ?></strong><br>
                                                <span><?= h($class['tipo']) ?> · <?= h($class['sala']) ?></span>
                                            </td>
                                            <td><?= h($class['treinador_nome']) ?></td>
                                            <td><?= h($class['ginasio_nome']) ?></td>
                                            <td><?= h(formatClassDay($class['dia_semana'])) ?> · <?= h($class['inicio']) ?> - <?= h($class['fim']) ?></td>
                                            <td><?= (int)$class['inscritos'] ?>/<?= (int)$class['lotacao'] ?></td>
                                            <td><span class="estado-conta estado-aula-admin-<?= h($class['estado']) ?>"><?= h($class['estado']) ?></span></td>
                                            <td>
                                                <div class="admin-acoes">
                                                    <a href="admin.php?class_edit=<?= (int)$class['id'] ?>#aulas-admin" class="botao claro-voltar">Editar</a>
                                                    <?php if ($class['estado'] !== 'cancelada') { ?>
                                                        <form action="../actions/action_admin_remove_class.php" method="post">
                                                            <input type="hidden" name="class_id" value="<?= (int)$class['id'] ?>">
                                                            <button type="submit" class="botao cliente">Remover</button>
                                                        </form>
                                                    <?php } ?>
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
