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
        '.titulo, .perfil-grid, .painel-editar-perfil, .painel-aulas, .painel-progresso, .painel-nutricao'
    );
    const nav = document.querySelector('.menu');

    btn.addEventListener('click', function () {
        const expanded = !detalhe.hidden;
        detalhe.hidden = expanded;
        btn.textContent = expanded ? 'Ver mais' : 'Fechar';
        for (const s of sectionsToHide) {
            s.hidden = !expanded;
        }
        if (nav) nav.hidden = !expanded;
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

function attachAdminSectionExpand() {
    const btns = document.querySelectorAll('.admin-ver-mais');
    if (btns.length === 0) return;

    const nav = document.querySelector('.menu');
    const allSections = [
        '.titulo', '#admin-geral', '#admin-contas', '#admin-contas-lista',
        '#admin-aulas', '#admin-aulas-lista', '#admin-equipamentos', '#admin-equipamentos-lista'
    ].map(sel => document.querySelector(sel)).filter(Boolean);

    for (const btn of btns) {
        btn.addEventListener('click', function () {
            const targetId = btn.dataset.target;
            const target = document.getElementById(targetId);
            const isExpanded = btn.textContent.trim() === 'Fechar';

            if (isExpanded) {
                for (const el of allSections) el.hidden = false;
                for (const e of document.querySelectorAll('.admin-extra-rows')) e.hidden = true;
                for (const f of document.querySelectorAll('.admin-filtros')) f.hidden = true;
                if (nav) nav.hidden = false;
                btn.textContent = 'Ver mais';
            } else {
                for (const el of allSections) {
                    el.hidden = el.id !== targetId;
                }
                for (const e of target.querySelectorAll('.admin-extra-rows')) e.hidden = false;
                const filtros = target.querySelector('.admin-filtros');
                if (filtros) filtros.hidden = false;
                if (nav) nav.hidden = true;
                btn.textContent = 'Fechar';
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }
}

function attachAdminAutoExpand() {
    const params = new URLSearchParams(window.location.search);
    const expand = params.get('expand');
    if (!expand) return;

    const map = {
        contas: 'admin-contas-lista',
        aulas: 'admin-aulas-lista',
        equipamentos: 'admin-equipamentos-lista',
    };
    const targetId = map[expand];
    if (!targetId) return;

    const btn = document.querySelector('.admin-ver-mais[data-target="' + targetId + '"]');
    if (btn) {
        btn.click();
    } else {
        const filtros = document.querySelector('#' + targetId + ' .admin-filtros');
        if (filtros) filtros.hidden = false;
    }
}

function attachAdminEditNavHide() {
    const params = new URLSearchParams(window.location.search);
    if (params.has('edit') || params.has('edit_class') || params.has('edit_equipment')) {
        const nav = document.querySelector('.menu');
        if (nav) nav.hidden = true;
    }
}

function attachPasswordConfirmation() {
    const forms = document.querySelectorAll('form');
    for (const form of forms) {
        const password = form.querySelector('input[name="password"]');
        const confirm  = form.querySelector('input[name="password_confirmation"]');
        if (!password || !confirm) continue;
        function check() {
            confirm.setCustomValidity(
                confirm.value && password.value !== confirm.value
                    ? 'As palavras-passe não coincidem.'
                    : ''
            );
        }
        password.addEventListener('input', check);
        confirm.addEventListener('input', check);
    }
}

function attachPhotoPreview() {
    const inputs = document.querySelectorAll('input[type="file"][name="photo"]');
    for (const input of inputs) {
        input.addEventListener('change', function () {
            const file = this.files[0];
            if (!file || !file.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = function (e) {
                let preview = input.parentElement.querySelector('.foto-preview-temp');
                if (!preview) {
                    preview = document.createElement('img');
                    preview.className = 'foto-preview-temp';
                    input.parentElement.insertBefore(preview, input);
                }
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }
}

function attachCharacterCounters() {
    const textareas = document.querySelectorAll('textarea[maxlength]');
    for (const ta of textareas) {
        const max = parseInt(ta.getAttribute('maxlength'), 10);
        const counter = document.createElement('small');
        counter.className = 'char-counter';
        counter.textContent = '0 / ' + max;
        ta.parentElement.appendChild(counter);
        ta.addEventListener('input', function () {
            counter.textContent = ta.value.length + ' / ' + max;
            counter.style.color = ta.value.length > max * 0.9 ? '#c0392b' : '';
        });
    }
}


function ajaxPost(url, formData) {
    return fetch(url, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
    }).then(function (r) { return r.json(); });
}

function attachAjaxDeletes() {
    function bindDelete(selector, getTarget) {
        document.querySelectorAll(selector).forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (e.defaultPrevented) return;
                e.preventDefault();
                const target = getTarget(form);
                if (!target) return;
                ajaxPost(form.action, new FormData(form))
                    .then(function (data) { if (data.success) target.remove(); })
                    .catch(function () { form.submit(); });
            });
        });
    }

    bindDelete('form[action*="action_delete_workout"]', function (f) { return f.closest('.treino-item'); });
    bindDelete('form[action*="action_delete_goal"]',    function (f) { return f.closest('.objetivo'); });
    bindDelete('form[action*="action_unassign_nutrition_plan"]', function (f) { return f.closest('.atribuicao-item'); });
}

function attachAjaxGoalUpdate() {
    document.querySelectorAll('form.objetivo-atualizar').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (e.defaultPrevented) return;
            e.preventDefault();
            const article = form.closest('.objetivo');
            if (!article) return;

            ajaxPost(form.action, new FormData(form))
                .then(function (data) {
                    if (!data.success) return;

                    const fill = article.querySelector('.objetivo-fill');
                    if (fill) fill.style.width = data.pct + '%';

                    const metaSpans = article.querySelectorAll('.objetivo-meta span');
                    if (metaSpans[0]) {
                        metaSpans[0].textContent =
                            data.valor_atual.toFixed(1).replace('.', ',') + ' / ' +
                            data.valor_alvo.toFixed(1).replace('.', ',') + ' ' + data.unidade;
                    }
                    if (metaSpans[1]) metaSpans[1].textContent = data.pct + '%';

                    const input = form.querySelector('input[name="valor_atual"]');
                    if (input) input.value = data.valor_atual.toFixed(1);

                    if (data.concluido) {
                        article.classList.add('objetivo-concluido');
                        if (fill) fill.style.background = '#2ecc71';
                        const cab = article.querySelector('.objetivo-cabecalho');
                        if (cab && !cab.querySelector('.faixa-verde')) {
                            const badge = document.createElement('span');
                            badge.className = 'faixa faixa-verde';
                            badge.textContent = 'Concluído';
                            cab.appendChild(badge);
                        }
                        form.hidden = true;
                    }
                })
                .catch(function () { form.submit(); });
        });
    });
}

attachConfirmForms();
attachFlashMessages();
attachAdminRoleSwitch();
attachEquipmentRefresh();
attachScheduleRefresh();
attachEquipmentToggle();
attachWorkoutToggle();
attachHoverAnimations();
attachAdminEditNavHide();
attachAdminSectionExpand();
attachAdminAutoExpand();
attachPasswordConfirmation();
attachPhotoPreview();
attachCharacterCounters();
attachAjaxDeletes();
attachAjaxGoalUpdate();
