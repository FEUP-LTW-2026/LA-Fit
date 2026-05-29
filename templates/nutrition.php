<?php
function drawTrainerNutritionSection(array $plans, array $members): void
{
    $tipoLabel = [
        'pequeno_almoco' => 'Pequeno-almoço',
        'almoco'         => 'Almoço',
        'jantar'         => 'Jantar',
        'lanche'         => 'Lanche',
        'outro'          => 'Outro',
    ];
?>
    <section class="painel painel-nutricao" id="trainer-nutricao">
        <div class="cabecalho-painel">
            <h2>Planos nutricionais</h2>
        </div>

        <details class="painel-form-colapsavel">
            <summary>Novo plano</summary>
            <form action="../actions/action_create_nutrition_plan.php" method="post" class="form-inline">
                        <?= csrfField() ?>
                <div class="campo">
                    <label>Nome do plano</label>
                    <input type="text" name="nome" placeholder="Ex: Plano de definição" required>
                </div>
                <div class="campo">
                    <label>Descrição</label>
                    <input type="text" name="descricao" placeholder="Opcional">
                </div>
                <button type="submit" class="botao amarelo">Criar plano</button>
            </form>
        </details>

        <?php if (empty($plans)) { ?>
            <p class="sem-dados">Ainda não criaste nenhum plano nutricional.</p>
        <?php } else { ?>
        <div class="lista-planos-nutricao">
            <?php foreach ($plans as $plan) {
                $totalCalorias = array_sum(array_column($plan['refeicoes'], 'calorias'));
            ?>
            <article class="plano-nutricao">
                <div class="plano-nutricao-cabecalho">
                    <div>
                        <h3><?= h($plan['nome']) ?></h3>
                        <?php if ($plan['descricao']) { ?>
                            <p class="plano-nutricao-desc"><?= h($plan['descricao']) ?></p>
                        <?php } ?>
                        <span class="plano-nutricao-meta">
                            <?= count($plan['refeicoes']) ?> <?= count($plan['refeicoes']) === 1 ? 'refeição' : 'refeições' ?>
                            <?php if ($totalCalorias > 0) { ?> · <?= $totalCalorias ?> kcal/dia<?php } ?>
                            · <?= count($plan['atribuicoes']) ?> <?= count($plan['atribuicoes']) === 1 ? 'membro' : 'membros' ?>
                        </span>
                    </div>
                    <form action="../actions/action_delete_nutrition_plan.php" method="post" data-confirm="Tens a certeza que queres eliminar este plano? Será removido de todos os membros.">
                        <?= csrfField() ?>
                        <input type="hidden" name="plan_id" value="<?= (int)$plan['id'] ?>">
                        <button type="submit" class="botao-remover"><i class="fa-solid fa-xmark"></i></button>
                    </form>
                </div>

                <details class="painel-form-colapsavel">
                    <summary>Refeições (<?= count($plan['refeicoes']) ?>)</summary>

                    <?php if (empty($plan['refeicoes'])) { ?>
                        <p class="sem-dados">Ainda não adicionaste refeições a este plano.</p>
                    <?php } else { ?>
                    <div class="lista-refeicoes">
                        <?php foreach ($plan['refeicoes'] as $meal) { ?>
                        <div class="refeicao-item">
                            <div class="refeicao-info">
                                <strong><?= h($meal['nome']) ?></strong>
                                <span class="faixa faixa-cinzento"><?= h($tipoLabel[$meal['tipo']] ?? $meal['tipo']) ?></span>
                            </div>
                            <div class="refeicao-macros">
                                <span><?= (int)$meal['calorias'] ?> kcal</span>
                                <span><?= number_format((float)$meal['proteinas'], 1) ?>g prot</span>
                                <span><?= number_format((float)$meal['hidratos'], 1) ?>g hid</span>
                                <span><?= number_format((float)$meal['gorduras'], 1) ?>g gord</span>
                            </div>
                            <form action="../actions/action_delete_meal.php" method="post" data-confirm="Remover esta refeição?">
                        <?= csrfField() ?>
                                <input type="hidden" name="meal_id" value="<?= (int)$meal['id'] ?>">
                                <button type="submit" class="botao-remover"><i class="fa-solid fa-xmark"></i></button>
                            </form>
                        </div>
                        <?php } ?>
                    </div>
                    <?php } ?>

                    <details class="painel-form-colapsavel">
                        <summary>Adicionar refeição</summary>
                        <form action="../actions/action_add_meal.php" method="post" class="form-inline">
                        <?= csrfField() ?>
                            <input type="hidden" name="plan_id" value="<?= (int)$plan['id'] ?>">
                            <div class="campos-linha">
                                <div class="campo">
                                    <label>Nome</label>
                                    <input type="text" name="nome" placeholder="Ex: Aveia com fruta" required>
                                </div>
                                <div class="campo">
                                    <label>Tipo</label>
                                    <select name="tipo" required>
                                        <option value="pequeno_almoco">Pequeno-almoço</option>
                                        <option value="almoco">Almoço</option>
                                        <option value="jantar">Jantar</option>
                                        <option value="lanche">Lanche</option>
                                        <option value="outro">Outro</option>
                                    </select>
                                </div>
                                <div class="campo">
                                    <label>Calorias (kcal)</label>
                                    <input type="number" name="calorias" min="0" placeholder="350" required>
                                </div>
                            </div>
                            <div class="campos-linha">
                                <div class="campo">
                                    <label>Proteínas (g)</label>
                                    <input type="number" name="proteinas" min="0" step="0.1" placeholder="20">
                                </div>
                                <div class="campo">
                                    <label>Hidratos (g)</label>
                                    <input type="number" name="hidratos" min="0" step="0.1" placeholder="50">
                                </div>
                                <div class="campo">
                                    <label>Gorduras (g)</label>
                                    <input type="number" name="gorduras" min="0" step="0.1" placeholder="10">
                                </div>
                            </div>
                            <button type="submit" class="botao amarelo">Adicionar</button>
                        </form>
                    </details>
                </details>

                <details class="painel-form-colapsavel">
                    <summary>Membros atribuídos (<?= count($plan['atribuicoes']) ?>)</summary>

                    <?php if (!empty($plan['atribuicoes'])) { ?>
                    <div class="lista-atribuicoes">
                        <?php foreach ($plan['atribuicoes'] as $a) { ?>
                        <div class="atribuicao-item">
                            <span><?= h($a['nome'] . ' ' . $a['apelido']) ?> <small>@<?= h($a['nome_utilizador']) ?></small></span>
                            <form action="../actions/action_unassign_nutrition_plan.php" method="post" data-confirm="Remover este membro do plano?">
                        <?= csrfField() ?>
                                <input type="hidden" name="assign_id" value="<?= (int)$a['atribuicao_id'] ?>">
                                <button type="submit" class="botao-remover"><i class="fa-solid fa-xmark"></i></button>
                            </form>
                        </div>
                        <?php } ?>
                    </div>
                    <?php } else { ?>
                        <p class="sem-dados">Nenhum membro atribuído a este plano.</p>
                    <?php } ?>

                    <?php if (!empty($members)) { ?>
                    <form action="../actions/action_assign_nutrition_plan.php" method="post" class="form-inline">
                        <?= csrfField() ?>
                        <input type="hidden" name="plan_id" value="<?= (int)$plan['id'] ?>">
                        <div class="campos-linha">
                            <div class="campo">
                                <label>Atribuir a</label>
                                <select name="membro_id" required>
                                    <option value="">Selecionar membro...</option>
                                    <?php foreach ($members as $m) { ?>
                                        <option value="<?= (int)$m['id'] ?>"><?= h($m['nome'] . ' ' . $m['apelido']) ?> (@<?= h($m['nome_utilizador']) ?>)</option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="botao cliente">Atribuir</button>
                    </form>
                    <?php } ?>
                </details>
            </article>
            <?php } ?>
        </div>
        <?php } ?>
    </section>
<?php
}

function drawMemberNutritionSection(array $plans): void
{
    $tipoLabel = [
        'pequeno_almoco' => 'Pequeno-almoço',
        'almoco'         => 'Almoço',
        'jantar'         => 'Jantar',
        'lanche'         => 'Lanche',
        'outro'          => 'Outro',
    ];
    $tipoIcon = [
        'pequeno_almoco' => 'fa-mug-hot',
        'almoco'         => 'fa-bowl-food',
        'jantar'         => 'fa-moon',
        'lanche'         => 'fa-apple-whole',
        'outro'          => 'fa-utensils',
    ];
?>
    <section class="painel painel-nutricao" id="perfil-nutricao">
        <div class="cabecalho-painel">
            <h2>Planos nutricionais</h2>
        </div>

        <?php if (empty($plans)) { ?>
            <p class="sem-dados">Ainda não tens planos nutricionais atribuídos.</p>
        <?php } else { ?>
        <div class="lista-planos-nutricao">
            <?php foreach ($plans as $plan) {
                $totalCalorias  = array_sum(array_column($plan['refeicoes'], 'calorias'));
                $totalProteinas = array_sum(array_column($plan['refeicoes'], 'proteinas'));
                $totalHidratos  = array_sum(array_column($plan['refeicoes'], 'hidratos'));
                $totalGorduras  = array_sum(array_column($plan['refeicoes'], 'gorduras'));
            ?>
            <article class="plano-nutricao-membro">
                <div class="plano-nutricao-cabecalho">
                    <div>
                        <h3><?= h($plan['nome']) ?></h3>
                        <?php if ($plan['descricao']) { ?>
                            <p class="plano-nutricao-desc"><?= h($plan['descricao']) ?></p>
                        <?php } ?>
                        <span class="plano-nutricao-meta">Treinador: <?= h($plan['treinador_nome'] . ' ' . $plan['treinador_apelido']) ?></span>
                    </div>
                    <?php if ($totalCalorias > 0) { ?>
                    <div class="plano-nutricao-total">
                        <strong><?= $totalCalorias ?></strong>
                        <span>kcal/dia</span>
                    </div>
                    <?php } ?>
                </div>

                <?php if (!empty($plan['refeicoes'])) { ?>
                <details class="painel-form-colapsavel">
                    <summary>Ver refeições (<?= count($plan['refeicoes']) ?>)</summary>
                    <div class="lista-refeicoes">
                        <?php foreach ($plan['refeicoes'] as $meal) { ?>
                        <div class="refeicao-item">
                            <div class="refeicao-info">
                                <span class="refeicao-icone"><i class="fa-solid <?= h($tipoIcon[$meal['tipo']] ?? 'fa-utensils') ?>"></i></span>
                                <div>
                                    <strong><?= h($meal['nome']) ?></strong>
                                    <small><?= h($tipoLabel[$meal['tipo']] ?? $meal['tipo']) ?></small>
                                </div>
                            </div>
                            <div class="refeicao-macros">
                                <span><?= (int)$meal['calorias'] ?> kcal</span>
                                <span><?= number_format((float)$meal['proteinas'], 1) ?>g prot</span>
                                <span><?= number_format((float)$meal['hidratos'], 1) ?>g hid</span>
                                <span><?= number_format((float)$meal['gorduras'], 1) ?>g gord</span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                    <?php if ($totalProteinas + $totalHidratos + $totalGorduras > 0) { ?>
                    <div class="plano-macros-total">
                        <span>Total diário:</span>
                        <span><?= number_format($totalProteinas, 1) ?>g proteínas</span>
                        <span><?= number_format($totalHidratos, 1) ?>g hidratos</span>
                        <span><?= number_format($totalGorduras, 1) ?>g gorduras</span>
                    </div>
                    <?php } ?>
                </details>
                <?php } else { ?>
                    <p class="sem-dados">Este plano ainda não tem refeições definidas.</p>
                <?php } ?>
            </article>
            <?php } ?>
        </div>
        <?php } ?>
    </section>
<?php
}
