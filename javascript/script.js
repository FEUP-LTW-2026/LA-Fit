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

    function open() {
        detalhe.hidden = false;
        btn.textContent = 'Fechar';
        for (const s of sectionsToHide) s.hidden = true;
        if (nav) nav.hidden = true;
    }

    function close() {
        detalhe.hidden = true;
        btn.textContent = 'Ver mais';
        for (const s of sectionsToHide) s.hidden = false;
        if (nav) nav.hidden = false;
    }

    const params = new URLSearchParams(window.location.search);
    if (params.has('zona') || params.has('estado')) {
        open();
    }

    btn.addEventListener('click', function () {
        if (detalhe.hidden) {
            open();
        } else {
            close();
            document.getElementById('perfil-equipamentos').scrollIntoView({ block: 'start' });
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

    function expandSection(targetId, scroll) {
        const target = document.getElementById(targetId);
        const btn = document.querySelector('.admin-ver-mais[data-target="' + targetId + '"]');
        if (!target) return;

        for (const el of allSections) {
            el.hidden = el.id !== targetId;
        }
        for (const e of target.querySelectorAll('.admin-extra-rows')) e.hidden = false;
        const filtros = target.querySelector('.admin-filtros');
        if (filtros) filtros.hidden = false;
        if (nav) nav.hidden = true;
        if (btn) btn.textContent = 'Fechar';
        if (scroll) target.scrollIntoView({ block: 'start' });
    }

    function collapseAll() {
        for (const el of allSections) el.hidden = false;
        for (const e of document.querySelectorAll('.admin-extra-rows')) e.hidden = true;
        for (const f of document.querySelectorAll('.admin-filtros')) f.hidden = true;
        if (nav) nav.hidden = false;
        for (const b of btns) b.textContent = 'Ver mais';
    }

    for (const btn of btns) {
        btn.addEventListener('click', function () {
            if (btn.textContent.trim() === 'Fechar') {
                collapseAll();
            } else {
                expandSection(btn.dataset.target, true);
            }
        });
    }

    window._adminExpandSection = expandSection;
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

    if (window._adminExpandSection) {
        window._adminExpandSection(targetId, false);
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


async function ajaxPost(url, formData) {
    const r = await fetch(url, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });
    return r.json();
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

    bindDelete('form[action*="action_workout"] input[name="_action"][value="delete"]~*', function (f) { return f.closest('.treino-item'); });
    bindDelete('form[action*="action_goal"] input[name="_action"][value="delete"]', function (f) { return f.closest('.objetivo'); });
    bindDelete('form[action*="action_nutrition_assignment"] input[name="_action"][value="unassign"]', function (f) { return f.closest('.atribuicao-item'); });
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

function attachAdminTableSearch(inputId, tableSelector) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const table = document.querySelector(tableSelector);
    if (!table) return;

    input.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();
        table.querySelectorAll('tbody tr').forEach(function (tr) {
            const text = tr.textContent.toLowerCase();
            tr.hidden = query !== '' && !text.includes(query);
        });
    });
}

function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function attachAdminUserSearch() {
    const input = document.getElementById('af-users-nome');
    const tableWrap = document.querySelector('#admin-contas-lista .tabela-wrap');
    if (!input || !tableWrap) return;

    let timer;

    function buildRows(users) {
        const csrf = (document.querySelector('input[name="csrf_token"]') || {}).value || '';
        let tbody = tableWrap.querySelector('tbody:not(.admin-extra-rows)');
        if (!tbody) return;

        tableWrap.querySelectorAll('tbody').forEach(function(tb) { tb.innerHTML = ''; });

        users.forEach(function(user) {
            const nome = escHtml((user.nome || '') + ' ' + (user.apelido || ''));
            const detalhe = user.papel === 'membro'
                ? escHtml((user.plano_nome || 'Sem plano') + ' · ' + (user.ginasio_nome || 'Sem ginásio'))
                : escHtml(user.especializacoes || 'Sem especializações');
            const isAtivo = user.estado === 'ativo';
            const toggleLabel = isAtivo ? 'Desativar' : 'Ativar';
            const toggleStatus = isAtivo ? 'inativo' : 'ativo';
            const confirmMsg = isAtivo
                ? 'Tens a certeza que queres desativar esta conta?'
                : 'Tens a certeza que queres ativar esta conta?';

            const tr = document.createElement('tr');
            tr.innerHTML =
                '<td>' + nome + '</td>' +
                '<td>' + escHtml(user.nome_utilizador || '') + '</td>' +
                '<td>' + escHtml(user.email || '') + '</td>' +
                '<td>' + escHtml(user.papel || '') + '</td>' +
                '<td><span class="estado-conta estado-conta-' + escHtml(user.estado) + '">' + escHtml(user.estado) + '</span></td>' +
                '<td>' + detalhe + '</td>' +
                '<td><div class="acoes-linha">' +
                    '<a href="profile.php?edit=' + parseInt(user.id, 10) + '" class="botao claro-voltar">Editar</a>' +
                    '<form action="../actions/action_admin_save_user.php" method="post" data-confirm="' + escHtml(confirmMsg) + '">' +
                        '<input type="hidden" name="csrf_token" value="' + escHtml(csrf) + '">' +
                        '<input type="hidden" name="_action" value="toggle">' +
                        '<input type="hidden" name="user_id" value="' + parseInt(user.id, 10) + '">' +
                        '<input type="hidden" name="status" value="' + escHtml(toggleStatus) + '">' +
                        '<button type="submit" class="botao cliente">' + escHtml(toggleLabel) + '</button>' +
                    '</form>' +
                '</div></td>';
            tbody.appendChild(tr);
        });

        attachConfirmForms();
    }

    function search() {
        const params = new URLSearchParams();
        const nome = input.value.trim();
        if (nome) params.set('nome', nome);
        const papel = (document.getElementById('af-users-papel') || {}).value || '';
        if (papel) params.set('papel', papel);
        const estado = (document.getElementById('af-users-estado') || {}).value || '';
        if (estado) params.set('estado', estado);
        const ordenar = (document.getElementById('af-users-ordenar') || {}).value || '';
        if (ordenar) params.set('ordenar', ordenar);

        fetch('api_users.php?' + params.toString())
            .then(function(r) { return r.json(); })
            .then(function(data) { buildRows(data.users || []); })
            .catch(function() {});
    }

    input.addEventListener('input', function() {
        clearTimeout(timer);
        timer = setTimeout(search, 300);
    });
}

function attachEquipmentNameSearch() {
    const input = document.getElementById('eq-nome');
    const zonas = document.querySelector('.zonas-equipamentos');
    if (!input || !zonas) return;

    let emptyMsg = zonas.previousElementSibling;
    if (!emptyMsg || !emptyMsg.matches('p')) emptyMsg = null;

    let timer;

    function search() {
        const params = new URLSearchParams();
        const nome = input.value.trim();
        if (nome) params.set('nome', nome);
        const zona = (document.getElementById('eq-zona') || {}).value || '';
        if (zona) params.set('zona', zona);
        const estado = (document.getElementById('eq-estado') || {}).value || '';
        if (estado) params.set('estado', estado);

        fetch('api_equipment.php?' + params.toString())
            .then(function(r) { return r.json(); })
            .then(function(data) {
                const eq = data.equipmentByZone || {};
                if (Object.keys(eq).length === 0) {
                    zonas.innerHTML = '';
                    if (emptyMsg) emptyMsg.hidden = false;
                } else {
                    if (emptyMsg) emptyMsg.hidden = true;
                    buildZonas(eq);
                }
            })
            .catch(function() {});
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
                p.textContent = eq.quantidade + ' ' + (parseInt(eq.quantidade, 10) === 1 ? 'unidade' : 'unidades');
                info.appendChild(h3);
                info.appendChild(p);

                const estadoDiv = document.createElement('div');
                estadoDiv.className = 'estado-equipamento estado-' + eq.estado;
                const badge = document.createElement('span');
                const estadoLabels = { disponivel: 'Disponível', ocupado: 'Em uso', manutencao: 'Manutenção' };
                badge.textContent = estadoLabels[eq.estado] || eq.estado;
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

    input.addEventListener('input', function() {
        clearTimeout(timer);
        timer = setTimeout(search, 300);
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
attachAdminUserSearch();
attachEquipmentNameSearch();
attachAdminTableSearch('af-class-nome', '#admin-aulas-lista .tabela');
attachAdminTableSearch('af-eq-nome-admin', '#admin-equipamentos-lista .tabela');
