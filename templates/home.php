<?php
function drawHome(array $plans, array $gyms, array $classes): void
{
?>
    <main>
        <?php if (!isset($_SESSION['username'])) { ?>
        <section class="principal">
            <div class="conteudo principal-caixa">
                <div class="principal-texto">
                    <p class="etiqueta">Promoção limitada</p>
                    <h1>Treina sem limites</h1>
                    <p class="descricao">
                        Ginásios modernos, aulas para todos os níveis e acesso flexível para
                        acompanhares o teu ritmo.
                    </p>

                    <div class="acoes">
                        <a href="enrollment.php" class="botao amarelo">Aderir por 19,99€/mês</a>
                        <a href="classes.php" class="botao claro">Ver aulas</a>
                    </div>

                    <div class="numeros">
                        <div class="numero">
                            <p><?= count($gyms) ?></p>
                            <p>Ginásios</p>
                        </div>
                        <div class="numero">
                            <p>24/7</p>
                            <p>Acesso</p>
                        </div>
                        <div class="numero">
                            <p><?= count($classes) ?>+</p>
                            <p>Aulas por semana</p>
                        </div>
                    </div>
                </div>

                <div class="principal-imagem">
                    <div class="imagem"></div>
                    <div class="cartao">
                        <p class="cartao-topo">Plano mais procurado</p>
                        <h3>Ilimitado</h3>
                        <p>Todos os ginásios, aulas de grupo e acesso total por 29,99€/mês.</p>
                    </div>
                </div>
            </div>
        </section>
        <?php } ?>

        <section class="seccao seccao-clara" id="vantagens">
            <div class="conteudo">
                <div class="titulo">
                    <p class="subtitulo">Vantagens</p>
                    <h2>Porque escolher a LAFit?</h2>
                    <p>
                        Temos tudo o que precisas para começar e continuar o teu treino com
                        conforto.
                    </p>
                </div>

                <div class="grelha-vantagens">
                    <article class="vantagem">
                        <div class="icone"><i class="fa-solid fa-clock"></i></div>
                        <h3>Acesso 24/7</h3>
                        <p>Treina a qualquer hora, de acordo com a tua rotina.</p>
                    </article>

                    <article class="vantagem">
                        <div class="icone"><i class="fa-solid fa-dumbbell"></i></div>
                        <h3>Equipamento moderno</h3>
                        <p>Espaços preparados com máquinas atuais e zonas completas.</p>
                    </article>

                    <article class="vantagem">
                        <div class="icone"><i class="fa-solid fa-user-group"></i></div>
                        <h3>Aulas de grupo</h3>
                        <p>Participa em várias modalidades ao longo da semana.</p>
                    </article>

                    <article class="vantagem">
                        <div class="icone"><i class="fa-solid fa-wifi"></i></div>
                        <h3>Wifi grátis</h3>
                        <p>Fica ligado enquanto treinas ou acompanhas os teus planos.</p>
                    </article>

                    <article class="vantagem">
                        <div class="icone"><i class="fa-solid fa-shower"></i></div>
                        <h3>Balneários modernos</h3>
                        <p>Usa os balneários, chuveiros e cacifos com comodidade.</p>
                    </article>

                    <article class="vantagem">
                        <div class="icone"><i class="fa-solid fa-lock-open"></i></div>
                        <h3>Sem permanência</h3>
                        <p>Escolhe o teu plano com liberdade e sem complicações.</p>
                    </article>
                </div>
            </div>
        </section>

        <?php if (!isset($_SESSION['username'])) { ?>
        <section class="seccao seccao-escura" id="planos">
            <div class="conteudo">
                <div class="titulo titulo-claro">
                    <p class="subtitulo">Planos</p>
                    <h2>Escolhe o teu plano</h2>
                    <p>Sem taxas escondidas e com opções para diferentes objetivos.</p>
                </div>

                <div class="grelha-planos">
                    <?php foreach ($plans as $plan) drawPlanCard($plan); ?>
                </div>
            </div>
        </section>
        <?php } ?>

        <section class="seccao seccao-clara" id="espacos">
            <div class="conteudo">
                <div class="titulo">
                    <p class="subtitulo">Espaços</p>
                    <h2>Conhece os nossos espaços</h2>
                    <p>Áreas pensadas para treino, aulas, recuperação e bem-estar.</p>
                </div>

                <div class="grelha-espacos">
                    <article class="espaco espaco-cardio">
                        <div class="texto-espaco">
                            <h3>Zona de cardio</h3>
                            <p>Passadeiras, bicicletas e treino de resistência.</p>
                        </div>
                    </article>

                    <article class="espaco espaco-aulas">
                        <div class="texto-espaco">
                            <h3>Aulas de grupo</h3>
                            <p>Modalidades dinâmicas para treinar acompanhado.</p>
                        </div>
                    </article>

                    <article class="espaco espaco-musculacao">
                        <div class="texto-espaco">
                            <h3>Zona de musculação</h3>
                            <p>Espaço completo para força e desenvolvimento físico.</p>
                        </div>
                    </article>

                    <article class="espaco espaco-balnearios">
                        <div class="texto-espaco">
                            <h3>Balneários</h3>
                            <p>Conforto e apoio antes e depois de cada treino.</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="seccao" id="aulas-destaque">
            <div class="conteudo">
                <div class="titulo">
                    <p class="subtitulo">Aulas</p>
                    <h2>Algumas aulas desta semana</h2>
                    <p>Horários simples para começares já a planear o próximo treino.</p>
                </div>

                <div class="grelha-aulas">
                    <?php foreach (array_slice($classes, 0, 3) as $class) { ?>
                        <article class="aula">
                            <p class="aula-dia"><?= h(formatClassDay($class['dia_semana'])) ?> · <?= h($class['inicio']) ?></p>
                            <h3><?= h($class['nome']) ?></h3>
                            <p><?= h($class['descricao']) ?></p>
                            <div class="meta-aula">
                                <span><?= h($class['ginasio_nome']) ?></span>
                                <span><?= (int)$class['inscritos'] ?>/<?= (int)$class['lotacao'] ?> inscritos</span>
                            </div>
                        </article>
                    <?php } ?>
                </div>

                <div class="centro">
                    <a href="classes.php" class="botao cliente">Ver todas as aulas</a>
                </div>
            </div>
        </section>

        <?php if (!isset($_SESSION['username'])) { ?>
        <section class="seccao chamada-final">
            <div class="conteudo chamada">
                <p class="subtitulo subtitulo-claro">Começa hoje</p>
                <h2>Pronto para transformar a tua rotina?</h2>
                <p>
                    Treina na LAFit, supera-te todos os dias e encontra um plano que acompanhe o teu ritmo.
                </p>
                <a href="enrollment.php" class="botao amarelo">Quero aderir</a>
            </div>
        </section>
        <?php } ?>
    </main>
<?php
}

function drawPlanCard(array $plan): void
{
    $benefits = explode('|', $plan['beneficios']);
    $isPopular = $plan['nome'] === 'Ilimitado';
?>
    <article class="plano <?= $isPopular ? 'destaque' : '' ?>">
        <?php if ($isPopular) { ?>
            <p class="faixa">Mais popular</p>
        <?php } ?>
        <h3><?= h($plan['nome']) ?></h3>
        <p><?= number_format((float)$plan['preco_mensal'], 2, ',', '') ?>€</p>
        <p class="periodo">por mês</p>
        <ul>
            <?php foreach ($benefits as $benefit) { ?>
                <li><?= h($benefit) ?></li>
            <?php } ?>
        </ul>
        <a href="enrollment.php?plano=<?= (int)$plan['id'] ?>" class="botao <?= $isPopular ? 'amarelo' : 'claro' ?> largo">Escolher plano</a>
    </article>
<?php
}
