<?php
function drawClassRosterPage(?array $class, array $members): void
{
?>
    <main class="pagina-perfil">
        <section class="seccao">
            <div class="conteudo">
                <div class="titulo">
                    <p class="subtitulo">Inscritos</p>
                    <?php if ($class) { ?>
                        <h1><?= h($class['nome']) ?></h1>
                        <p><?= h(formatClassDay($class['dia_semana'])) ?> · <?= h($class['inicio']) ?> - <?= h($class['fim']) ?></p>
                    <?php } ?>
                </div>

                <section class="painel painel-aulas">
                    <h2><?= count($members) ?> <?= count($members) === 1 ? 'inscrito' : 'inscritos' ?></h2>

                    <?php if (count($members) === 0) { ?>
                        <p>Nenhum membro inscrito nesta aula.</p>
                    <?php } else { ?>
                        <table class="tabela-inscritos">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Apelido</th>
                                    <th>Username</th>
                                    <th>Plano</th>
                                    <th>Data de inscrição</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($members as $member) { ?>
                                    <tr>
                                        <td><?= h($member['nome']) ?></td>
                                        <td><?= h($member['apelido']) ?></td>
                                        <td><?= h($member['nome_utilizador']) ?></td>
                                        <td><?= h($member['plano_nome'] ?? '-') ?></td>
                                        <td><?= h(substr($member['inscrito_em'], 0, 10)) ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    <?php } ?>
                </section>
            </div>
        </section>
    </main>
<?php
}
