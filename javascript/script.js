function attachConfirmForms() {
    const forms = document.querySelectorAll('form[data-confirm]');
    for (const form of forms) {
        form.addEventListener('submit', function(e) {
            const message = this.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    }

    const buttons = document.querySelectorAll('button[data-confirm]');
    for (const btn of buttons) {
        btn.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    }
}

function attachFlashMessages() {
    const messages = document.querySelectorAll('.mensagem');
    for (const message of messages) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = '×';
        btn.className = 'mensagem-fechar';
        btn.addEventListener('click', function() {
            message.remove();
        });
        message.appendChild(btn);

        setTimeout(function() {
            if (message.parentElement) message.remove();
        }, 5000);
    }
}

function attachAdminRoleSwitch() {
    const roleEl = document.getElementById('role');
    const membroFieldset = document.getElementById('fieldset-membro');
    const treinadorFieldset = document.getElementById('fieldset-treinador');

    if (!roleEl || !membroFieldset || !treinadorFieldset) return;

    function update() {
        const isMembro = roleEl.value === 'membro';
        membroFieldset.style.display = isMembro ? '' : 'none';
        treinadorFieldset.style.display = isMembro ? 'none' : '';
        const planSelect = document.getElementById('plan_id');
        if (planSelect) planSelect.required = isMembro;
    }

    update();
    if (roleEl.tagName === 'SELECT') {
        roleEl.addEventListener('change', update);
    }
}

function attachEquipmentRefresh() {
    const zonas = document.querySelector('.zonas-equipamentos');
    const resumo = document.querySelector('.resumo-equipamentos');

    if (!zonas || !resumo) return;

    function formatEstado(estado) {
        const map = { disponivel: 'Disponível', ocupado: 'Em uso', manutencao: 'Manutenção' };
        return map[estado] || estado;
    }

    function buildResumo(summary) {
        const items = resumo.querySelectorAll('.resumo-equipamento');
        for (const item of items) {
            const strong = item.querySelector('strong');
            if (!strong) continue;
            if (item.classList.contains('estado-disponivel')) strong.textContent = summary.disponivel;
            else if (item.classList.contains('estado-ocupado')) strong.textContent = summary.ocupado;
            else if (item.classList.contains('estado-manutencao')) strong.textContent = summary.manutencao;
        }
    }

    function buildZonas(equipmentByZone) {
        zonas.innerHTML = '';
        for (const [zone, items] of Object.entries(equipmentByZone)) {
            const section = document.createElement('section');
            section.className = 'zona-equipamentos';

            const cabecalho = document.createElement('div');
            cabecalho.className = 'cabecalho-zona';
            const h2 = document.createElement('h2');
            h2.textContent = zone;
            const span = document.createElement('span');
            span.textContent = items.length + ' ' + (items.length === 1 ? 'equipamento' : 'equipamentos');
            cabecalho.appendChild(h2);
            cabecalho.appendChild(span);

            const lista = document.createElement('div');
            lista.className = 'lista-equipamentos';

            for (const eq of items) {
                const article = document.createElement('article');
                article.className = 'equipamento';

                const info = document.createElement('div');
                const h3 = document.createElement('h3');
                h3.textContent = eq.nome;
                const p = document.createElement('p');
                p.textContent = eq.quantidade + ' ' + (parseInt(eq.quantidade) === 1 ? 'unidade' : 'unidades');
                info.appendChild(h3);
                info.appendChild(p);

                const estadoDiv = document.createElement('div');
                estadoDiv.className = 'estado-equipamento estado-' + eq.estado;
                const badge = document.createElement('span');
                badge.textContent = formatEstado(eq.estado);
                estadoDiv.appendChild(badge);

                if (eq.atualizado_em) {
                    const small = document.createElement('small');
                    small.textContent = 'Atualizado em ' + eq.atualizado_em.substring(0, 16);
                    estadoDiv.appendChild(small);
                }

                article.appendChild(info);
                article.appendChild(estadoDiv);
                lista.appendChild(article);
            }

            section.appendChild(cabecalho);
            section.appendChild(lista);
            zonas.appendChild(section);
        }
    }

    function refresh() {
        const urlParams = new URLSearchParams(window.location.search);
        const params = new URLSearchParams();
        if (urlParams.get('zona')) params.set('zona', urlParams.get('zona'));
        if (urlParams.get('estado')) params.set('estado', urlParams.get('estado'));

        fetch('api_equipment.php?' + params.toString())
            .then(function(response) { return response.json(); })
            .then(function(data) {
                buildResumo(data.summary);
                buildZonas(data.equipmentByZone);
            })
            .catch(function() {});
    }

    setInterval(refresh, 30000);
}

function attachHoverAnimations() {
    const liftCards = document.querySelectorAll('.painel, .aula, .vantagem, .plano, .resumo-equipamento');
    for (const card of liftCards) {
        card.addEventListener('mouseenter', function() {
            this.classList.add('hover-lift');
        });
        card.addEventListener('mouseleave', function() {
            this.classList.remove('hover-lift');
        });
    }

    const equipamentos = document.querySelectorAll('.equipamento');
    for (const eq of equipamentos) {
        eq.addEventListener('mouseenter', function() {
            this.classList.add('hover-highlight');
        });
        eq.addEventListener('mouseleave', function() {
            this.classList.remove('hover-highlight');
        });
    }

    const espacos = document.querySelectorAll('.espaco');
    for (const espaco of espacos) {
        espaco.addEventListener('mouseenter', function() {
            this.classList.add('hover-zoom');
        });
        espaco.addEventListener('mouseleave', function() {
            this.classList.remove('hover-zoom');
        });
    }
}

function attachScheduleRefresh() {
    const grid = document.querySelector('.grelha-aulas');
    if (!grid) return;

    function refresh() {
        const urlParams = new URLSearchParams(window.location.search);
        const params = new URLSearchParams();
        if (urlParams.get('type')) params.set('type', urlParams.get('type'));
        if (urlParams.get('trainer')) params.set('trainer', urlParams.get('trainer'));
        if (urlParams.get('day')) params.set('day', urlParams.get('day'));
        if (urlParams.get('time')) params.set('time', urlParams.get('time'));

        fetch('api_classes.php?' + params.toString())
            .then(function(response) { return response.json(); })
            .then(function(data) {
                const cards = grid.querySelectorAll('.aula[data-class-id]');
                for (const card of cards) {
                    const id = parseInt(card.getAttribute('data-class-id'), 10);
                    const info = data.classes[id];
                    if (!info) continue;
                    const vagasEl = card.querySelector('.vagas-aula');
                    if (vagasEl) {
                        vagasEl.innerHTML = '<i class="fa-solid fa-users"></i> ' + info.vagas + ' vagas';
                    }
                }
            })
            .catch(function() {});
    }

    setInterval(refresh, 30000);
}

function attachEquipmentToggle() {
    const btn = document.getElementById('btn-ver-equipamentos');
    const detalhe = document.querySelector('.equipamentos-detalhe');
    if (!btn || !detalhe) return;

    const sectionsToHide = document.querySelectorAll(
        '.titulo, .perfil-grid, .painel-editar-perfil, .painel-aulas, .painel-progresso'
    );

    btn.addEventListener('click', function () {
        const expanded = !detalhe.hidden;
        detalhe.hidden = expanded;
        btn.textContent = expanded ? 'Ver mais' : 'Fechar';
        for (const s of sectionsToHide) {
            s.hidden = !expanded;
        }
        if (!expanded) {
            document.getElementById('perfil-equipamentos').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
}

function attachWorkoutToggle() {
    const btn = document.querySelector('.toggle-treinos');
    if (!btn) return;
    const extrasWorkouts = document.querySelector('.treinos-extras');
    const extrasGoals = document.querySelector('.objetivos-extras');
    const ref = extrasWorkouts || extrasGoals;
    if (!ref) return;
    btn.addEventListener('click', function () {
        const expanded = !ref.hidden;
        if (extrasWorkouts) extrasWorkouts.hidden = expanded;
        if (extrasGoals) extrasGoals.hidden = expanded;
        btn.textContent = expanded
            ? btn.dataset.labelMore
            : 'Esconder';
    });
    btn.dataset.labelMore = btn.textContent.trim();
}

attachConfirmForms();
attachFlashMessages();
attachAdminRoleSwitch();
attachEquipmentRefresh();
attachScheduleRefresh();
attachEquipmentToggle();
attachWorkoutToggle();
attachHoverAnimations();
