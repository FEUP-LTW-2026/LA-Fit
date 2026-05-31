<?php
function drawClassReviewsPage(array $class, array $reviews): void
{
    $total = count($reviews);
    $media = $total > 0 ? array_sum(array_column($reviews, 'classificacao')) / $total : null;
?>
    <main class="pagina-perfil">
        <section class="seccao">
            <div class="conteudo">
                <div class="titulo">
                    <p class="subtitulo">Opiniões</p>
                    <h1><?= h(ucfirst($class['tipo'])) ?></h1>
                    <p>Avaliações de todas as sessões de <?= h(ucfirst($class['tipo'])) ?></p>
                </div>

                <section class="painel painel-aulas">
                    <div class="cabecalho-painel">
                        <span></span>
                        <a href="classes.php" class="botao claro-voltar">Voltar</a>
                    </div>
                    <?php if ($total === 0) { ?>
                        <p>Ainda não há opiniões para esta aula.</p>
                    <?php } else { ?>
                        <h2>
                            <?= $total ?> <?= $total === 1 ? 'opinião' : 'opiniões' ?>
                            <?php if ($media !== null) { ?>
                                · Média <?= number_format($media, 1) ?>/10
                            <?php } ?>
                        </h2>
                        <div class="lista-reportes">
                            <?php foreach ($reviews as $review) { ?>
                                <article class="reporte">
                                    <div class="reporte-cabecalho">
                                        <div>
                                            <h3><?= h($review['nome'] . ' ' . $review['apelido']) ?></h3>
                                            <p class="reporte-meta"><?= h(substr($review['criada_em'], 0, 10)) ?></p>
                                            <?php if (!empty($review['comentario'])) { ?>
                                                <p class="reporte-descricao"><?= h($review['comentario']) ?></p>
                                            <?php } ?>
                                        </div>
                                        <span class="estado-reporte"><?= (int)$review['classificacao'] ?>/10</span>
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
