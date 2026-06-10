document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("formPaciente");
    if (!form) return;

    const pacienteContato = document.querySelector("input[name='pacienteContato']");
    const responsavelContato = document.querySelector("input[name='responsavelContato']");

    /* ===================================================
       FUNÇÕES DE MÁSCARA (Reutilizada)
    =================================================== */
    function aplicarMascaraTelefone(campo) {
        let valor = campo.value.replace(/\D/g, "").substring(0, 11);

        if (valor.length <= 10) {
            valor = valor.replace(/^(\d{2})(\d)/, "($1) $2");
            valor = valor.replace(/(\d{4})(\d)/, "$1-$2");
        } else {
            valor = valor.replace(/^(\d{2})(\d)/, "($1) $2");
            valor = valor.replace(/(\d{5})(\d)/, "$1-$2");
        }

        campo.value = valor;
    }

    /* ===================================================
       EVENTOS DE DIGITAÇÃO
    =================================================== */
    // Contato do Paciente
    if (pacienteContato) {
        pacienteContato.addEventListener("input", function () {
            aplicarMascaraTelefone(this);
        });
        if (pacienteContato.value) aplicarMascaraTelefone(pacienteContato);
    }

    // Contato do Responsável
    if (responsavelContato) {
        responsavelContato.addEventListener("input", function () {
            aplicarMascaraTelefone(this);
        });
        if (responsavelContato.value) aplicarMascaraTelefone(responsavelContato);
    }

    /* ===================================================
       REMOVE MÁSCARAS NO SUBMIT
    =================================================== */
    form.addEventListener("submit", function () {
        if (pacienteContato) pacienteContato.value = pacienteContato.value.replace(/\D/g, "");
        if (responsavelContato) responsavelContato.value = responsavelContato.value.replace(/\D/g, "");
    });

    /* ===================================================
       RETORNA A ETAPA 2 CASO TENHA ERROS
    =================================================== */
    const divEtapa2 = document.getElementById('etapa2');

    if (divEtapa2) {
        const temErroNaEtapa2 = divEtapa2.querySelector('.is-invalid');
        if (temErroNaEtapa2) {
            mudarEtapa(2);
        }
    }

    /* ===================================================
       INICIALIZAÇÃO DA TELA
    =================================================== */
    alternarCamposResponsavel();
});

/* ===================================================
   LÓGICA DAS ETAPAS
=================================================== */
function mudarEtapa(etapa) {
    const divEtapa1 = document.getElementById('etapa1');
    const divEtapa2 = document.getElementById('etapa2');
    const btn1 = document.getElementById('indicadorEtapa1');
    const btn2 = document.getElementById('indicadorEtapa2');
    const barraProgresso = document.getElementById('barraProgresso');

    if (!divEtapa1 || !divEtapa2) return;

    // Validação nativa do HTML antes de avançar para a Etapa 2
    if (etapa === 2) {
        const camposEtapa1 = divEtapa1.querySelectorAll('input, select, textarea');
        let etapa1Valida = true;

        camposEtapa1.forEach(campo => {
            if (!campo.checkValidity()) {
                etapa1Valida = false;
                campo.reportValidity();
            }
        });

        if (!etapa1Valida) return;
    }

    if (etapa === 1) {
        divEtapa1.classList.remove('d-none');
        divEtapa2.classList.add('d-none');
        barraProgresso.style.width = '50%';
        btn1.classList.replace('btn-secondary', 'btn-primary');
        btn2.classList.replace('btn-primary', 'btn-secondary');
    } else if (etapa === 2) {
        divEtapa1.classList.add('d-none');
        divEtapa2.classList.remove('d-none');
        barraProgresso.style.width = '100%';
        btn1.classList.replace('btn-primary', 'btn-secondary');
        btn2.classList.replace('btn-secondary', 'btn-primary');
    }
}

/* ===================================================
   LÓGICA DE MOSTRAR/ESCONDER O RESPONSÁVEL
=================================================== */
function alternarCamposResponsavel() {
    const selectElement = document.getElementById('responsavelVinculo');
    const boxResponsavel = document.getElementById('boxResponsavel');

    if (!selectElement || !boxResponsavel) return;

    const vinculo = selectElement.value;

    if (vinculo === 'PROPRIO') {
        boxResponsavel.style.display = 'none';

        // Remove a obrigatoriedade dos campos do responsável quando ele fica oculto
        document.getElementById('responsavelNome').required = false;
    } else {
        boxResponsavel.style.display = 'flex';

        // Retorna a obrigatoriedade dos campos caso outra pessoa seja a responsável
        document.getElementById('responsavelNome').required = true;
    }
}