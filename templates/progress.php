<?php
function drawProgressSection(array $workouts, array $goals, array $stats): void
{
    $tipoLabel = [
        'musculacao' => 'Musculação',
        'cardio'     => 'Cardio',
        'funcional'  => 'Funcional',
        'yoga'       => 'Yoga',
        'outro'      => 'Outro',
    ];
    $tipoIcon = [
        'musculacao' => 'fa-dumbbell',
        'cardio'     => 'fa-heart-pulse',
        'funcional'  => 'fa-person-running',
        'yoga'       => 'fa-spa',
        'outro'      => 'fa-circle-dot',
    ];

    $maxSemanalTreinos = 1;
    foreach ($stats['semanal'] as $s) {
        if ((int)$s['treinos'] > $maxSemanalTreinos) $maxSemanalTreinos = (int)$s['treinos'];
    }
?>
    <section class="painel painel-progresso" id="perfil-progresso">
        <div class="cabecalho-painel">
            <h2>Progresso</h2>
        </div>

        <div class="stats-progresso">
            <div class="stat-progresso">
                <strong><?= $stats['mes_treinos'] ?></strong>
                <span>Treinos este mês</span>
            </div>
            <div class="stat-progresso">
                <strong><?= $stats['mes_minutos'] ?></strong>
                <span>Minutos este mês</span>
            </div>
            <div class="stat-progresso">
                <strong><?= $stats['mes_dias'] ?></strong>
                <span>Dias ativos este mês</span>
            </div>
            <div class="stat-progresso">
                <strong><?= $stats['total_treinos'] ?></strong>
                <span>Treinos totais</span>
            </div>
        </div>

        <?php if (!empty($stats['semanal'])) { ?>
        <div class="grafico-semanal">
            <h3>Atividade das últimas 8 semanas</h3>
            <div class="barras-grafico">
                <?php foreach ($stats['semanal'] as $semana) {
                    $pct = round((int)$semana['treinos'] / $maxSemanalTreinos * 100);
                    [$w, $y] = explode('-', $semana['semana']);
                    $label = 'S' . (int)$w;
                ?>
                    <div class="barra-semana">
                        <span class="barra-valor"><?= (int)$semana['treinos'] ?></span>
                        <div class="barra-coluna">
                            <div class="barra-fill" style="height: <?= $pct ?>%"></div>
                        </div>
                        <span class="barra-label"><?= h($label) ?></span>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php } ?>

        <div class="progresso-grid">

            <div class="progresso-col">
                <h3>Objetivos</h3>

                <?php if (count($goals) > 0) { ?>
                <div class="lista-objetivos">
                    <?php foreach ($goals as $i => $goal) {
                        $pct = $goal['valor_alvo'] > 0
                            ? min(100, round($goal['valor_atual'] / $goal['valor_alvo'] * 100))
                            : 0;
                        $concluido = (bool)$goal['concluido'];
                    ?>
                        <?php if ($i === 1) { ?><div class="objetivos-extras" hidden><?php } ?>
                        <article class="objetivo <?= $concluido ? 'objetivo-concluido' : '' ?>">
                            <div class="objetivo-cabecalho">
                                <p class="objetivo-descricao"><?= h($goal['descricao']) ?></p>
                                <?php if ($concluido) { ?>
                                    <span class="faixa faixa-verde">Concluído</span>
                                <?php } ?>
                            </div>
                            <div class="objetivo-progresso-barra">
                                <div class="objetivo-fill" style="width: <?= $pct ?>%"></div>
                            </div>
                            <div class="objetivo-meta">
                                <span><?= h(number_format($goal['valor_atual'], 1, ',', '')) ?> / <?= h(number_format($goal['valor_alvo'], 1, ',', '')) ?> <?= h($goal['unidade']) ?></span>
                                <span><?= $pct ?>%</span>
                            </div>
                            <?php if ($goal['data_limite']) { ?>
                                <p class="objetivo-prazo"><i class="fa-regular fa-calendar"></i> <?= h(substr($goal['data_limite'], 0, 10)) ?></p>
                            <?php } ?>

                            <?php if (!$concluido) { ?>
                            <form action="../actions/action_update_goal.php" method="post" class="objetivo-atualizar">
                        <?= csrfField() ?>
                                <input type="hidden" name="goal_id" value="<?= (int)$goal['id'] ?>">
                                <input type="number" name="valor_atual" step="0.1" min="0" value="<?= h(number_format($goal['valor_atual'], 1, '.', '')) ?>" required>
                                <span><?= h($goal['unidade']) ?></span>
                                <button type="submit" class="botao claro-voltar">Atualizar</button>
                            </form>
                            <?php } ?>

                            <form action="../actions/action_delete_goal.php" method="post" data-confirm="Tens a certeza que queres remover este objetivo?">
                        <?= csrfField() ?>
                                <input type="hidden" name="goal_id" value="<?= (int)$goal['id'] ?>">
                                <button type="submit" class="botao-remover"><i class="fa-solid fa-xmark"></i></button>
                            </form>
                        </article>
                    <?php } ?>
                    <?php if (count($goals) > 1) { ?></div><?php } ?>
                </div>
                <?php } else { ?>
                    <p class="sem-dados">Ainda não tens objetivos definidos.</p>
                <?php } ?>

                <details class="painel-form-colapsavel">
                    <summary>Novo objetivo</summary>
                    <form action="../actions/action_create_goal.php" method="post" class="form-inline">
                        <?= csrfField() ?>
                        <div class="campo">
                            <label>Descrição</label>
                            <input type="text" name="descricao" placeholder="Ex: Correr 5 km" required>
                        </div>
                        <div class="campos-linha">
                            <div class="campo">
                                <label>Meta</label>
                                <input type="number" name="valor_alvo" step="0.1" min="0.1" placeholder="Ex: 5" required>
                            </div>
                            <div class="campo">
                                <label>Unidade</label>
                                <input type="text" name="unidade" placeholder="Ex: km">
                            </div>
                            <div class="campo">
                                <label>Prazo</label>
                                <input type="date" name="data_limite">
                            </div>
                        </div>
                        <button type="submit" class="botao amarelo">Criar objetivo</button>
                    </form>
                </details>
            </div>

            <div class="progresso-col">
                <h3>Registo de treinos</h3>

                <details class="painel-form-colapsavel" open>
                    <summary>Registar treino</summary>
                    <form action="../actions/action_log_workout.php" method="post" class="form-inline">
                        <?= csrfField() ?>
                        <div class="campos-linha">
                            <div class="campo">
                                <label>Data</label>
                                <input type="date" name="data" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="campo">
                                <label>Tipo</label>
                                <select name="tipo" required>
                                    <option value="musculacao">Musculação</option>
                                    <option value="cardio">Cardio</option>
                                    <option value="funcional">Funcional</option>
                                    <option value="yoga">Yoga</option>
                                    <option value="outro">Outro</option>
                                </select>
                            </div>
                            <div class="campo">
                                <label>Duração (min)</label>
                                <input type="number" name="duracao" min="1" max="480" placeholder="60" required>
                            </div>
                        </div>
                        <div class="campo">
                            <label>Notas</label>
                            <input type="text" name="notas" placeholder="Opcional">
                        </div>
                        <button type="submit" class="botao amarelo">Registar</button>
                    </form>
                </details>

                <?php if (count($workouts) > 0) { ?>
                <div class="lista-treinos">
                    <?php foreach ($workouts as $i => $w) { ?>
                        <?php if ($i === 1) { ?><div class="treinos-extras" hidden><?php } ?>
                        <article class="treino-item">
                            <div class="treino-icone tipo-<?= h($w['tipo']) ?>">
                                <i class="fa-solid <?= h($tipoIcon[$w['tipo']] ?? 'fa-circle-dot') ?>"></i>
                            </div>
                            <div class="treino-info">
                                <strong><?= h($tipoLabel[$w['tipo']] ?? $w['tipo']) ?></strong>
                                <span><?= h(date('d/m/Y', strtotime($w['data']))) ?> · <?= (int)$w['duracao_minutos'] ?> min</span>
                                <?php if ($w['notas']) { ?><p class="treino-notas"><?= h($w['notas']) ?></p><?php } ?>
                            </div>
                            <form action="../actions/action_delete_workout.php" method="post" data-confirm="Tens a certeza que queres remover este registo?">
                        <?= csrfField() ?>
                                <input type="hidden" name="workout_id" value="<?= (int)$w['id'] ?>">
                                <button type="submit" class="botao-remover"><i class="fa-solid fa-xmark"></i></button>
                            </form>
                        </article>
                    <?php } ?>
                    <?php if (count($workouts) > 1) { ?></div><?php } ?>
                </div>
                <?php } else { ?>
                    <p class="sem-dados">Ainda não registaste nenhum treino.</p>
                <?php } ?>
            </div>

        </div>

        <?php
        $totalExtra = max(0, count($workouts) - 1) + max(0, count($goals) - 1);
        if ($totalExtra > 0) { ?>
        <button type="button" class="toggle-treinos painel-form-colapsavel-summary">
            Ver tudo
        </button>
        <?php } ?>
    </section>
<?php
}
