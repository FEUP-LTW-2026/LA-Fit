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
    <script src="../javascript/script.js" defer></script>
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

            <?php
            $onProfilePage = in_array($activePage, ['perfil', 'trainer', 'admin', 'report'], true);
            $hideNav = $onProfilePage || ($loggedIn && $activePage === 'aulas');
            ?>
            <?php if (!$hideNav) { ?>
            <nav class="menu">
                <a href="index.php" class="<?= $activePage === 'home' ? 'ativo' : '' ?>">Início</a>
                <a href="index.php#vantagens">Vantagens</a>
                <?php if (!$loggedIn) { ?>
                    <a href="index.php#planos">Planos</a>
                <?php } ?>
                <a href="index.php#espacos">Espaços</a>
                <a href="index.php#aulas-destaque" class="<?= $activePage === 'aulas' ? 'ativo' : '' ?>">Aulas</a>
                <a href="#contactos">Contactos</a>
            </nav>
            <?php } elseif ($activePage === 'perfil') { ?>
            <nav class="menu">
                <a href="#perfil">Perfil</a>
                <a href="#perfil-aulas">Aulas</a>
                <a href="#perfil-equipamentos">Equipamentos</a>
                <a href="#perfil-progresso">Progresso</a>
            </nav>
            <?php } elseif ($activePage === 'trainer') { ?>
            <nav class="menu">
                <a href="#trainer">Perfil</a>
                <a href="#trainer-aulas">Aulas</a>
            </nav>
            <?php } elseif ($activePage === 'admin') { ?>
            <nav class="menu">
                <a href="#admin">Geral</a>
                <a href="#admin-contas">Contas</a>
                <a href="#admin-aulas">Aulas</a>
                <a href="#admin-equipamentos">Equipamentos</a>
            </nav>
            <?php } ?>

            <div class="acoes-topo">
                <?php if ($loggedIn) { ?>
                    <?php
                    $profilePage = 'perfil.php';
                    ?>
                    <?php if (!$onProfilePage) { ?>
                        <a href="<?= $profilePage ?>" class="botao cliente">Olá, <?= h($_SESSION['username']) ?></a>
                    <?php } elseif ($activePage === 'perfil') { ?>
                        <a href="report.php" class="botao cliente">Reportar problema</a>
                    <?php } elseif ($activePage === 'admin') { ?>
                        <a href="report.php" class="botao cliente">Ver reportes</a>
                    <?php } elseif ($activePage === 'report') { ?>
                        <a href="perfil.php" class="botao cliente">Área do cliente</a>
                    <?php } ?>
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
