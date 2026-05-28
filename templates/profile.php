<?php
function drawProfilePage(array $user, ?array $member, array $enrollments, array $equipmentByZone, array $summary, array $equipmentFilters = [], array $equipmentFilterOptions = [], array $messages = [], array $workouts = [], array $goals = [], array $progressSummary = []): void
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

                <?php if ($member) { ?>
                <section class="painel painel-progresso" id="perfil-progresso">
                    <div class="cabecalho-painel">
                        <h2>Progresso</h2>
                        <p>Regista treinos, define metas e acompanha a evolução com estatísticas simples.</p>
                    </div>

                    <div class="grelha-progresso">
                        <article class="progresso-card">
                            <p>Treinos últimos 30 dias</p>
                            <strong><?= (int)$progressSummary['total_workouts'] ?></strong>
                        </article>
                        <article class="progresso-card">
                            <p>Minutos de treino</p>
                            <strong><?= (int)$progressSummary['total_minutes'] ?> min</strong>
                        </article>
                        <article class="progresso-card">
                            <p>Calorias queimadas</p>
                            <strong><?= (int)$progressSummary['total_calories'] ?> kcal</strong>
                        </article>
                        <article class="progresso-card">
                            <p>Duração média</p>
                            <strong><?= (int)$progressSummary['average_duration'] ?> min</strong>
                        </article>
                    </div>

                    <div class="perfil-progresso-secao">
                        <div>
                            <h3>Metas ativas</h3>
                            <?php if (count($goals) === 0) { ?>
                                <p class="sem-conteudo">Ainda não definiste nenhuma meta.</p>
                            <?php } else { ?>
                                <ul class="lista-metas">
                                    <?php foreach ($goals as $goal) { ?>
                                        <li class="meta-item">
                                            <div class="meta-detalhe">
                                                <strong><?= h($goal['titulo']) ?></strong>
                                                <p><?= h($goal['descricao'] ?: 'Sem descrição adicional.') ?></p>
                                                <small>
                                                    <?= h($goal['objetivo_valor']) ?> <?= h($goal['unidade']) ?> · <?= h($goal['estado']) ?>
                                                    <?= $goal['data_limite'] ? 'até ' . h($goal['data_limite']) : '' ?>
                                                </small>
                                            </div>
                                            <div class="meta-progresso">
                                                <span><?= (int)$goal['progress']['current'] ?>/<?= (int)$goal['progress']['target'] ?> <?= h($goal['progress']['label']) ?></span>
                                                <div class="barra-meta">
                                                    <div class="barra-meta-preenchida" style="width: <?= (int)$goal['progress']['percent'] ?>%"></div>
                                                </div>
                                                <small><?= (int)$goal['progress']['percent'] ?>% <?= $goal['progress']['complete'] ? 'Concluído' : 'Em progresso' ?></small>
                                            </div>
                                        </li>
                                    <?php } ?>
                                </ul>
                            <?php } ?>
                        </div>

                        <div>
                            <h3>Últimos 7 dias</h3>
                            <div class="grafico-semana">
                                <?php
                                    $daily = $progressSummary['daily'] ?? [];
                                    $max = max($daily) ?: 1;
                                    $dayNames = [
                                        'segunda' => 'Seg',
                                        'terca' => 'Ter',
                                        'quarta' => 'Qua',
                                        'quinta' => 'Qui',
                                        'sexta' => 'Sex',
                                        'sabado' => 'Sáb',
                                        'domingo' => 'Dom',
                                    ];
                                ?>
                                <?php foreach ($daily as $day => $count) { ?>
                                    <div class="barra-dia">
                                        <span><?= h($dayNames[$day] ?? substr($day, 0, 3)) ?></span>
                                        <div class="barra-externa">
                                            <div class="barra-interna" style="width: <?= (int)round(($count / $max) * 100) ?>%"></div>
                                        </div>
                                        <small><?= (int)$count ?></small>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </section>
                <?php } ?>

                <?php if ($member) { ?>
                <section class="painel painel-progresso" id="perfil-treinos">
                    <div class="cabecalho-painel">
                        <h2>Registos recentes</h2>
                        <p>Vê os últimos treinos que registaste para acompanhar o teu ritmo.</p>
                    </div>

                    <?php if (count($workouts) === 0) { ?>
                        <p class="sem-conteudo">Ainda não registaste nenhum treino.</p>
                    <?php } else { ?>
                        <ul class="lista-treinos">
                            <?php foreach ($workouts as $workout) { ?>
                                <li class="entrada-treino">
                                    <div>
                                        <strong><?= h($workout['tipo']) ?></strong>
                                        <small><?= h($workout['data_treino']) ?> · <?= (int)$workout['duracao_minutos'] ?> min · <?= (int)$workout['calorias'] ?> kcal</small>
                                    </div>
                                    <?php if (!empty($workout['notas'])) { ?>
                                        <p><?= h($workout['notas']) ?></p>
                                    <?php } ?>
                                </li>
                            <?php } ?>
                        </ul>
                    <?php } ?>
                </section>
                <?php } ?>

                <section class="painel painel-editar-perfil" >
                    <h2>Editar perfil</h2>
                    <form action="../actions/action_update_profile.php" method="post" enctype="multipart/form-data">
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

                <?php if ($member) { ?>
                <section class="painel painel-form painel-registo-treino" id="perfil-registo-treino">
                    <div class="cabecalho-painel">
                        <h2>Registar treino</h2>
                        <p>Guarda o treino do dia e vê a tua atividade a crescer.</p>
                    </div>

                    <form action="../actions/action_log_workout.php" method="post">
                        <fieldset class="grupo">
                            <legend>Treino</legend>
                            <div class="campos">
                                <div class="campo">
                                    <label for="workout_date">Data do treino</label>
                                    <input type="date" id="workout_date" name="date" required>
                                </div>
                                <div class="campo">
                                    <label for="workout_type">Tipo de treino</label>
                                    <input type="text" id="workout_type" name="type" placeholder="e.g. Força, Cardio" required>
                                </div>
                                <div class="campo">
                                    <label for="workout_duration">Duração (min)</label>
                                    <input type="number" id="workout_duration" name="duration" min="1" required>
                                </div>
                                <div class="campo">
                                    <label for="workout_calories">Calorias</label>
                                    <input type="number" id="workout_calories" name="calories" min="0" required>
                                </div>
                                <div class="campo campo-largo">
                                    <label for="workout_notes">Notas</label>
                                    <input type="text" id="workout_notes" name="notes" placeholder="Como correu o treino?">
                                </div>
                            </div>
                        </fieldset>
                        <button type="submit" class="botao amarelo">Registar treino</button>
                    </form>
                </section>

                <section class="painel painel-form painel-metas" id="perfil-metas">
                    <div class="cabecalho-painel">
                        <h2>Definir meta</h2>
                        <p>Cria um objetivo e acompanha os resultados com percentagens claras.</p>
                    </div>

                    <form action="../actions/action_set_goal.php" method="post">
                        <fieldset class="grupo">
                            <legend>Meta</legend>
                            <div class="campos">
                                <div class="campo">
                                    <label for="goal_title">Título</label>
                                    <input type="text" id="goal_title" name="title" required>
                                </div>
                                <div class="campo campo-largo">
                                    <label for="goal_description">Descrição</label>
                                    <input type="text" id="goal_description" name="description" placeholder="e.g. 3 treinos por semana">
                                </div>
                                <div class="campo">
                                    <label for="goal_type">Tipo</label>
                                    <select id="goal_type" name="type" required>
                                        <option value="treinos">Treinos</option>
                                        <option value="minutos">Minutos</option>
                                        <option value="calorias">Calorias</option>
                                    </select>
                                </div>
                                <div class="campo">
                                    <label for="goal_target">Objetivo</label>
                                    <input type="number" id="goal_target" name="target" min="1" required>
                                </div>
                                <div class="campo">
                                    <label for="goal_unit">Unidade</label>
                                    <input type="text" id="goal_unit" name="unit" value="treinos" required>
                                </div>
                                <div class="campo">
                                    <label for="goal_deadline">Data limite</label>
                                    <input type="date" id="goal_deadline" name="deadline">
                                </div>
                            </div>
                        </fieldset>
                        <button type="submit" class="botao amarelo">Criar meta</button>
                    </form>
                </section>
                <?php } ?>

                <section class="painel painel-aulas" id="perfil-aulas">
                    <div class="cabecalho-painel">
                        <h2>As tuas aulas</h2>
                        <a href="aulas.php" class="botao cliente">Ver aulas</a>
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
                                                        <form action="../actions/action_cancel_enrollment.php" method="post" data-confirm="Tens a certeza que queres cancelar a inscrição nesta aula?">
                                                            <input type="hidden" name="class_id" value="<?= (int)$enrollment['id'] ?>">
                                                            <input type="hidden" name="return_to" value="perfil.php">
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

                <?php if ($member) { ?>
                <section class="painel painel-equipamentos" id="perfil-equipamentos">
                    <div class="cabecalho-painel">
                        <h2>Equipamentos</h2>
                    </div>

                    <div class="resumo-equipamentos">
                        <?php drawEquipmentSummaryItem('disponivel', 'Disponíveis', $summary['disponivel'] ?? 0, 'fa-circle-check'); ?>
                        <?php drawEquipmentSummaryItem('ocupado', 'Em uso', $summary['ocupado'] ?? 0, 'fa-clock'); ?>
                        <?php drawEquipmentSummaryItem('manutencao', 'Manutenção', $summary['manutencao'] ?? 0, 'fa-screwdriver-wrench'); ?>
                    </div>

                    <?php drawEquipmentFilters($equipmentFilters, $equipmentFilterOptions, 'perfil.php'); ?>

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
                </section>
                <?php } ?>
            </div>
        </section>
    </main>
<?php
}
