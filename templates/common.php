<?php
function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function formatClassDay(string $day): string
{
    $days = [
        'segunda' => 'segunda',
        'terca' => 'terça',
        'quarta' => 'quarta',
        'quinta' => 'quinta',
        'sexta' => 'sexta',
        'sabado' => 'sábado',
        'domingo' => 'domingo',
    ];

    return $days[$day] ?? $day;
}

function drawHeader(string $title = 'LAFit', string $activePage = 'home', array $extraCss = []): void
{
    $loggedIn = isset($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($title) ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <?php foreach ($extraCss as $css) { ?>
        <link rel="stylesheet" href="<?= h($css) ?>">
    <?php } ?>
</head>

<body>
    <div class="barra-superior">
        <div class="conteudo topo">
            <p><i class="fa-solid fa-location-dot" style="color: rgb(255, 212, 59);"></i> Encontra o ginásio mais próximo de ti</p>
            <p><i class="fa-solid fa-phone" style="color: rgb(255, 212, 59);"></i> Contacto: 911 978 544</p>
        </div>
    </div>

    <header class="cabecalho">
        <div class="conteudo linha-cabecalho">
            <a href="index.php" class="logo">
                <img src="../images/logo.png" alt="Logo da LAFit" class="logo-img">
                <span class="logo-nome">LAFit</span>
            </a>

            <nav class="menu">
                <a href="index.php" class="<?= $activePage === 'home' ? 'ativo' : '' ?>">Início</a>
                <a href="index.php#vantagens">Vantagens</a>
                <?php if (!$loggedIn) { ?>
                    <a href="index.php#planos">Planos</a>
                <?php } ?>
                <a href="index.php#espacos">Espaços</a>
                <a href="index.php#aulas-destaque" class="<?= $activePage === 'aulas' ? 'ativo' : '' ?>">Aulas</a>
                <?php if (($_SESSION['role'] ?? '') === 'administrador') { ?>
                    <a href="admin.php" class="<?= $activePage === 'admin' ? 'ativo' : '' ?>">Admin</a>
                <?php } ?>
                <a href="#contactos">Contactos</a>
            </nav>

            <div class="ações-topo">
                <?php if ($loggedIn) { ?>
                    <?php
                    $profilePage = match ($_SESSION['role'] ?? '') {
                        'treinador' => 'trainer.php',
                        'administrador' => 'admin.php',
                        default => 'perfil.php',
                    };
                    ?>
                    <a href="<?= $profilePage ?>" class="botao cliente">Olá, <?= h($_SESSION['username']) ?></a>
                    <a href="../actions/action_logout.php" class="botao amarelo">Sair</a>
                <?php } else { ?>
                    <a href="login.php" class="botao cliente">Iniciar Sessão</a>
                    <a href="inscricao.php" class="botao amarelo">Aderir agora</a>
                <?php } ?>
            </div>
        </div>
    </header>
<?php
}

function drawFooter(): void
{
?>
    <footer class="rodape" id="contactos">
        <div class="conteudo rodape-caixa">
            <div>
                <h3>LAFit</h3>
                <p>Juntos para uma melhor versão de ti</p>
            </div>

            <div>
                <h3>Ginásios</h3>
                <p>LA</p>
                <p>Caxinas</p>
                <p>Póvoa de Varzim</p>
                <p>Ramalde</p>
            </div>

            <div>
                <h3>Contactos</h3>
                <p>911 978 544</p>
                <p>lafit@email.pt</p>
            </div>

            <div>
                <h3>Morada</h3>
                <p>Largo Carlos Araújo</p>
                <p>4480-123 - Vila do Conde</p>
            </div>
        </div>
    </footer>
</body>

</html>
<?php
}
