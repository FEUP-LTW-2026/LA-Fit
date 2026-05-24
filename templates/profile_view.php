<?php
function drawTrainerProfilePage(array $trainer, array $classes): void
{
    $initials = strtoupper(substr($trainer['nome'], 0, 1) . substr($trainer['apelido'], 0, 1));
?>
    <main class="pagina-perfil">
        <section class="seccao">
            <div class="conteudo">
                <div class="perfil-grid-lateral">
                    <article class="painel painel-principal">
                        <div class="perfil-topo">
                            <?php if (!empty($trainer['fotografia'])) { ?>
                                <img src="../<?= h($trainer['fotografia']) ?>" alt="Fotografia de <?= h($trainer['nome']) ?>" class="foto-perfil">
                            <?php } else { ?>
                                <div class="foto-perfil foto-perfil-vazia"><?= h($initials) ?></div>
                            <?php } ?>
                            <div>
                                <p class="subtitulo">Treinador</p>
                                <h1><?= h($trainer['nome'] . ' ' . $trainer['apelido']) ?></h1>
                            </div>
                        </div>
                        <h2>Biografia</h2>
                        <p><?= h($trainer['biografia'] ?? 'Sem biografia definida.') ?></p>
                    </article>

                    <article class="painel">
                        <h2>Especializações</h2>
                        <p><?= h($trainer['especializacoes'] ?? '-') ?></p>
                    </article>

                    <article class="painel">
                        <h2>Certificados</h2>
                        <p><?= h($trainer['certificacoes'] ?? '-') ?></p>
                    </article>
                </div>

                <section class="painel painel-aulas">
                    <h2>Aulas</h2>
                    <?php if (count($classes) === 0) { ?>
                        <p>Sem aulas agendadas.</p>
                    <?php } else { ?>
                        <div class="grelha-aulas">
                            <?php foreach ($classes as $class) { ?>
                                <article class="aula">
                                    <p class="aula-dia"><?= h(formatClassDay($class['dia_semana'])) ?> · <?= h($class['inicio']) ?> - <?= h($class['fim']) ?></p>
                                    <h3><?= h($class['nome']) ?></h3>
                                    <p><?= h($class['descricao']) ?></p>
                                    <div class="meta-aula">
                                        <span><i class="fa-solid fa-location-dot"></i> <?= h($class['ginasio_nome']) ?></span>
                                        <span><i class="fa-solid fa-door-open"></i> <?= h($class['sala']) ?></span>
                                        <span><i class="fa-solid fa-users"></i> <?= (int)$class['inscritos'] ?>/<?= (int)$class['lotacao'] ?> vagas</span>
                                    </div>
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
