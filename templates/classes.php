<?php
function drawClassesPage(array $classes, array $enrolledClassIds): void
{
    $isLoggedIn = isset($_SESSION['username']);
    $isMember = ($_SESSION['role'] ?? '') === 'membro';
?>
    <main class="pagina-aulas">
        <section class="secção">
            <div class="conteudo">
                <div class="titulo">
                    <p class="subtitulo">Horário</p>
                    <h1>Aulas de grupo</h1>
                    <p>Escolhe uma aula, confirma as vagas e inscreve-te com a tua conta de membro.</p>
                </div>

                <?php if (isset($_GET['sucesso'])) { ?>
                    <p class="mensagem sucesso">Inscrição atualizada com sucesso.</p>
                <?php } ?>
                <?php if (isset($_GET['erro'])) { ?>
                    <p class="mensagem erro">Não foi possível fazer essa inscrição.</p>
                <?php } ?>

                <div class="grelha-aulas">
                    <?php foreach ($classes as $class) drawClassCard($class, $enrolledClassIds, $isLoggedIn, $isMember); ?>
                </div>
            </div>
        </section>
    </main>
<?php
}

function drawClassCard(array $class, array $enrolledClassIds, bool $isLoggedIn, bool $isMember): void
{
    $classId = (int)$class['id'];
    $available = (int)$class['lotacao'] - (int)$class['inscritos'];
    $alreadyEnrolled = in_array($classId, $enrolledClassIds, true);
?>
    <article class="aula">
        <p class="aula-dia"><?= h(formatClassDay($class['dia_semana'])) ?> · <?= h($class['inicio']) ?> - <?= h($class['fim']) ?></p>
        <h2><?= h($class['nome']) ?></h2>
        <p><?= h($class['descricao']) ?></p>

        <div class="meta-aula">
            <span><i class="fa-solid fa-location-dot"></i> <?= h($class['ginasio_nome']) ?></span>
            <span><i class="fa-solid fa-user"></i> <?= h($class['treinador_nome']) ?></span>
            <span><i class="fa-solid fa-door-open"></i> <?= h($class['sala']) ?></span>
            <span><i class="fa-solid fa-users"></i> <?= $available ?> vagas</span>
        </div>

        <?php if (!$isLoggedIn) { ?>
            <a href="login.php" class="botao cliente largo">Entrar para inscrever</a>
        <?php } elseif (!$isMember) { ?>
            <p class="estado-aula">Só membros podem inscrever-se em aulas.</p>
        <?php } elseif ($alreadyEnrolled) { ?>
            <p class="estado-aula inscrito">Já estás inscrito nesta aula.</p>
        <?php } elseif ($available <= 0) { ?>
            <p class="estado-aula">Aula cheia.</p>
        <?php } else { ?>
            <form action="../actions/action_enroll_class.php" method="post">
                <input type="hidden" name="class_id" value="<?= $classId ?>">
                <button type="submit" class="botao amarelo largo">Inscrever-me</button>
            </form>
        <?php } ?>
    </article>
<?php
}
