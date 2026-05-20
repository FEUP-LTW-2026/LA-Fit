<?php
function output_profile_page(array $user, ?array $member, array $enrollments): void
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
?>
    <main class="pagina-perfil">
        <section class="secção">
            <div class="conteudo">
                <div class="titulo">
                    <p class="subtitulo">Área Cliente</p>
                    <h1>Olá, <?= h($user['nome']) ?></h1>
                    <p>Aqui tens um resumo simples da tua conta e das tuas aulas.</p>
                </div>

                <div class="perfil-grid">
                    <article class="painel">
                        <h2>Dados da conta</h2>
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

                <section class="painel painel-aulas">
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
                                                    </div>

                                                    <form action="action_cancel_enrollment.php" method="post">
                                                        <input type="hidden" name="class_id" value="<?= (int)$enrollment['id'] ?>">
                                                        <button type="submit" class="botao claro-voltar">Cancelar</button>
                                                    </form>
                                                </article>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>
                                </section>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </section>
            </div>
        </section>
    </main>
<?php
}
