/* ==========================================================
   Sidebar recolhível (Plataforma Normalize)
   Arquivo: assets/js/sidebar.js
   Requer: Bootstrap bundle carregado ANTES deste arquivo (para os tooltips).
   ========================================================== */
(function () {
    'use strict';

    var CHAVE = 'sidebarRecolhida';
    var html = document.documentElement;
    var botao = document.getElementById('btnRecolherSidebar');

    if (!botao) {
        return;
    }

    var icone = botao.querySelector('.material-symbols-outlined');
    var links = document.querySelectorAll('#menuLateral .nav-link[data-rotulo]');
    var desktop = window.matchMedia('(min-width: 768px)');
    var temBootstrap = typeof bootstrap !== 'undefined' && !!bootstrap.Tooltip;
    var tooltips = [];

    function estaRecolhida() {
        return html.classList.contains('sidebar-recolhida');
    }

    /* ---------- Tooltips (Bootstrap) ----------
       Criados uma vez. Só aparecem com a sidebar recolhida E no desktop;
       no menu aberto (ou no celular) o texto já está visível. */
    if (temBootstrap) {
        links.forEach(function (link) {
            tooltips.push(new bootstrap.Tooltip(link, {
                title: link.getAttribute('data-rotulo'),
                placement: 'right',
                container: 'body',   // evita o corte pelo overflow da sidebar
                trigger: 'hover focus'
            }));

            link.addEventListener('show.bs.tooltip', function (evento) {
                if (!(estaRecolhida() && desktop.matches)) {
                    evento.preventDefault();
                }
            });
        });

        // Tooltip do botão de recolher/expandir (texto muda conforme o estado)
        tooltips.push(new bootstrap.Tooltip(botao, {
            title: function () {
                return estaRecolhida() ? 'Expandir menu' : 'Recolher menu';
            },
            placement: 'bottom',
            container: 'body',
            trigger: 'hover'
        }));
    }

    function esconderTooltips() {
        tooltips.forEach(function (tip) { tip.hide(); });
    }

    function aplicar(recolhida, salvar) {
        html.classList.toggle('sidebar-recolhida', recolhida);

        var texto = recolhida ? 'Expandir menu' : 'Recolher menu';
        icone.textContent = recolhida ? 'left_panel_open' : 'left_panel_close';
        botao.setAttribute('aria-label', texto);

        if (temBootstrap) {
            esconderTooltips();
        } else {
            // Sem Bootstrap JS: usa o tooltip nativo do navegador
            botao.setAttribute('title', texto);
            links.forEach(function (link) {
                if (recolhida) {
                    link.setAttribute('title', link.getAttribute('data-rotulo'));
                } else {
                    link.removeAttribute('title');
                }
            });
        }

        if (salvar) {
            try {
                localStorage.setItem(CHAVE, recolhida ? '1' : '0');
            } catch (e) { /* localStorage indisponível: segue sem lembrar */ }
        }
    }

    botao.addEventListener('click', function () {
        aplicar(!estaRecolhida(), true);
    });

    // Sincroniza ícone com o estado já aplicado no <head>
    aplicar(estaRecolhida(), false);
})();