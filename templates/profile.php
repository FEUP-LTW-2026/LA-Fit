<?php
function drawProfilePage(array $user, ?array $member, array $enrollments, array $equipmentByZone, array $summary, array $equipmentFilters = [], array $equipmentFilterOptions = [], array $messages = [], array $workouts = [], array $goals = [], array $workoutStats = [], array $nutritionPlans = [], array $features = []): void
{
    $weekDays = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
    $enrollmentsByDay = [];

    foreach ($weekDays as $day) {
        $enrollmentsByDay[$day] = [];
    }

    foreach ($enrollments as $enrollment) {
        $day = $enrollment['dia_semana'];
        if (!isset($enrollmentsByDay[$day])) {
            $enrollmentsByDay[$day] = [];
        }
        $enrollmentsByDay[$day][] = $enrollment;
    }

    $initials = strtoupper(substr($user['nome'], 0, 1) . substr($user['apelido'], 0, 1));
?>
    <main class="pagina-perfil" id="perfil">
        <section class="seccao">
            <div class="conteudo">
                <div class="titulo">
                    <p class="subtitulo">Área Cliente</p>
                    <h1>Olá, <?= h($user['nome']) ?></h1>
                    <p>Aqui tens um resumo simples da tua conta e das tuas aulas.</p>
                </div>

                <?php if (!empty($messages['success'])) { ?>
                    <p class="mensagem sucesso"><?= h($messages['success']) ?></p>
                <?php } ?>
                <?php if (!empty($messages['error'])) { ?>
                    <p class="mensagem erro"><?= h($messages['error']) ?></p>
                <?php } ?>

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
                            <dt>Tipo de conta</dt>
                            <dd><?= h($user['papel']) ?></dd>
                        </dl>
                    </article>

                    <article class="painel">
                        <h2>Plano</h2>
                        <?php if ($member) { ?>
                            <dl class="detalhes">
                                <dt>Plano atual</dt>
                                <dd><?= h($member['plano_nome'] ?? 'Sem plano') ?></dd>
                                <dt>Preço mensal</dt>
                                <dd><?= isset($member['preco_mensal']) ? number_format((float)$member['preco_mensal'], 2, ',', '') . '€' : '-' ?></dd>
                                <dt>Ginásio preferido</dt>
                                <dd><?= h($member['ginasio_nome'] ?? '-') ?></dd>
                            </dl>
                        <?php } else { ?>
                            <p>Esta conta ainda não tem perfil de membro associado.</p>
                        <?php } ?>
                    </article>
                </div>

                <section class="painel painel-editar-perfil" >
                    <h2>Editar perfil</h2>
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
                            </div>
                        </fieldset>

                        <fieldset class="grupo">
                            <legend>Conta</legend>
                            <div class="campos">
                                <div class="campo">
                                    <label for="username">Username</label>
                                    <input type="text" id="username" name="username" value="<?= h($user['nome_utilizador']) ?>" required>
                                </div>

                                <div class="campo">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" value="<?= h($user['email']) ?>" required>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="grupo">
                            <legend>Palavra-passe</legend>
                            <div class="campos">
                                <div class="campo">
                                    <label for="password">Nova palavra-passe</label>
                                    <input type="password" id="password" name="password">
                                </div>

                                <div class="campo">
                                    <label for="password_confirmation">Confirmar palavra-passe</label>
                                    <input type="password" id="password_confirmation" name="password_confirmation">
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

                <?php $canClasses = empty($features) || !empty($features['classes']); ?>
                <?php $canProgress = empty($features) || !empty($features['progress']); ?>
                <?php $canNutrition = empty($features) || !empty($features['nutrition']); ?>

                <?php if ($canClasses) { ?>
                <section class="painel painel-aulas" id="perfil-aulas">
                    <div class="cabecalho-painel">
                        <h2>As tuas aulas</h2>
                        <a href="classes.php" class="botao cliente">Ver aulas</a>
                    </div>

                    <?php if (count($enrollments) === 0) { ?>
                        <p>Ainda não estás inscrito em nenhuma aula.</p>
                    <?php } else { ?>
                        <div class="horario-inscricoes">
                            <?php foreach ($weekDays as $day) { ?>
                                <section class="dia-horario">
                                    <h3><?= h(formatClassDay($day)) ?></h3>

                                    <?php if (count($enrollmentsByDay[$day]) === 0) { ?>
                                        <p class="sem-aulas-dia">Sem aulas</p>
                                    <?php } else { ?>
                                        <div class="aulas-dia-lista">
                                            <?php foreach ($enrollmentsByDay[$day] as $enrollment) { ?>
                                                <article class="inscricao">
                                                    <div>
                                                        <p class="aula-dia"><?= h($enrollment['inicio']) ?> - <?= h($enrollment['fim']) ?></p>
                                                        <h4><?= h($enrollment['nome']) ?></h4>
                                                        <p><?= h($enrollment['ginasio_nome']) ?> · <?= h($enrollment['sala']) ?></p>
                                                        <p><?= h($enrollment['treinador_nome']) ?></p>
                                                        <?php if (($enrollment['inscricao_estado'] ?? '') === 'presente') { ?>
                                                            <p class="estado-presenca">Aula frequentada</p>
                                                        <?php } ?>
                                                    </div>

                                                    <?php if (($enrollment['inscricao_estado'] ?? '') === 'inscrito') { ?>
                                                        <form action="../actions/action_enrollment.php" method="post" data-confirm="Tens a certeza que queres cancelar a inscrição nesta aula?">
                        <?= csrfField() ?>
                        <input type="hidden" name="_action" value="cancel">
                                                            <input type="hidden" name="class_id" value="<?= (int)$enrollment['id'] ?>">
                                                            <input type="hidden" name="return_to" value="profile.php">
                                                            <button type="submit" class="botao claro-voltar">Cancelar</button>
                                                        </form>
                                                    <?php } ?>
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

                <?php if ($member) { ?>
                <section class="painel painel-equipamentos" id="perfil-equipamentos">
                    <div class="cabecalho-painel">
                        <h2>Equipamentos</h2>
                        <button type="button" class="botao cliente" id="btn-ver-equipamentos">Ver mais</button>
                    </div>

                    <div class="resumo-equipamentos">
                        <?php drawEquipmentSummaryItem('disponivel', 'Disponíveis', $summary['disponivel'] ?? 0, 'fa-circle-check'); ?>
                        <?php drawEquipmentSummaryItem('ocupado', 'Em uso', $summary['ocupado'] ?? 0, 'fa-clock'); ?>
                        <?php drawEquipmentSummaryItem('manutencao', 'Manutenção', $summary['manutencao'] ?? 0, 'fa-screwdriver-wrench'); ?>
                    </div>

                    <div class="equipamentos-detalhe" hidden>
                        <?php drawEquipmentFilters($equipmentFilters, $equipmentFilterOptions, 'profile.php#perfil-equipamentos'); ?>

                        <?php if (count($equipmentByZone) === 0) { ?>
                            <p>Nenhum equipamento encontrado com esses filtros.</p>
                        <?php } else { ?>
                            <div class="zonas-equipamentos">
                                <?php foreach ($equipmentByZone as $zone => $items) { ?>
                                    <section class="zona-equipamentos">
                                        <div class="cabecalho-zona">
                                            <h2><?= h($zone) ?></h2>
                                            <span><?= count($items) ?> <?= count($items) === 1 ? 'equipamento' : 'equipamentos' ?></span>
                                        </div>
                                        <div class="lista-equipamentos">
                                            <?php foreach ($items as $equipment) drawEquipmentItem($equipment); ?>
                                        </div>
                                    </section>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                </section>
                <?php } ?>

                <?php if ($member && $canProgress) { drawProgressSection($workouts, $goals, $workoutStats); } ?>

                <?php if ($member && $canNutrition) { drawMemberNutritionSection($nutritionPlans); } ?>
            </div>
        </section>
    </main>
<?php
}
