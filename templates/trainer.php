<?php
function drawTrainerPage(array $user, array $trainer, array $classes, array $messages = []): void
{
    $initials = strtoupper(substr($user['nome'], 0, 1) . substr($user['apelido'], 0, 1));
?>
    <main class="pagina-perfil">
        <section class="seccao">
            <div class="conteudo">
                <div class="titulo">
                    <p class="subtitulo">Área Treinador</p>
                    <h1>Olá, <?= h($user['nome']) ?></h1>
                    <p>Gere o teu perfil público e consulta as tuas aulas.</p>
                </div>

                <?php if (!empty($messages['success'])) { ?>
                    <p class="mensagem sucesso"><?= h($messages['success']) ?></p>
                <?php } ?>
                <?php if (!empty($messages['error'])) { ?>
                    <p class="mensagem erro"><?= h($messages['error']) ?></p>
                <?php } ?>

                <div class="perfil-grid">
                    <article class="painel">
                        <div class="perfil-topo">
                            <?php if (!empty($user['fotografia'])) { ?>
                                <img src="../<?= h($user['fotografia']) ?>" alt="Fotografia de <?= h($user['nome']) ?>" class="foto-perfil">
                            <?php } else { ?>
                                <div class="foto-perfil foto-perfil-vazia"><?= h($initials) ?></div>
                            <?php } ?>
                            <div>
                                <h2>Dados da conta</h2>
                                <p><?= h($user['nome'] . ' ' . $user['apelido']) ?></p>
                            </div>
                        </div>
                        <dl class="detalhes">
                            <dt>Username</dt>
                            <dd><?= h($user['nome_utilizador']) ?></dd>
                            <dt>Email</dt>
                            <dd><?= h($user['email']) ?></dd>
                            <dt>Especializações</dt>
                            <dd><?= h($trainer['especializacoes'] ?? '-') ?></dd>
                            <dt>Certificados</dt>
                            <dd><?= h($trainer['certificacoes'] ?? '-') ?></dd>
                        </dl>
                    </article>

                    <article class="painel">
                        <h2>Biografia</h2>
                        <p><?= h($trainer['biografia'] ?? 'Sem biografia definida.') ?></p>
                    </article>
                </div>

                <section class="painel painel-editar-perfil">
                    <h2>Editar perfil público</h2>
                    <form action="../actions/action_update_trainer.php" method="post" enctype="multipart/form-data">
                        <fieldset class="grupo">
                            <legend>Dados pessoais</legend>
                            <div class="campos">
                                <div class="campo">
                                    <label for="first_name">Primeiro nome</label>
                                    <input type="text" id="first_name" name="first_name" value="<?= h($user['nome']) ?>" required>
                                </div>
                                <div class="campo">
                                    <label for="last_name">Apelido</label>
                                    <input type="text" id="last_name" name="last_name" value="<?= h($user['apelido']) ?>" required>
                                </div>
                                <div class="campo campo-largo">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" value="<?= h($user['email']) ?>" required>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="grupo">
                            <legend>Perfil público</legend>
                            <div class="campos">
                                <div class="campo campo-largo">
                                    <label for="bio">Biografia</label>
                                    <input type="text" id="bio" name="bio" value="<?= h($trainer['biografia'] ?? '') ?>">
                                </div>
                                <div class="campo campo-largo">
                                    <label for="specializations">Especializações</label>
                                    <input type="text" id="specializations" name="specializations" value="<?= h($trainer['especializacoes'] ?? '') ?>">
                                </div>
                                <div class="campo campo-largo">
                                    <label for="certifications">Certificados</label>
                                    <input type="text" id="certifications" name="certifications" value="<?= h($trainer['certificacoes'] ?? '') ?>">
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="grupo">
                            <legend>Fotografia</legend>
                            <div class="campo campo-largo">
                                <label for="photo">Foto de perfil</label>
                                <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                <p class="ajuda-campo">JPG, PNG ou WebP até 2 MB.</p>
                            </div>
                        </fieldset>

                        <button type="submit" class="botao amarelo">Guardar alterações</button>
                    </form>
                </section>

                <section class="painel painel-aulas">
                    <h2>As tuas aulas</h2>
                    <?php if (count($classes) === 0) { ?>
                        <p>Ainda não tens aulas atribuídas.</p>
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
                                        <span><i class="fa-solid fa-users"></i> <a href="class_roster.php?aula=<?= (int)$class['id'] ?>" class="link-amarelo"><?= (int)$class['inscritos'] ?>/<?= (int)$class['lotacao'] ?> inscritos</a></span>
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
