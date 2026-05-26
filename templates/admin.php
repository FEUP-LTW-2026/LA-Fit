<?php
function drawAdminPage(array $users, array $plans, array $gyms, array $classes, array $trainers, array $reports, ?array $editingUser, ?array $editingClass, array $messages = []): void
{
    $isEditing = $editingUser !== null;
    $isEditingClass = $editingClass !== null;
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

                <?php if (!$isEditingClass) { ?>
                <section class="painel painel-editar-perfil painel-form">
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
                <?php } ?>

                <?php if (!$isEditing) { drawAdminClassCatalog($classes, $trainers, $gyms, $editingClass); } ?>

                <?php if (!$isEditing && !$isEditingClass) { ?>
                <section class="painel painel-lista">
                    <h2>Membros e treinadores</h2>

                    <?php if (count($users) === 0) { ?>
                        <p>Ainda não existem contas para gerir.</p>
                    <?php } else { ?>
                        <div class="tabela-wrap">
                            <table class="tabela">
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
                                                <div class="acoes-linha">
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
                <?php } ?>

                <?php if (!$isEditing && !$isEditingClass) { drawAdminReports($reports); } ?>
            </div>
        </section>
    </main>
<?php
}

function drawAdminReports(array $reports): void
{
    $estadoLabel = ['pendente' => 'Pendente', 'em_analise' => 'Em análise', 'resolvido' => 'Resolvido'];
    $tipoLabel   = ['equipamento' => 'Equipamento', 'aula' => 'Aula', 'outro' => 'Outro'];
    $pendentes   = count(array_filter($reports, fn($r) => $r['estado'] === 'pendente'));
?>
    <section class="painel painel-lista">
        <h2>Reportes <?php if ($pendentes > 0) { ?><span class="faixa"><?= $pendentes ?> pendente<?= $pendentes > 1 ? 's' : '' ?></span><?php } ?></h2>

        <?php if (count($reports) === 0) { ?>
            <p>Não há reportes submetidos.</p>
        <?php } else { ?>
            <div class="lista-reportes">
                <?php foreach ($reports as $report) { ?>
                    <article class="reporte">
                        <div class="reporte-cabecalho">
                            <div>
                                <span class="faixa"><?= h($tipoLabel[$report['tipo']] ?? $report['tipo']) ?></span>
                                <h3><?= h($report['assunto']) ?></h3>
                                <p class="reporte-meta"><?= h($report['membro_nome']) ?> · <?= h(substr($report['criado_em'], 0, 10)) ?></p>
                                <p class="reporte-descricao"><?= h($report['descricao']) ?></p>
                            </div>
                            <span class="estado-reporte estado-reporte-<?= h($report['estado']) ?>">
                                <?= h($estadoLabel[$report['estado']] ?? $report['estado']) ?>
                            </span>
                        </div>

                        <?php if (!empty($report['resposta_admin'])) { ?>
                            <div class="reporte-resposta">
                                <p><strong>Resposta:</strong> <?= h($report['resposta_admin']) ?></p>
                            </div>
                        <?php } ?>

                        <form action="../actions/action_admin_respond_report.php" method="post" class="reporte-form">
                            <input type="hidden" name="report_id" value="<?= (int)$report['id'] ?>">
                            <div class="campos">
                                <div class="campo campo-largo">
                                    <label for="resposta-<?= (int)$report['id'] ?>">Resposta</label>
                                    <textarea id="resposta-<?= (int)$report['id'] ?>" name="resposta" rows="2"><?= h($report['resposta_admin'] ?? '') ?></textarea>
                                </div>
                                <div class="campo">
                                    <label for="estado-<?= (int)$report['id'] ?>">Estado</label>
                                    <select id="estado-<?= (int)$report['id'] ?>" name="estado" required>
                                        <option value="pendente" <?= $report['estado'] === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                        <option value="em_analise" <?= $report['estado'] === 'em_analise' ? 'selected' : '' ?>>Em análise</option>
                                        <option value="resolvido" <?= $report['estado'] === 'resolvido' ? 'selected' : '' ?>>Resolvido</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="botao claro-voltar">Guardar resposta</button>
                        </form>
                    </article>
                <?php } ?>
            </div>
        <?php } ?>
    </section>
<?php
}

function drawAdminClassCatalog(array $classes, array $trainers, array $gyms, ?array $editingClass): void
{
    $isEditing = $editingClass !== null;
    $formAction = $isEditing ? '../actions/action_admin_update_class.php' : '../actions/action_admin_create_class.php';
    $days = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
    $statuses = ['agendada' => 'Agendada', 'concluida' => 'Concluída', 'cancelada' => 'Cancelada'];
?>
    <section class="painel painel-editar-perfil painel-form">
        <div class="cabecalho-painel">
            <h2><?= $isEditing ? 'Editar aula' : 'Criar aula' ?></h2>
            <?php if ($isEditing) { ?>
                <a href="admin.php" class="botao claro-voltar">Nova aula</a>
            <?php } ?>
        </div>

        <form action="<?= h($formAction) ?>" method="post">
            <?php if ($isEditing) { ?>
                <input type="hidden" name="class_id" value="<?= (int)$editingClass['id'] ?>">
            <?php } ?>

            <fieldset class="grupo">
                <legend>Catálogo de aulas</legend>
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
                        <textarea id="class_description" name="description" rows="3"><?= h($editingClass['descricao'] ?? '') ?></textarea>
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
                    <div class="campo">
                        <label for="class_day">Dia</label>
                        <select id="class_day" name="day" required>
                            <?php foreach ($days as $day) { ?>
                                <option value="<?= h($day) ?>" <?= ($editingClass['dia_semana'] ?? 'segunda') === $day ? 'selected' : '' ?>>
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
                        <input type="number" id="class_capacity" name="capacity" min="1" value="<?= h($editingClass['lotacao'] ?? '20') ?>" required>
                    </div>
                    <div class="campo">
                        <label for="class_room">Sala</label>
                        <input type="text" id="class_room" name="room" value="<?= h($editingClass['sala'] ?? '') ?>">
                    </div>
                    <div class="campo">
                        <label for="class_status">Estado</label>
                        <select id="class_status" name="status" required>
                            <?php foreach ($statuses as $value => $label) { ?>
                                <option value="<?= h($value) ?>" <?= ($editingClass['estado'] ?? 'agendada') === $value ? 'selected' : '' ?>>
                                    <?= h($label) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </fieldset>

            <button type="submit" class="botao amarelo"><?= $isEditing ? 'Guardar aula' : 'Criar aula' ?></button>
        </form>
    </section>

    <?php if (!$isEditing) { ?>
    <section class="painel painel-lista">
        <h2>Aulas</h2>

        <?php if (count($classes) === 0) { ?>
            <p>Ainda não existem aulas no catálogo.</p>
        <?php } else { ?>
            <div class="tabela-wrap">
                <table class="tabela tabela-aulas-admin">
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
                                    <strong><?= h($class['nome']) ?></strong>
                                    <span><?= h($class['tipo']) ?> · <?= h($class['sala'] ?: 'Sem sala') ?></span>
                                </td>
                                <td><?= h($class['treinador_nome']) ?></td>
                                <td><?= h($class['ginasio_nome']) ?></td>
                                <td><?= h(formatClassDay($class['dia_semana'])) ?> · <?= h($class['inicio']) ?> - <?= h($class['fim']) ?></td>
                                <td><?= (int)$class['inscritos'] ?> / <?= (int)$class['lotacao'] ?></td>
                                <td><span class="estado-conta estado-conta-<?= h($class['estado']) ?>"><?= h($class['estado']) ?></span></td>
                                <td>
                                    <div class="acoes-linha">
                                        <a href="admin.php?edit_class=<?= (int)$class['id'] ?>" class="botao claro-voltar">Editar</a>
                                        <?php if ($class['estado'] !== 'cancelada') { ?>
                                            <form action="../actions/action_admin_delete_class.php" method="post" data-confirm="Tens a certeza que queres remover esta aula do catálogo?">
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
    <?php } ?>
<?php
}
