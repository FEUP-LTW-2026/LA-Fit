<?php
function drawAdminReportPage(array $reports, array $messages = []): void
{
    $estadoLabel = ['pendente' => 'Pendente', 'em_analise' => 'Em análise', 'resolvido' => 'Resolvido'];
    $tipoLabel   = ['equipamento' => 'Equipamento', 'aula' => 'Aula', 'outro' => 'Outro'];
    $pendentes   = count(array_filter($reports, fn($r) => $r['estado'] === 'pendente'));
?>
    <main class="pagina-perfil">
        <section class="seccao">
            <div class="conteudo">
                <div class="titulo">
                    <p class="subtitulo">Administração</p>
                    <h1>Reportes <?php if ($pendentes > 0) { ?><span class="faixa"><?= $pendentes ?> pendente<?= $pendentes > 1 ? 's' : '' ?></span><?php } ?></h1>
                    <p>Gere e responde aos reportes submetidos pelos membros.</p>
                </div>

                <?php if (!empty($messages['success'])) { ?>
                    <p class="mensagem sucesso"><?= h($messages['success']) ?></p>
                <?php } ?>
                <?php if (!empty($messages['error'])) { ?>
                    <p class="mensagem erro"><?= h($messages['error']) ?></p>
                <?php } ?>

                <section class="painel painel-lista">
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
            </div>
        </section>
    </main>
<?php
}

function drawReportPage(array $reports, array $messages = []): void
{
    $estadoLabel = ['pendente' => 'Pendente', 'em_analise' => 'Em análise', 'resolvido' => 'Resolvido'];
    $tipoLabel   = ['equipamento' => 'Equipamento', 'aula' => 'Aula', 'outro' => 'Outro'];
?>
    <main class="pagina-perfil">
        <section class="seccao">
            <div class="conteudo">
                <div class="titulo">
                    <p class="subtitulo">Área de Apoio</p>
                    <h1>Reportar problema</h1>
                    <p>Reporta problemas com equipamentos, aulas ou qualquer outra questão.</p>
                </div>

                <?php if (!empty($messages['success'])) { ?>
                    <p class="mensagem sucesso"><?= h($messages['success']) ?></p>
                <?php } ?>
                <?php if (!empty($messages['error'])) { ?>
                    <p class="mensagem erro"><?= h($messages['error']) ?></p>
                <?php } ?>

                <section class="painel painel-form">
                    <div class="cabecalho-painel">
                        <h2>Novo reporte</h2>
                    </div>

                    <form action="../actions/action_create_report.php" method="post">
                        <fieldset class="grupo">
                            <legend>Detalhes do problema</legend>
                            <div class="campos">
                                <div class="campo">
                                    <label for="tipo">Tipo de problema</label>
                                    <select id="tipo" name="tipo" required>
                                        <option value="equipamento">Equipamento</option>
                                        <option value="aula">Aula</option>
                                        <option value="outro">Outro</option>
                                    </select>
                                </div>
                                <div class="campo campo-largo">
                                    <label for="assunto">Assunto</label>
                                    <input type="text" id="assunto" name="assunto" placeholder="Ex: Passadeira avariada na zona de cardio" required>
                                </div>
                                <div class="campo campo-largo">
                                    <label for="descricao">Descrição</label>
                                    <textarea id="descricao" name="descricao" rows="4" maxlength="500" placeholder="Descreve o problema com o máximo de detalhe possível." required></textarea>
                                </div>
                            </div>
                        </fieldset>

                        <button type="submit" class="botao amarelo">Enviar reporte</button>
                    </form>
                </section>

                <?php if (count($reports) > 0) { ?>
                <section class="painel painel-lista">
                    <h2><?= count($reports) ?> <?= count($reports) === 1 ? 'reporte enviado' : 'reportes enviados' ?></h2>

                    <div class="lista-reportes">
                        <?php foreach ($reports as $report) { ?>
                            <article class="reporte">
                                <div class="reporte-cabecalho">
                                    <div>
                                        <span class="faixa"><?= h($tipoLabel[$report['tipo']] ?? $report['tipo']) ?></span>
                                        <h3><?= h($report['assunto']) ?></h3>
                                        <p class="reporte-data"><?= h(substr($report['criado_em'], 0, 10)) ?></p>
                                    </div>
                                    <span class="estado-reporte estado-reporte-<?= h($report['estado']) ?>">
                                        <?= h($estadoLabel[$report['estado']] ?? $report['estado']) ?>
                                    </span>
                                </div>

                                <p class="reporte-descricao"><?= h($report['descricao']) ?></p>

                                <?php if (!empty($report['resposta_admin'])) { ?>
                                    <div class="reporte-resposta">
                                        <p><strong>Resposta da administração:</strong></p>
                                        <p><?= h($report['resposta_admin']) ?></p>
                                    </div>
                                <?php } ?>
                            </article>
                        <?php } ?>
                    </div>
                </section>
                <?php } ?>
            </div>
        </section>
    </main>
<?php
}
