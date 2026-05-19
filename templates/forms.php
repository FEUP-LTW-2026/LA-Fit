<?php
function output_login_page(?string $error = null): void
{
?>
    <main class="pagina-cliente">
        <section class="secção area-login">
            <div class="conteudo caixa-login">
                <div class="texto-login">
                    <p class="subtitulo">Área Cliente</p>
                    <h1>Entra na tua conta</h1>
                    <p class="texto-suporte">
                        Acede ao teu perfil para consultares o teu plano, veres os teus dados
                        e acompanhares a tua atividade no ginásio.
                    </p>

                    <div class="lista-login">
                        <p>Consulta o teu plano atual</p>
                        <p>Vê as aulas em que estás inscrito</p>
                        <p>Entra com username ou email</p>
                    </div>
                </div>

                <div class="bloco-login">
                    <h2>Login</h2>
                    <?php if ($error) { ?>
                        <p class="mensagem erro"><?= h($error) ?></p>
                    <?php } ?>

                    <form action="action_login.php" method="post">
                        <div class="campo-login">
                            <label for="login">Username ou email</label>
                            <input type="text" id="login" name="login" required>
                        </div>

                        <div class="campo-login">
                            <label for="password">Palavra-passe</label>
                            <input type="password" id="password" name="password" required>
                        </div>

                        <button type="submit" class="botao amarelo largo">Entrar</button>
                        <p class="texto-planos-login">
                            Ainda não és membro?
                            <a href="inscricao.php" class="link-login">Faz a tua inscrição</a>
                        </p>
                    </form>
                </div>
            </div>
        </section>
    </main>
<?php
}

function output_registration_page(array $plans, array $gyms, ?string $error = null, ?int $selectedPlan = null): void
{
?>
    <main class="pagina-insc">
        <section class="secção area-insc">
            <div class="conteudo caixa-insc">
                <div class="caixa">
                    <p class="subtitulo">Inscrição</p>
                    <h1>Faz a tua inscrição</h1>

                    <?php if ($error) { ?>
                        <p class="mensagem erro"><?= h($error) ?></p>
                    <?php } ?>

                    <form action="action_register.php" method="post">
                        <fieldset class="grupo">
                            <legend>Dados pessoais</legend>
                            <div class="campos">
                                <div class="campo">
                                    <label for="first_name">Primeiro nome</label>
                                    <input type="text" id="first_name" name="first_name" required>
                                </div>

                                <div class="campo">
                                    <label for="last_name">Apelido</label>
                                    <input type="text" id="last_name" name="last_name" required>
                                </div>

                                <div class="campo campo-largo">
                                    <label for="birth_date">Data de nascimento</label>
                                    <input type="date" id="birth_date" name="birth_date">
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="grupo">
                            <legend>Conta</legend>
                            <div class="campos">
                                <div class="campo">
                                    <label for="username">Username</label>
                                    <input type="text" id="username" name="username" required>
                                </div>

                                <div class="campo">
                                    <label for="password">Palavra-passe</label>
                                    <input type="password" id="password" name="password" required>
                                </div>

                                <div class="campo campo-largo">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" required>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="grupo">
                            <legend>Contacto</legend>
                            <div class="campos">
                                <div class="campo">
                                    <label for="phone">Telefone</label>
                                    <input type="tel" id="phone" name="phone">
                                </div>

                                <div class="campo">
                                    <label for="city">Cidade</label>
                                    <input type="text" id="city" name="city">
                                </div>

                                <div class="campo campo-largo">
                                    <label for="address">Rua e número</label>
                                    <input type="text" id="address" name="address">
                                </div>

                                <div class="campo">
                                    <label for="postal_code">Código postal</label>
                                    <input type="text" id="postal_code" name="postal_code" placeholder="4480-123">
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="grupo">
                            <legend>Plano e localização</legend>
                            <div class="campos">
                                <div class="campo">
                                    <label for="plan_id">Plano</label>
                                    <select id="plan_id" name="plan_id" required>
                                        <?php foreach ($plans as $plan) { ?>
                                            <option value="<?= (int)$plan['id'] ?>" <?= $selectedPlan === (int)$plan['id'] ? 'selected' : '' ?>>
                                                <?= h($plan['nome']) ?> - <?= number_format((float)$plan['preco_mensal'], 2, ',', '') ?>€/mês
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="campo">
                                    <label for="gym_id">Ginásio preferido</label>
                                    <select id="gym_id" name="gym_id" required>
                                        <?php foreach ($gyms as $gym) { ?>
                                            <option value="<?= (int)$gym['id'] ?>"><?= h($gym['nome']) ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </fieldset>

                        <label class="termos">
                            <input type="checkbox" name="terms" value="1" required>
                            <span>Aceito os termos e condições e a política de privacidade</span>
                        </label>

                        <button type="submit" class="botao amarelo largo">Continuar</button>
                    </form>
                </div>
            </div>
        </section>
    </main>
<?php
}
