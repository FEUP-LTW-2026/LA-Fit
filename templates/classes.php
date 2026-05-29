<?php
function drawClassesPage(array $classes, array $enrolledClassIds, array $filters = [], array $filterOptions = []): void
{
    $isLoggedIn = isset($_SESSION['username']);
    $isMember = ($_SESSION['role'] ?? '') === 'membro';
    $returnTo = 'classes.php' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
?>
    <main class="pagina-aulas">
        <section class="seccao">
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

                <?php drawClassFilters($filters, $filterOptions); ?>

                <?php if (count($classes) === 0) { ?>
                    <div class="class-empty-state">
                        <h2>Nao encontramos aulas com esses filtros.</h2>
                        <p>Experimenta ajustar o tipo, treinador, dia ou hora para veres mais opcoes.</p>
                        <a href="classes.php" class="botao cliente">Limpar filtros</a>
                    </div>
                <?php } else { ?>
                    <div class="grelha-aulas">
                        <?php foreach ($classes as $class) drawClassCard($class, $enrolledClassIds, $isLoggedIn, $isMember, $returnTo); ?>
                    </div>
                <?php } ?>
            </div>
        </section>
    </main>
<?php
}

function drawClassFilters(array $filters, array $filterOptions): void
{
    $days = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
    $types = $filterOptions['types'] ?? [];
    $trainers = $filterOptions['trainers'] ?? [];
    $times = $filterOptions['times'] ?? [];
?>
    <form class="class-filters" action="classes.php" method="get">
        <div class="filter-field">
            <label for="type">Tipo</label>
            <select id="type" name="type">
                <option value="">Todos</option>
                <?php foreach ($types as $type) { ?>
                    <option value="<?= h($type['tipo']) ?>" <?= ($filters['type'] ?? '') === $type['tipo'] ? 'selected' : '' ?>>
                        <?= h($type['nome']) ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="filter-field">
            <label for="trainer">Treinador</label>
            <select id="trainer" name="trainer">
                <option value="">Todos</option>
                <?php foreach ($trainers as $trainer) { ?>
                    <option value="<?= (int)$trainer['id'] ?>" <?= (string)($filters['trainer'] ?? '') === (string)$trainer['id'] ? 'selected' : '' ?>>
                        <?= h($trainer['nome']) ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="filter-field">
            <label for="day">Dia</label>
            <select id="day" name="day">
                <option value="">Todos</option>
                <?php foreach ($days as $day) { ?>
                    <option value="<?= h($day) ?>" <?= ($filters['day'] ?? '') === $day ? 'selected' : '' ?>>
                        <?= h(formatClassDay($day)) ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="filter-field">
            <label for="time">Hora</label>
            <select id="time" name="time">
                <option value="">Todas</option>
                <?php foreach ($times as $time) { ?>
                    <option value="<?= h($time) ?>" <?= ($filters['time'] ?? '') === $time ? 'selected' : '' ?>>
                        <?= h($time) ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="botao amarelo">Filtrar</button>
            <a href="classes.php" class="botao cliente">Limpar</a>
        </div>
    </form>
<?php
}

function drawClassCard(array $class, array $enrolledClassIds, bool $isLoggedIn, bool $isMember, string $returnTo): void
{
    $classId = (int)$class['id'];
    $available = (int)$class['lotacao'] - (int)$class['inscritos'];
    $alreadyEnrolled = in_array($classId, $enrolledClassIds, true);
?>
    <article class="aula" data-class-id="<?= $classId ?>">
        <p class="aula-dia"><?= h(formatClassDay($class['dia_semana'])) ?> · <?= h($class['inicio']) ?> - <?= h($class['fim']) ?></p>
        <h2><?= h($class['nome']) ?></h2>
        <p><?= h($class['descricao']) ?></p>

        <div class="meta-aula">
            <span><i class="fa-solid fa-location-dot"></i> <?= h($class['ginasio_nome']) ?></span>
            <span><i class="fa-solid fa-user"></i> <a href="profile_view.php?id=<?= (int)$class['treinador_id'] ?>" class="link-amarelo"><?= h($class['treinador_nome']) ?></a></span>
            <span><i class="fa-solid fa-door-open"></i> <?= h($class['sala']) ?></span>
            <span class="vagas-aula"><i class="fa-solid fa-users"></i> <?= $available ?> vagas</span>
        </div>

        <div class="convite-avaliacao">
            <h3>Já foi a uma destas? Deixe a sua opinião</h3>
            <a href="review.php?class_id=<?= $classId ?>" class="botao amarelo largo">Dar opinião</a>
        </div>

        <?php if (!$isLoggedIn) { ?>
            <a href="login.php" class="botao cliente largo">Entrar para inscrever</a>
        <?php } elseif (!$isMember) { ?>
            <p class="estado-aula">Só membros podem inscrever-se em aulas.</p>
        <?php } elseif ($alreadyEnrolled) { ?>
            <p class="estado-aula inscrito">Já estás inscrito nesta aula.</p>
            <form action="../actions/action_cancel_enrollment.php" method="post" data-confirm="Tens a certeza que queres cancelar a inscrição nesta aula?">
                <input type="hidden" name="class_id" value="<?= $classId ?>">
                <input type="hidden" name="return_to" value="<?= h($returnTo) ?>">
                <button type="submit" class="botao cliente largo">Cancelar inscrição</button>
            </form>
        <?php } elseif ($available <= 0) { ?>
            <p class="estado-aula">Aula cheia.</p>
        <?php } else { ?>
            <form action="../actions/action_enroll_class.php" method="post">
                <input type="hidden" name="class_id" value="<?= $classId ?>">
                <input type="hidden" name="return_to" value="<?= h($returnTo) ?>">
                <button type="submit" class="botao amarelo largo">Inscrever-me</button>
            </form>
        <?php } ?>
    </article>
<?php
}
