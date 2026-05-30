<?php
function drawTrainerPage(array $user, array $trainer, array $classes, array $messages = [], array $nutritionPlans = [], array $nutritionMembers = [], ?array $editingClass = null, array $classFilters = []): void
{
    $initials = strtoupper(substr($user['nome'], 0, 1) . substr($user['apelido'], 0, 1));

    $weekDays = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
    $classesByDay = array_fill_keys($weekDays, []);
    foreach ($classes as $class) {
        $day = $class['dia_semana'];
        if (isset($classesByDay[$day])) {
            $classesByDay[$day][] = $class;
        }
    }
?>
    <main class="pagina-perfil" id="trainer">
        <section class="seccao">
            <div class="conteudo">
                <?php if (!$editingClass) { ?>
                <div class="titulo">
                    <p class="subtitulo">Área Treinador</p>
                    <h1>Olá, <?= h($user['nome']) ?></h1>
                    <p>Gere o teu perfil público e consulta as tuas aulas.</p>
                </div>
                <?php } ?>

                <?php if (!empty($messages['success'])) { ?>
                    <p class="mensagem sucesso"><?= h($messages['success']) ?></p>
                <?php } ?>
                <?php if (!empty($messages['error'])) { ?>
                    <p class="mensagem erro"><?= h($messages['error']) ?></p>
                <?php } ?>

                <?php if (!$editingClass) { ?>
                <div class="perfil-grid">
                    <article class="painel">
                        <div class="perfil-topo">
                            <?php if (!empty($user['fotografia'])) { ?>
                                <img src="../<?= h($user['fotografia']) ?>" alt="Fotografia de <?= h($user['nome']) ?>" class="foto-perfil">
                            <?php } else { ?>
                                <div class="foto-perfil foto-perfil-vazia"><?= h($initials) ?></div>
                            <?php } ?>
                            <div>
                                <h2>Dados da conta</h2>
                                <p><?= h($user['nome'] . ' ' . $user['apelido']) ?></p>
                            </div>
                        </div>
                        <dl class="detalhes">
                            <dt>Username</dt>
                            <dd><?= h($user['nome_utilizador']) ?></dd>
                            <dt>Email</dt>
                            <dd><?= h($user['email']) ?></dd>
                            <dt>Especializações</dt>
                            <dd><?= h($trainer['especializacoes'] ?? '-') ?></dd>
                            <dt>Certificados</dt>
                            <dd><?= h($trainer['certificacoes'] ?? '-') ?></dd>
                        </dl>
                    </article>

                    <article class="painel">
                        <h2>Biografia</h2>
                        <p><?= h($trainer['biografia'] ?? 'Sem biografia definida.') ?></p>
                    </article>
                </div>
                <?php } ?>

                <?php if (!$editingClass) { ?>
                <section class="painel painel-editar-perfil">
                    <h2>Editar perfil público</h2>
                    <form action="../actions/action_update_profile.php" method="post" enctype="multipart/form-data">
                        <?= csrfField() ?>
                        <fieldset class="grupo">
                            <legend>Dados pessoais</legend>
                            <div class="campos">
                                <div class="campo">
                                    <label for="first_name">Primeiro nome</label>
                                    <input type="text" id="first_name" name="first_name" value="<?= h($user['nome']) ?>" required>
                                </div>
                                <div class="campo">
                                    <label for="last_name">Apelido</label>
                                    <input type="text" id="last_name" name="last_name" value="<?= h($user['apelido']) ?>" required>
                                </div>
                                <div class="campo campo-largo">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" value="<?= h($user['email']) ?>" required>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="grupo">
                            <legend>Perfil público</legend>
                            <div class="campos">
                                <div class="campo campo-largo">
                                    <label for="bio">Biografia</label>
                                    <input type="text" id="bio" name="bio" value="<?= h($trainer['biografia'] ?? '') ?>">
                                </div>
                                <div class="campo campo-largo">
                                    <label>Especializações</label>
                                    <?php
                                    $especOpts = ['Cycling', 'Pilates', 'Hyrox', 'Kickbox', 'Karaté', 'Funcional'];
                                    $especAtivas = array_map('trim', explode(',', $trainer['especializacoes'] ?? ''));
                                    ?>
                                    <div class="checklist">
                                        <?php foreach ($especOpts as $opt) { ?>
                                            <label class="checklist-item">
                                                <input type="checkbox" name="specializations[]" value="<?= h($opt) ?>" <?= in_array($opt, $especAtivas, true) ? 'checked' : '' ?>>
                                                <?= h($opt) ?>
                                            </label>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="campo campo-largo">
                                    <label for="certifications">Certificados</label>
                                    <input type="text" id="certifications" name="certifications" value="<?= h($trainer['certificacoes'] ?? '') ?>">
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="grupo">
                            <legend>Fotografia</legend>
                            <div class="campo campo-largo">
                                <label for="photo">Foto de perfil</label>
                                <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                <p class="ajuda-campo">JPG, PNG ou WebP até 2 MB.</p>
                            </div>
                        </fieldset>

                        <button type="submit" class="botao amarelo">Guardar alterações</button>
                    </form>
                </section>
                <?php } ?>

                <?php if ($editingClass) { ?>
                <section class="painel painel-editar-perfil painel-form" id="trainer-aulas">
                    <div class="cabecalho-painel">
                        <h2>Editar aula</h2>
                        <a href="profile.php" class="botao claro-voltar">Cancelar</a>
                    </div>
                    <form action="../actions/action_class.php" method="post">
                        <?= csrfField() ?>
                        <input type="hidden" name="class_id" value="<?= (int)$editingClass['id'] ?>">
                        <?php
                        $days     = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
                        $statuses = ['agendada' => 'Agendada', 'concluida' => 'Concluída', 'cancelada' => 'Cancelada'];
                        ?>
                        <fieldset class="grupo">
                            <legend>Detalhes da aula</legend>
                            <div class="campos">
                                <div class="campo">
                                    <label for="tc_name">Nome</label>
                                    <input type="text" id="tc_name" name="name" value="<?= h($editingClass['nome']) ?>" required>
                                </div>
                                <div class="campo">
                                    <label for="tc_type">Tipo</label>
                                    <select id="tc_type" name="type" required>
                                        <?php
                                        $tiposAula = ['cycling', 'funcional', 'hyrox', 'karate', 'kickbox', 'pilates', 'outro'];
                                        foreach ($tiposAula as $t) { ?>
                                            <option value="<?= h($t) ?>" <?= ($editingClass['tipo'] ?? '') === $t ? 'selected' : '' ?>><?= h(ucfirst($t)) ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="campo campo-largo">
                                    <label for="tc_desc">Descrição</label>
                                    <textarea id="tc_desc" name="description" rows="2"><?= h($editingClass['descricao'] ?? '') ?></textarea>
                                </div>
                                <div class="campo">
                                    <label for="tc_day">Dia</label>
                                    <select id="tc_day" name="day" required>
                                        <?php foreach ($days as $d) { ?>
                                            <option value="<?= h($d) ?>" <?= $editingClass['dia_semana'] === $d ? 'selected' : '' ?>>
                                                <?= h(formatClassDay($d)) ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="campo">
                                    <label for="tc_start">Início</label>
                                    <input type="time" id="tc_start" name="start" value="<?= h($editingClass['inicio']) ?>" required>
                                </div>
                                <div class="campo">
                                    <label for="tc_end">Fim</label>
                                    <input type="time" id="tc_end" name="end" value="<?= h($editingClass['fim']) ?>" required>
                                </div>
                                <div class="campo">
                                    <label for="tc_room">Sala</label>
                                    <input type="text" id="tc_room" name="room" value="<?= h($editingClass['sala'] ?? '') ?>">
                                </div>
                                <div class="campo">
                                    <label for="tc_status">Estado</label>
                                    <select id="tc_status" name="status" required>
                                        <?php foreach ($statuses as $val => $label) { ?>
                                            <option value="<?= h($val) ?>" <?= $editingClass['estado'] === $val ? 'selected' : '' ?>>
                                                <?= h($label) ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </fieldset>
                        <button type="submit" class="botao amarelo">Guardar aula</button>
                    </form>
                </section>
                <?php } else { ?>
                <section class="painel painel-aulas" id="trainer-aulas">
                    <div class="cabecalho-painel">
                        <h2>As tuas aulas</h2>
                    </div>

                    <div class="admin-filtros">
                        <form class="class-filters" action="profile.php" method="get">
                            <?php $tiposAula = ['cycling', 'funcional', 'hyrox', 'karate', 'kickbox', 'pilates', 'outro']; ?>
                            <div class="filter-field">
                                <label for="tf-tipo">Tipo</label>
                                <select id="tf-tipo" name="trainer_type">
                                    <option value="">Todos</option>
                                    <?php foreach ($tiposAula as $t) { ?>
                                        <option value="<?= h($t) ?>" <?= ($classFilters['type'] ?? '') === $t ? 'selected' : '' ?>><?= h(ucfirst($t)) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="filter-actions">
                                <button type="submit" class="botao amarelo">Filtrar</button>
                                <a href="profile.php#trainer-aulas" class="botao cliente">Limpar</a>
                            </div>
                        </form>
                    </div>

                    <?php if (count($classes) === 0) { ?>
                        <p>Ainda não tens aulas atribuídas.</p>
                    <?php } else { ?>
                        <div class="horario-inscricoes">
                            <?php foreach ($weekDays as $day) { ?>
                                <section class="dia-horario">
                                    <h3><?= h(formatClassDay($day)) ?></h3>

                                    <?php if (count($classesByDay[$day]) === 0) { ?>
                                        <p class="sem-aulas-dia">Sem aulas</p>
                                    <?php } else { ?>
                                        <div class="aulas-dia-lista">
                                            <?php foreach ($classesByDay[$day] as $class) { ?>
                                                <article class="inscricao">
                                                    <div>
                                                        <p class="aula-dia"><?= h($class['inicio']) ?> - <?= h($class['fim']) ?></p>
                                                        <h4><?= h($class['nome']) ?></h4>
                                                        <p><?= h($class['ginasio_nome']) ?> · <?= h($class['sala']) ?></p>
                                                        <p>
                                                            <a href="class_roster.php?aula=<?= (int)$class['id'] ?>" class="link-amarelo">
                                                                <i class="fa-solid fa-users"></i>
                                                                <?= (int)$class['inscritos'] ?>/<?= (int)$class['lotacao'] ?> inscritos
                                                            </a>
                                                        </p>
                                                    </div>
                                                    <div class="acoes-linha">
                                                        <a href="profile.php?edit_class=<?= (int)$class['id'] ?>#trainer-aulas" class="botao claro-voltar">Editar</a>
                                                        <a href="class_reviews.php?aula=<?= (int)$class['id'] ?>" class="botao cliente">Avaliações</a>
                                                    </div>
                                                </article>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>
                                </section>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </section>
                <?php } ?>

                <?php if (!$editingClass) { drawTrainerNutritionSection($nutritionPlans, $nutritionMembers); } ?>
            </div>
        </section>
    </main>
<?php
}
