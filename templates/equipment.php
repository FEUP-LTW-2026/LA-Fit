<?php
function drawEquipmentPage(array $equipmentByZone, array $summary, array $filters = [], array $filterOptions = []): void
{
?>
    <main class="pagina-equipamentos">
        <section class="seccao">
            <div class="conteudo">
                <div class="titulo">
                    <p class="subtitulo">Sala principal</p>
                    <h1>Disponibilidade dos equipamentos</h1>
                    <p>Consulta o estado atual das máquinas e zonas antes do treino.</p>
                </div>

                <div class="resumo-equipamentos">
                    <?php drawEquipmentSummaryItem('disponivel', 'Disponíveis', $summary['disponivel'] ?? 0, 'fa-circle-check'); ?>
                    <?php drawEquipmentSummaryItem('ocupado', 'Em uso', $summary['ocupado'] ?? 0, 'fa-clock'); ?>
                    <?php drawEquipmentSummaryItem('manutencao', 'Manutenção', $summary['manutencao'] ?? 0, 'fa-screwdriver-wrench'); ?>
                </div>

                <?php drawEquipmentFilters($filters, $filterOptions); ?>

                <?php if (count($equipmentByZone) === 0) { ?>
                    <div class="class-empty-state">
                        <h2>Nenhum equipamento encontrado com esses filtros.</h2>
                        <p>Experimenta ajustar a zona ou o estado para veres mais opções.</p>
                        <a href="equipment.php" class="botao cliente">Limpar filtros</a>
                    </div>
                <?php } else { ?>
                    <div class="zonas-equipamentos">
                        <?php foreach ($equipmentByZone as $zone => $items) { ?>
                            <section class="zona-equipamentos">
                                <div class="cabecalho-zona">
                                    <h2><?= h($zone) ?></h2>
                                    <span><?= count($items) ?> <?= count($items) === 1 ? 'equipamento' : 'equipamentos' ?></span>
                                </div>

                                <div class="lista-equipamentos">
                                    <?php foreach ($items as $equipment) drawEquipmentItem($equipment); ?>
                                </div>
                            </section>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </section>
    </main>
<?php
}

function drawEquipmentFilters(array $filters, array $filterOptions, string $action = 'equipment.php'): void
{
    $zones = $filterOptions['zones'] ?? [];
    $states = ['disponivel' => 'Disponível', 'ocupado' => 'Em uso', 'manutencao' => 'Manutenção'];
?>
    <form class="class-filters" action="<?= h($action) ?>" method="get">
        <div class="filter-field">
            <label for="eq-nome">Nome</label>
            <input type="search" id="eq-nome" name="nome" value="<?= h($filters['nome'] ?? '') ?>" placeholder="Pesquisar equipamento..." autocomplete="off">
        </div>
        <div class="filter-field">
            <label for="eq-zona">Zona</label>
            <select id="eq-zona" name="zona">
                <option value="">Todas</option>
                <?php foreach ($zones as $zone) { ?>
                    <option value="<?= h($zone) ?>" <?= ($filters['zona'] ?? '') === $zone ? 'selected' : '' ?>>
                        <?= h($zone) ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="filter-field">
            <label for="eq-estado">Estado</label>
            <select id="eq-estado" name="estado">
                <option value="">Todos</option>
                <?php foreach ($states as $key => $label) { ?>
                    <option value="<?= h($key) ?>" <?= ($filters['estado'] ?? '') === $key ? 'selected' : '' ?>>
                        <?= h($label) ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="botao amarelo">Filtrar</button>
            <a href="<?= h($action) ?>" class="botao cliente">Limpar</a>
        </div>
    </form>
<?php
}

function drawEquipmentSummaryItem(string $state, string $label, int $total, string $icon): void
{
?>
    <article class="resumo-equipamento estado-<?= h($state) ?>">
        <i class="fa-solid <?= h($icon) ?>"></i>
        <div>
            <p><?= h($label) ?></p>
            <strong><?= $total ?></strong>
        </div>
    </article>
<?php
}

function drawEquipmentItem(array $equipment): void
{
    $state = $equipment['estado'];
    $updatedAt = substr($equipment['atualizado_em'] ?? '', 0, 16);
?>
    <article class="equipamento">
        <div>
            <h3><?= h($equipment['nome']) ?></h3>
            <p><?= (int)$equipment['quantidade'] ?> <?= (int)$equipment['quantidade'] === 1 ? 'unidade' : 'unidades' ?></p>
        </div>

        <div class="estado-equipamento estado-<?= h($state) ?>">
            <span><?= h(formatEquipmentState($state)) ?></span>
            <?php if ($updatedAt !== '') { ?>
                <small>Atualizado em <?= h($updatedAt) ?></small>
            <?php } ?>
        </div>
    </article>
<?php
}

function formatEquipmentState(string $state): string
{
    $states = [
        'disponivel' => 'Disponível',
        'ocupado' => 'Em uso',
        'manutencao' => 'Manutenção',
    ];

    return $states[$state] ?? $state;
}
