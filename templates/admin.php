<?php
function drawAdminPage(array $users, array $plans, array $gyms, array $classes, array $trainers, array $equipment, ?array $editingUser, ?array $editingClass, ?array $editingEquipment, array $messages = [], array $overview = [], array $filters = []): void
{
    $isEditing = $editingUser !== null;
    $isEditingClass = $editingClass !== null;
    $isEditingEquipment = $editingEquipment !== null;
    $formAction = '../actions/action_admin_save_user.php';
    $selectedRole = $editingUser['papel'] ?? 'membro';
    $selectedStatus = $editingUser['estado'] ?? 'ativo';
?>
    <main class="pagina-admin" id="admin">
        <section class="seccao">
            <div class="conteudo">
                <?php if (!$isEditing && !$isEditingClass && !$isEditingEquipment) { ?>
                <div class="titulo">
                    <p class="subtitulo">Administração</p>
                    <h1>Painel de gestão</h1>
                    <p>Gere contas, aulas, equipamentos e reportes do ginásio.</p>
                </div>
                <?php } ?>

                <?php if (!empty($messages['success'])) { ?>
                    <p class="mensagem sucesso"><?= h($messages['success']) ?></p>
                <?php } ?>
                <?php if (!empty($messages['error'])) { ?>
                    <p class="mensagem erro"><?= h($messages['error']) ?></p>
                <?php } ?>

                <?php if (!$isEditing && !$isEditingClass && !$isEditingEquipment && !empty($overview)) { drawAdminOverview($overview); } ?>

                <?php if (!$isEditingClass && !$isEditingEquipment) { ?>
                <section class="painel painel-editar-perfil painel-form" id="admin-contas">
                    <div class="cabecalho-painel">
                        <h2><?= $isEditing ? 'Editar conta' : 'Criar conta' ?></h2>
                        <?php if ($isEditing) { ?>
                            <a href="profile.php" class="botao claro-voltar">Cancelar</a>
                        <?php } ?>
                    </div>

                    <form action="<?= h($formAction) ?>" method="post">
                        <?= csrfField() ?>
                        <?php if ($isEditing) { ?>
                            <input type="hidden" name="user_id" value="<?= (int)$editingUser['id'] ?>">
                            <input type="hidden" id="role" name="role" value="<?= h($selectedRole) ?>">
                        <?php } ?>

                        <fieldset class="grupo">
                            <legend>Dados pessoais</legend>
                            <div class="campos">
                                <div class="campo">
                                    <label for="first_name">Primeiro nome</label>
                                    <input type="text" id="first_name" name="first_name" value="<?= h($editingUser['nome'] ?? '') ?>" required>
                                </div>
                                <div class="campo">
                                    <label for="last_name">Apelido</label>
                                    <input type="text" id="last_name" name="last_name" value="<?= h($editingUser['apelido'] ?? '') ?>" required>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="grupo">
                            <legend>Conta</legend>
                            <div class="campos">
                                <div class="campo">
                                    <label for="username">Username</label>
                                    <input type="text" id="username" name="username" value="<?= h($editingUser['nome_utilizador'] ?? '') ?>" required>
                                </div>
                                <div class="campo">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" value="<?= h($editingUser['email'] ?? '') ?>" required>
                                </div>
                                <div class="campo">
                                    <label for="password"><?= $isEditing ? 'Nova palavra-passe' : 'Palavra-passe' ?></label>
                                    <input type="password" id="password" name="password" <?= $isEditing ? '' : 'required' ?>>
                                </div>
                                <div class="campo">
                                    <label for="status">Estado</label>
                                    <select id="status" name="status" required>
                                        <option value="ativo" <?= $selectedStatus === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                        <option value="inativo" <?= $selectedStatus === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                                    </select>
                                </div>
                                <?php if (!$isEditing) { ?>
                                    <div class="campo">
                                        <label for="role">Tipo de conta</label>
                                        <select id="role" name="role" required>
                                            <option value="membro" <?= $selectedRole === 'membro' ? 'selected' : '' ?>>Membro</option>
                                            <option value="treinador" <?= $selectedRole === 'treinador' ? 'selected' : '' ?>>Treinador</option>
                                        </select>
                                    </div>
                                <?php } ?>
                            </div>
                        </fieldset>

                        <fieldset class="grupo" id="fieldset-membro">
                            <legend>Dados de membro</legend>
                            <div class="campos">
                                <div class="campo">
                                    <label for="plan_id">Plano</label>
                                    <select id="plan_id" name="plan_id" required>
                                        <option value="">Sem plano</option>
                                        <?php foreach ($plans as $plan) { ?>
                                            <option value="<?= (int)$plan['id'] ?>" <?= (int)($editingUser['plano_id'] ?? 0) === (int)$plan['id'] ? 'selected' : '' ?>>
                                                <?= h($plan['nome']) ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="campo">
                                    <label for="gym_id">Ginásio</label>
                                    <select id="gym_id" name="gym_id">
                                        <option value="">Sem ginásio</option>
                                        <?php foreach ($gyms as $gym) { ?>
                                            <option value="<?= (int)$gym['id'] ?>" <?= (int)($editingUser['ginasio_id'] ?? 0) === (int)$gym['id'] ? 'selected' : '' ?>>
                                                <?= h($gym['nome']) ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="grupo" id="fieldset-treinador">
                            <legend>Dados de treinador</legend>
                            <div class="campos">
                                <div class="campo campo-largo">
                                    <label for="bio">Biografia</label>
                                    <input type="text" id="bio" name="bio" value="<?= h($editingUser['biografia'] ?? '') ?>">
                                </div>
                                <div class="campo campo-largo">
                                    <label>Especializações</label>
                                    <?php
                                    $especOpts = ['Cycling', 'Pilates', 'Hyrox', 'Kickbox', 'Karaté', 'Funcional'];
                                    $especAtivas = array_map('trim', explode(',', $editingUser['especializacoes'] ?? ''));
                                    ?>
                                    <div class="checklist">
                                        <?php foreach ($especOpts as $opt) { ?>
                                            <label class="checklist-item">
                                                <input type="checkbox" name="specializations[]" value="<?= h($opt) ?>" <?= in_array($opt, $especAtivas, true) ? 'checked' : '' ?>>
                                                <?= h($opt) ?>
                                            </label>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="campo">
                                    <label for="certifications">Certificações</label>
                                    <input type="text" id="certifications" name="certifications" value="<?= h($editingUser['certificacoes'] ?? '') ?>">
                                </div>
                            </div>
                        </fieldset>

                        <div class="acoes-linha">
                            <button type="submit" class="botao amarelo"><?= $isEditing ? 'Guardar alterações' : 'Criar conta' ?></button>
                        </div>
                    </form>
                    <?php if ($isEditing) { ?>
                    <form action="../actions/action_admin_save_user.php" method="post" data-confirm="Tens a certeza que queres elevar esta conta para administrador? Esta ação não pode ser revertida." class="margem-topo">
                        <?= csrfField() ?>
                        <input type="hidden" name="_action" value="elevate">
                        <input type="hidden" name="user_id" value="<?= (int)$editingUser['id'] ?>">
                        <button type="submit" class="botao cliente">Elevar para Admin</button>
                    </form>
                    <?php } ?>

                </section>
                <?php } ?>

                <?php if (!$isEditing && !$isEditingClass && !$isEditingEquipment) { ?>
                <?php
                    $userFilters   = $filters['user']   ?? [];
                    $hasUserFilter = !empty($userFilters['papel']) || !empty($userFilters['estado']) || !empty($userFilters['ordenar']);
                    $expandUsers   = ($filters['expand'] ?? '') === 'contas' || $hasUserFilter;
                ?>
                <section class="painel painel-lista" id="admin-contas-lista">
                    <div class="cabecalho-painel">
                        <h2>Membros e treinadores</h2>
                        <button type="button" class="botao cliente admin-ver-mais" data-target="admin-contas-lista">Ver mais</button>
                    </div>

                    <div class="admin-filtros" <?= $expandUsers ? '' : 'hidden' ?>>
                        <form class="class-filters" action="profile.php" method="get">
                            <input type="hidden" name="expand" value="contas">
                            <div class="filter-field">
                                <label for="af-users-nome">Nome</label>
                                <input type="search" id="af-users-nome" name="admin_users_nome" placeholder="Pesquisar por nome..." autocomplete="off">
                            </div>
                            <div class="filter-field">
                                <label for="af-users-papel">Tipo</label>
                                <select id="af-users-papel" name="admin_users_papel">
                                    <option value="">Todos</option>
                                    <option value="membro"    <?= ($userFilters['papel'] ?? '') === 'membro'    ? 'selected' : '' ?>>Membro</option>
                                    <option value="treinador" <?= ($userFilters['papel'] ?? '') === 'treinador' ? 'selected' : '' ?>>Treinador</option>
                                </select>
                            </div>
                            <div class="filter-field">
                                <label for="af-users-estado">Estado</label>
                                <select id="af-users-estado" name="admin_users_estado">
                                    <option value="">Todos</option>
                                    <option value="ativo"   <?= ($userFilters['estado'] ?? '') === 'ativo'   ? 'selected' : '' ?>>Ativo</option>
                                    <option value="inativo" <?= ($userFilters['estado'] ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                                </select>
                            </div>
                            <div class="filter-field">
                                <label for="af-users-ordenar">Ordenar por</label>
                                <select id="af-users-ordenar" name="admin_users_ordenar">
                                    <option value="">Predefinido</option>
                                    <option value="nome_az"  <?= ($userFilters['ordenar'] ?? '') === 'nome_az'  ? 'selected' : '' ?>>Nome A → Z</option>
                                    <option value="nome_za"  <?= ($userFilters['ordenar'] ?? '') === 'nome_za'  ? 'selected' : '' ?>>Nome Z → A</option>
                                    <option value="recente"  <?= ($userFilters['ordenar'] ?? '') === 'recente'  ? 'selected' : '' ?>>Mais recente</option>
                                    <option value="antigo"   <?= ($userFilters['ordenar'] ?? '') === 'antigo'   ? 'selected' : '' ?>>Mais antigo</option>
                                </select>
                            </div>
                            <div class="filter-actions">
                                <button type="submit" class="botao amarelo">Filtrar</button>
                                <a href="profile.php?expand=contas" class="botao cliente">Limpar</a>
                            </div>
                        </form>
                    </div>

                    <?php if (count($users) === 0) { ?>
                        <p>Ainda não existem contas para gerir.</p>
                    <?php } else { ?>
                        <div class="tabela-wrap">
                            <table class="tabela">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Detalhe</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $i => $user) { ?>
                                        <?php if ($i === 3) { ?></tbody><tbody class="admin-extra-rows" hidden><?php } ?>
                                        <tr>
                                            <td><?= h($user['nome'] . ' ' . $user['apelido']) ?></td>
                                            <td><?= h($user['nome_utilizador']) ?></td>
                                            <td><?= h($user['email']) ?></td>
                                            <td><?= h($user['papel']) ?></td>
                                            <td><span class="estado-conta estado-conta-<?= h($user['estado']) ?>"><?= h($user['estado']) ?></span></td>
                                            <td>
                                                <?php if ($user['papel'] === 'membro') { ?>
                                                    <?= h($user['plano_nome'] ?? 'Sem plano') ?> · <?= h($user['ginasio_nome'] ?? 'Sem ginásio') ?>
                                                <?php } else { ?>
                                                    <?= h($user['especializacoes'] ?? 'Sem especializações') ?>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <div class="acoes-linha">
                                                    <a href="profile.php?edit=<?= (int)$user['id'] ?>" class="botao claro-voltar">Editar</a>
                                                    <form action="../actions/action_admin_save_user.php" method="post" data-confirm="<?= $user['estado'] === 'ativo' ? 'Tens a certeza que queres desativar esta conta?' : 'Tens a certeza que queres ativar esta conta?' ?>">
                                                        <?= csrfField() ?>
                                                        <input type="hidden" name="_action" value="toggle">
                                                        <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                                                        <input type="hidden" name="status" value="<?= $user['estado'] === 'ativo' ? 'inativo' : 'ativo' ?>">
                                                        <button type="submit" class="botao cliente">
                                                            <?= $user['estado'] === 'ativo' ? 'Desativar' : 'Ativar' ?>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                </section>
                <?php } ?>

                <?php if (!$isEditing && !$isEditingEquipment) { drawAdminClassCatalog($classes, $trainers, $gyms, $editingClass, $filters['class'] ?? [], $filters['expand'] ?? ''); } ?>

                <?php if (!$isEditing && !$isEditingClass) { drawAdminEquipmentSection($equipment, $editingEquipment, $filters['eq'] ?? [], $filters['eq_options'] ?? [], $filters['expand'] ?? ''); } ?>

            </div>
        </section>
    </main>
<?php
}

function drawAdminOverview(array $overview): void
{
    $stats            = $overview['stats'];
    $manutencao       = $overview['manutencao'];
    $inativas         = $overview['inativas'];
    $canceladas       = $overview['canceladas'];
    $popularClasses   = $overview['popular_classes'] ?? [];
    $equipmentUsage   = $overview['equipment_usage'] ?? [];
    $memberRetention  = $overview['member_retention'] ?? [];

    $temAlertas = $stats['equipamentos_manutencao'] > 0
               || $stats['reportes_pendentes'] > 0
               || $stats['contas_inativas'] > 0
               || $stats['aulas_canceladas'] > 0;
?>
    <section class="painel painel-lista visao-geral" id="admin-geral">
        <h2>Visão geral do sistema</h2>

        <div class="grelha-stats">
            <div class="stat-card">
                <p><?= (int)$stats['membros_ativos'] ?></p>
                <p>Membros ativos</p>
            </div>
            <div class="stat-card">
                <p><?= (int)$stats['treinadores_ativos'] ?></p>
                <p>Treinadores ativos</p>
            </div>
            <div class="stat-card">
                <p><?= (int)$stats['aulas_agendadas'] ?></p>
                <p>Aulas agendadas</p>
            </div>
            <div class="stat-card <?= $stats['reportes_pendentes'] > 0 ? 'stat-card-alerta' : '' ?>">
                <p><?= (int)$stats['reportes_pendentes'] ?></p>
                <p>Reportes pendentes</p>
            </div>
            <div class="stat-card <?= $stats['equipamentos_manutencao'] > 0 ? 'stat-card-aviso' : '' ?>">
                <p><?= (int)$stats['equipamentos_manutencao'] ?></p>
                <p>Equipamentos em manutenção</p>
            </div>
        </div>

        <?php if ($temAlertas) { ?>
        <div class="alertas-sistema">
            <h3>Itens que precisam de atenção</h3>

            <?php if (count($manutencao) > 0) { ?>
            <div class="alerta-sistema alerta-aviso">
                <div class="alerta-sistema-corpo">
                    <p class="alerta-sistema-titulo">Equipamentos em manutenção (<?= count($manutencao) ?>)</p>
                    <ul class="alerta-sistema-lista">
                        <?php foreach ($manutencao as $item) { ?>
                            <li>
                                <?= h($item['nome']) ?> · <?= h($item['zona']) ?> · <?= (int)$item['quantidade'] ?> unidade<?= (int)$item['quantidade'] !== 1 ? 's' : '' ?>
                                <a href="profile.php?edit_equipment=<?= (int)$item['id'] ?>" class="alerta-link">Editar</a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
            <?php } ?>

            <?php if ($stats['reportes_pendentes'] > 0 || $stats['reportes_em_analise'] > 0) { ?>
            <div class="alerta-sistema alerta-erro">
                <div class="alerta-sistema-corpo">
                    <p class="alerta-sistema-titulo">
                        Reportes por resolver —
                        <?= (int)$stats['reportes_pendentes'] ?> pendente<?= $stats['reportes_pendentes'] != 1 ? 's' : '' ?>,
                        <?= (int)$stats['reportes_em_analise'] ?> em análise
                    </p>
                    <p class="alerta-sistema-desc">Acede à secção de reportes abaixo para responder.</p>
                </div>
            </div>
            <?php } ?>

            <?php if (count($inativas) > 0) { ?>
            <div class="alerta-sistema alerta-neutro">
                <div class="alerta-sistema-corpo">
                    <p class="alerta-sistema-titulo">Contas inativas (<?= count($inativas) ?>)</p>
                    <ul class="alerta-sistema-lista">
                        <?php foreach ($inativas as $user) { ?>
                            <li>
                                <?= h($user['nome'] . ' ' . $user['apelido']) ?> · <?= h($user['nome_utilizador']) ?> · <?= h($user['papel']) ?>
                                <a href="profile.php?edit=<?= (int)$user['id'] ?>" class="alerta-link">Editar</a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
            <?php } ?>

            <?php if (count($canceladas) > 0) { ?>
            <div class="alerta-sistema alerta-neutro">
                <div class="alerta-sistema-corpo">
                    <p class="alerta-sistema-titulo">Aulas canceladas (<?= count($canceladas) ?>)</p>
                    <ul class="alerta-sistema-lista">
                        <?php foreach ($canceladas as $aula) { ?>
                            <li>
                                <?= h($aula['nome']) ?> · <?= h(formatClassDay($aula['dia_semana'])) ?> <?= h($aula['inicio']) ?>–<?= h($aula['fim']) ?> · <?= h($aula['treinador_nome']) ?>
                                <a href="profile.php?edit_class=<?= (int)$aula['id'] ?>" class="alerta-link">Editar</a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } else { ?>
            <p class="sistema-ok">Tudo em ordem. Sem itens que precisem de atenção.</p>
        <?php } ?>

        <div class="grelha-analytics">
            <section class="analytics-card">
                <h3>Aulas mais populares</h3>
                <?php if (count($popularClasses) === 0) { ?>
                    <p>Nenhuma aula com inscrições por enquanto.</p>
                <?php } else { ?>
                    <ul class="analytics-list">
                        <?php foreach ($popularClasses as $class) { ?>
                            <li>
                                <div>
                                    <strong><?= h($class['nome']) ?></strong>
                                    <small><?= h($class['ginasio_nome']) ?> · <?= h($class['treinador_nome']) ?></small>
                                </div>
                                <span><?= (int)$class['inscritos'] ?> inscrito<?= (int)$class['inscritos'] !== 1 ? 's' : '' ?></span>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>
            </section>

            <section class="analytics-card">
                <h3>Uso do equipamento</h3>
                <ul class="analytics-list">
                    <li>
                        <span>Disponível</span>
                        <strong><?= (int)$equipmentUsage['disponivel']['item_count'] ?> itens</strong>
                    </li>
                    <li>
                        <span>Em uso</span>
                        <strong><?= (int)$equipmentUsage['ocupado']['item_count'] ?> itens</strong>
                    </li>
                    <li>
                        <span>Em manutenção</span>
                        <strong><?= (int)$equipmentUsage['manutencao']['item_count'] ?> itens</strong>
                    </li>
                </ul>
                <p class="analytics-note">Total de unidades reportadas: <?= (int)$equipmentUsage['disponivel']['total_quantity'] + (int)$equipmentUsage['ocupado']['total_quantity'] + (int)$equipmentUsage['manutencao']['total_quantity'] ?></p>
            </section>

            <section class="analytics-card">
                <h3>Retenção de membros</h3>
                <div class="analytics-metrics">
                    <p><strong><?= h($memberRetention['retention_rate']) ?>%</strong> com atividade nos últimos 30 dias</p>
                    <p><strong><?= h($memberRetention['participation_rate']) ?>%</strong> com inscrição ativa</p>
                    <p><strong><?= (int)$memberRetention['active_members'] ?></strong> membros ativos</p>
                </div>
            </section>
        </div>
    </section>
<?php
}

function drawAdminEquipmentSection(array $equipment, ?array $editingEquipment, array $eqFilters = [], array $eqFilterOptions = [], string $expand = ''): void
{
    $isEditing = $editingEquipment !== null;
    $formAction = '../actions/action_admin_equipment.php';
    $states = ['disponivel' => 'Disponível', 'ocupado' => 'Em uso', 'manutencao' => 'Manutenção'];
    $selectedState = $editingEquipment['estado'] ?? 'disponivel';
    $existingZones = array_unique(array_column($equipment, 'zona'));
    sort($existingZones);
?>
    <section class="painel painel-editar-perfil painel-form" id="admin-equipamentos">
        <div class="cabecalho-painel">
            <h2><?= $isEditing ? 'Editar equipamento' : 'Adicionar equipamento' ?></h2>
            <?php if ($isEditing) { ?>
                <a href="profile.php" class="botao claro-voltar">Cancelar</a>
            <?php } ?>
        </div>

        <form action="<?= h($formAction) ?>" method="post">
            <?= csrfField() ?>
            <?php if ($isEditing) { ?>
                <input type="hidden" name="equipment_id" value="<?= (int)$editingEquipment['id'] ?>">
            <?php } ?>

            <fieldset class="grupo">
                <legend>Equipamento</legend>
                <div class="campos">
                    <div class="campo">
                        <label for="eq_nome">Nome</label>
                        <input type="text" id="eq_nome" name="nome" value="<?= h($editingEquipment['nome'] ?? '') ?>" required>
                    </div>
                    <div class="campo">
                        <label for="eq_zona">Zona</label>
                        <select id="eq_zona" name="zona" required>
                            <?php
                            $zonasEquip = ['Cardio', 'Funcional', 'Musculação'];
                            foreach ($zonasEquip as $z) { ?>
                                <option value="<?= h($z) ?>" <?= ($editingEquipment['zona'] ?? '') === $z ? 'selected' : '' ?>><?= h($z) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="campo">
                        <label for="eq_estado">Estado</label>
                        <select id="eq_estado" name="estado" required>
                            <?php foreach ($states as $value => $label) { ?>
                                <option value="<?= h($value) ?>" <?= $selectedState === $value ? 'selected' : '' ?>>
                                    <?= h($label) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="campo">
                        <label for="eq_quantidade">Quantidade</label>
                        <input type="number" id="eq_quantidade" name="quantidade" min="1" value="<?= (int)($editingEquipment['quantidade'] ?? 1) ?>" required>
                    </div>
                </div>
            </fieldset>

            <button type="submit" class="botao amarelo"><?= $isEditing ? 'Guardar equipamento' : 'Adicionar equipamento' ?></button>
        </form>
    </section>

    <?php if (!$isEditing) { ?>
    <?php
        $hasEqFilter  = !empty($eqFilters['zona']) || !empty($eqFilters['estado']);
        $expandEq     = $expand === 'equipamentos' || $hasEqFilter;
        $allZones     = $eqFilterOptions['zones'] ?? $existingZones;
    ?>
    <section class="painel painel-lista" id="admin-equipamentos-lista">
        <div class="cabecalho-painel">
            <h2>Equipamentos</h2>
            <button type="button" class="botao cliente admin-ver-mais" data-target="admin-equipamentos-lista">Ver mais</button>
        </div>

        <div class="admin-filtros" <?= $expandEq ? '' : 'hidden' ?>>
            <form class="class-filters" action="profile.php" method="get">
                <input type="hidden" name="expand" value="equipamentos">
                <div class="filter-field">
                    <label for="af-eq-zona">Zona</label>
                    <select id="af-eq-zona" name="admin_eq_zona">
                        <option value="">Todas</option>
                        <?php foreach ($allZones as $zone) { ?>
                            <option value="<?= h($zone) ?>" <?= ($eqFilters['zona'] ?? '') === $zone ? 'selected' : '' ?>>
                                <?= h($zone) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="filter-field">
                    <label for="af-eq-estado">Estado</label>
                    <select id="af-eq-estado" name="admin_eq_estado">
                        <option value="">Todos</option>
                        <?php foreach ($states as $key => $label) { ?>
                            <option value="<?= h($key) ?>" <?= ($eqFilters['estado'] ?? '') === $key ? 'selected' : '' ?>>
                                <?= h($label) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="botao amarelo">Filtrar</button>
                    <a href="profile.php?expand=equipamentos" class="botao cliente">Limpar</a>
                </div>
            </form>
        </div>

        <?php if (count($equipment) === 0) { ?>
            <p>Ainda não existem equipamentos registados.</p>
        <?php } else { ?>
            <div class="tabela-wrap">
                <table class="tabela">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Zona</th>
                            <th>Estado</th>
                            <th>Quantidade</th>
                            <th>Atualizado em</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($equipment as $i => $item) { ?>
                            <?php if ($i === 3) { ?></tbody><tbody class="admin-extra-rows" hidden><?php } ?>
                            <tr>
                                <td><?= h($item['nome']) ?></td>
                                <td><?= h($item['zona']) ?></td>
                                <td><span class="estado-conta estado-conta-<?= h($item['estado']) ?>"><?= h($states[$item['estado']] ?? $item['estado']) ?></span></td>
                                <td><?= (int)$item['quantidade'] ?></td>
                                <td><?= h(substr($item['atualizado_em'] ?? '', 0, 16)) ?></td>
                                <td>
                                    <div class="acoes-linha">
                                        <a href="profile.php?edit_equipment=<?= (int)$item['id'] ?>" class="botao claro-voltar">Editar</a>
                                        <form action="../actions/action_admin_equipment.php" method="post" data-confirm="Tens a certeza que queres remover este equipamento?">
                        <?= csrfField() ?>
                        <input type="hidden" name="_action" value="delete">
                                            <input type="hidden" name="equipment_id" value="<?= (int)$item['id'] ?>">
                                            <button type="submit" class="botao cliente">Remover</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </section>
    <?php } ?>
<?php
}


function drawAdminClassCatalog(array $classes, array $trainers, array $gyms, ?array $editingClass, array $classFilters = [], string $expand = ''): void
{
    $isEditing = $editingClass !== null;
    $formAction = '../actions/action_class.php';
    $days = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
    $statuses = ['agendada' => 'Agendada', 'concluida' => 'Concluída', 'cancelada' => 'Cancelada'];
?>
    <section class="painel painel-editar-perfil painel-form" id="admin-aulas">
        <div class="cabecalho-painel">
            <h2><?= $isEditing ? 'Editar aula' : 'Criar aula' ?></h2>
            <?php if ($isEditing) { ?>
                <a href="profile.php" class="botao claro-voltar">Cancelar</a>
            <?php } ?>
        </div>

        <form action="<?= h($formAction) ?>" method="post">
            <?= csrfField() ?>
            <?php if ($isEditing) { ?>
                <input type="hidden" name="class_id" value="<?= (int)$editingClass['id'] ?>">
            <?php } ?>

            <fieldset class="grupo">
                <legend>Catálogo de aulas</legend>
                <div class="campos">
                    <div class="campo">
                        <label for="class_name">Nome</label>
                        <input type="text" id="class_name" name="name" value="<?= h($editingClass['nome'] ?? '') ?>" required>
                    </div>
                    <div class="campo">
                        <label for="class_type">Tipo</label>
                        <select id="class_type" name="type" required>
                            <?php
                            $tiposAula = ['cycling', 'funcional', 'hyrox', 'karate', 'kickbox', 'pilates', 'outro'];
                            foreach ($tiposAula as $t) { ?>
                                <option value="<?= h($t) ?>" <?= ($editingClass['tipo'] ?? '') === $t ? 'selected' : '' ?>><?= h(ucfirst($t)) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="campo campo-largo">
                        <label for="class_description">Descrição</label>
                        <textarea id="class_description" name="description" rows="3"><?= h($editingClass['descricao'] ?? '') ?></textarea>
                    </div>
                    <div class="campo">
                        <label for="class_trainer">Treinador</label>
                        <select id="class_trainer" name="trainer_id" required>
                            <option value="">Escolher treinador</option>
                            <?php foreach ($trainers as $trainer) { ?>
                                <option value="<?= (int)$trainer['id'] ?>" <?= (int)($editingClass['treinador_id'] ?? 0) === (int)$trainer['id'] ? 'selected' : '' ?>>
                                    <?= h($trainer['nome']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="campo">
                        <label for="class_gym">Ginásio</label>
                        <select id="class_gym" name="gym_id" required>
                            <option value="">Escolher ginásio</option>
                            <?php foreach ($gyms as $gym) { ?>
                                <option value="<?= (int)$gym['id'] ?>" <?= (int)($editingClass['ginasio_id'] ?? 0) === (int)$gym['id'] ? 'selected' : '' ?>>
                                    <?= h($gym['nome']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="campo">
                        <label for="class_day">Dia</label>
                        <select id="class_day" name="day" required>
                            <?php foreach ($days as $day) { ?>
                                <option value="<?= h($day) ?>" <?= ($editingClass['dia_semana'] ?? 'segunda') === $day ? 'selected' : '' ?>>
                                    <?= h(formatClassDay($day)) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="campo">
                        <label for="class_start">Início</label>
                        <input type="time" id="class_start" name="start" value="<?= h($editingClass['inicio'] ?? '') ?>" required>
                    </div>
                    <div class="campo">
                        <label for="class_end">Fim</label>
                        <input type="time" id="class_end" name="end" value="<?= h($editingClass['fim'] ?? '') ?>" required>
                    </div>
                    <div class="campo">
                        <label for="class_capacity">Lotação</label>
                        <input type="number" id="class_capacity" name="capacity" min="1" value="<?= h($editingClass['lotacao'] ?? '20') ?>" required>
                    </div>
                    <div class="campo">
                        <label for="class_room">Sala</label>
                        <input type="text" id="class_room" name="room" value="<?= h($editingClass['sala'] ?? '') ?>">
                    </div>
                    <div class="campo">
                        <label for="class_status">Estado</label>
                        <select id="class_status" name="status" required>
                            <?php foreach ($statuses as $value => $label) { ?>
                                <option value="<?= h($value) ?>" <?= ($editingClass['estado'] ?? 'agendada') === $value ? 'selected' : '' ?>>
                                    <?= h($label) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </fieldset>

            <button type="submit" class="botao amarelo"><?= $isEditing ? 'Guardar aula' : 'Criar aula' ?></button>
        </form>
    </section>

    <?php if (!$isEditing) { ?>
    <?php
        $hasClassFilter = !empty($classFilters['type'])    || !empty($classFilters['trainer']) || !empty($classFilters['gym'])
                       || !empty($classFilters['day'])  || !empty($classFilters['estado']);
        $expandClasses  = $expand === 'aulas' || $hasClassFilter;
        $dayLabels = [
            'segunda' => 'Segunda', 'terca' => 'Terça', 'quarta' => 'Quarta',
            'quinta'  => 'Quinta',  'sexta' => 'Sexta', 'sabado' => 'Sábado', 'domingo' => 'Domingo',
        ];
    ?>
    <section class="painel painel-lista" id="admin-aulas-lista">
        <div class="cabecalho-painel">
            <h2>Aulas</h2>
            <button type="button" class="botao cliente admin-ver-mais" data-target="admin-aulas-lista">Ver mais</button>
        </div>

        <div class="admin-filtros" <?= $expandClasses ? '' : 'hidden' ?>>
            <form class="class-filters" action="profile.php" method="get">
                <input type="hidden" name="expand" value="aulas">
                <?php $tiposAula = ['cycling', 'funcional', 'hyrox', 'karate', 'kickbox', 'pilates', 'outro']; ?>
                <div class="filter-field">
                    <label for="af-class-tipo">Tipo</label>
                    <select id="af-class-tipo" name="admin_classes_tipo">
                        <option value="">Todos</option>
                        <?php foreach ($tiposAula as $t) { ?>
                            <option value="<?= h($t) ?>" <?= ($classFilters['type'] ?? '') === $t ? 'selected' : '' ?>><?= h(ucfirst($t)) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="filter-field">
                    <label for="af-class-trainer">Treinador</label>
                    <select id="af-class-trainer" name="admin_classes_trainer">
                        <option value="">Todos</option>
                        <?php foreach ($trainers as $trainer) { ?>
                            <option value="<?= (int)$trainer['id'] ?>" <?= (int)($classFilters['trainer'] ?? 0) === (int)$trainer['id'] ? 'selected' : '' ?>>
                                <?= h($trainer['nome']) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="filter-field">
                    <label for="af-class-gym">Ginásio</label>
                    <select id="af-class-gym" name="admin_classes_gym">
                        <option value="">Todos</option>
                        <?php foreach ($gyms as $gym) { ?>
                            <option value="<?= (int)$gym['id'] ?>" <?= (int)($classFilters['gym'] ?? 0) === (int)$gym['id'] ? 'selected' : '' ?>>
                                <?= h($gym['nome']) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="filter-field">
                    <label for="af-class-day">Dia</label>
                    <select id="af-class-day" name="admin_classes_day">
                        <option value="">Todos</option>
                        <?php foreach ($dayLabels as $val => $label) { ?>
                            <option value="<?= h($val) ?>" <?= ($classFilters['day'] ?? '') === $val ? 'selected' : '' ?>>
                                <?= h($label) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="filter-field">
                    <label for="af-class-estado">Estado</label>
                    <select id="af-class-estado" name="admin_classes_estado">
                        <option value="">Todos</option>
                        <?php foreach ($statuses as $key => $label) { ?>
                            <option value="<?= h($key) ?>" <?= ($classFilters['estado'] ?? '') === $key ? 'selected' : '' ?>>
                                <?= h($label) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="botao amarelo">Filtrar</button>
                    <a href="profile.php?expand=aulas" class="botao cliente">Limpar</a>
                </div>
            </form>
        </div>

        <?php if (count($classes) === 0) { ?>
            <p>Ainda não existem aulas no catálogo.</p>
        <?php } else { ?>
            <div class="tabela-wrap">
                <table class="tabela tabela-aulas-admin">
                    <thead>
                        <tr>
                            <th>Aula</th>
                            <th>Treinador</th>
                            <th>Ginásio</th>
                            <th>Horário</th>
                            <th>Lotação</th>
                            <th>Estado</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classes as $i => $class) { ?>
                            <?php if ($i === 3) { ?></tbody><tbody class="admin-extra-rows" hidden><?php } ?>
                            <tr>
                                <td>
                                    <strong><?= h($class['nome']) ?></strong>
                                    <span><?= h($class['tipo']) ?> · <?= h($class['sala'] ?: 'Sem sala') ?></span>
                                </td>
                                <td><?= h($class['treinador_nome']) ?></td>
                                <td><?= h($class['ginasio_nome']) ?></td>
                                <td><?= h(formatClassDay($class['dia_semana'])) ?> · <?= h($class['inicio']) ?> - <?= h($class['fim']) ?></td>
                                <td><?= (int)$class['inscritos'] ?> / <?= (int)$class['lotacao'] ?></td>
                                <td><span class="estado-conta estado-conta-<?= h($class['estado']) ?>"><?= h($class['estado']) ?></span></td>
                                <td>
                                    <div class="acoes-linha">
                                        <a href="profile.php?edit_class=<?= (int)$class['id'] ?>" class="botao claro-voltar">Editar</a>
                                        <a href="class_reviews.php?aula=<?= (int)$class['id'] ?>" class="botao cliente">Ver avaliações</a>
                                        <?php if ($class['estado'] !== 'cancelada') { ?>
                                            <form action="../actions/action_class.php" method="post" data-confirm="Tens a certeza que queres remover esta aula do catálogo?">
                        <?= csrfField() ?>
                        <input type="hidden" name="_action" value="delete">
                                                <input type="hidden" name="class_id" value="<?= (int)$class['id'] ?>">
                                                <button type="submit" class="botao cliente">Remover</button>
                                            </form>
                                        <?php } ?>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </section>
    <?php } ?>
<?php
}
