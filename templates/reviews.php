<?php
function drawReviewPage(array $classes, ?int $selectedClassId = null): void
{
    $selectedClass = null;

    foreach ($classes as $class) {
        if ((int)$class['id'] === $selectedClassId) {
            $selectedClass = $class;
            break;
        }
    }

    if (!$selectedClass && count($classes) > 0) {
        $selectedClass = $classes[0];
    }
?>
    <main class="pagina-avaliacao">
        <section class="seccao">
            <div class="conteudo">
                <div class="titulo">
                    <p class="subtitulo">Opinião</p>
                    <h1>Deixe a sua opinião</h1>
                    <p>Conte-nos como correu a aula para ajudarmos a melhorar a experiência de treino.</p>
                </div>

                <?php if (isset($_GET['sucesso'])) { ?>
                    <p class="mensagem sucesso">Opinião guardada com sucesso.</p>
                <?php } ?>
                <?php if (isset($_GET['erro'])) { ?>
                    <p class="mensagem erro">Não foi possível guardar a sua opinião.</p>
                <?php } ?>

                <section class="painel painel-avaliacao">
                    <?php if (count($classes) === 0) { ?>
                        <h2>Ainda não há aulas disponíveis para avaliar</h2>
                        <p>As opiniões ficam disponíveis depois de marcar presença numa aula.</p>
                        <a href="aulas.php" class="botao amarelo">Ver horários</a>
                    <?php } else { ?>
                        <form action="../actions/action_review_class.php" method="post" class="form-avaliacao-pagina">
                            <div class="campo">
                                <label for="class_id">1. Em que sessão quer deixar a sua opinião?</label>
                                <select id="class_id" name="class_id" required>
                                    <?php foreach ($classes as $class) { ?>
                                        <option value="<?= (int)$class['id'] ?>" <?= $selectedClass && (int)$selectedClass['id'] === (int)$class['id'] ? 'selected' : '' ?>>
                                            <?= h($class['nome']) ?> - <?= h(formatClassDay($class['dia_semana'])) ?>, <?= h($class['inicio']) ?> às <?= h($class['fim']) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="campo">
                                <label for="rating">2. De 1 a 10, quão boa foi a sessão?</label>
                                <select id="rating" name="rating" required>
                                    <option value="">Escolher classificação</option>
                                    <?php for ($rating = 10; $rating >= 1; $rating--) { ?>
                                        <option value="<?= $rating ?>" <?= $selectedClass && (int)($selectedClass['classificacao'] ?? 0) === $rating ? 'selected' : '' ?>>
                                            <?= $rating ?>/10
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="campo campo-largo">
                                <label for="comment">3. O que correu bem e o que pode ser melhorado?</label>
                                <textarea id="comment" name="comment" rows="6" placeholder="Escreva aqui a sua opinião..."><?= h($selectedClass['comentario'] ?? '') ?></textarea>
                            </div>

                            <button type="submit" class="botao amarelo">Guardar opinião</button>
                        </form>
                    <?php } ?>
                </section>
            </div>
        </section>
    </main>
<?php
}
