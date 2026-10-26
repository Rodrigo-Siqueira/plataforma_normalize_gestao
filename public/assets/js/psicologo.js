document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("formPsicologo");
    if (!form) return;

    const crp = document.querySelector("input[name='crp']");
    const contato = document.querySelector("input[name='contato']");
    const comissao = document.querySelector("input[name='comissao']");

    /* ===================================================
       FUNÇÕES DE MÁSCARA
    =================================================== */

    function aplicarMascaraCRP(campo) {
        let valor = campo.value.replace(/\D/g, "").substring(0, 15);

        if (valor.length > 2) {
            valor = valor.substring(0, 2) + "/" + valor.substring(2);
        }

        campo.value = valor;
    }

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

    function aplicarMascaraComissao(campo) {
        campo.value = campo.value
            .replace(/[^0-9.,]/g, "")
            .replace(/(,.*?),/g, "$1")
            .replace(/(\..*)\./g, "$1");
    }

    /* ===================================================
       EVENTOS DIGITAÇÃO
    =================================================== */

    if (crp) {
        crp.addEventListener("input", function () {
            aplicarMascaraCRP(this);
        });

        // APLICA AO CARREGAR (valor vindo do banco)
        if (crp.value) aplicarMascaraCRP(crp);
    }

    if (contato) {
        contato.addEventListener("input", function () {
            aplicarMascaraTelefone(this);
        });

        if (contato.value) aplicarMascaraTelefone(contato);
    }

    if (comissao) {
        comissao.addEventListener("input", function () {
            aplicarMascaraComissao(this);
        });

        if (comissao.value) aplicarMascaraComissao(comissao);
    }

    /* ===================================================
       REMOVE MÁSCARAS NO SUBMIT
    =================================================== */
    form.addEventListener("submit", function () {

        if (crp) crp.value = crp.value.replace(/\D/g, "");
        if (contato) contato.value = contato.value.replace(/\D/g, "");
        if (comissao) comissao.value = comissao.value.replace(",", ".");

    });

    /* ===================================================
       RETORNA A ETAPA 2 CASO TENHA ERROS
    =================================================== */
    const divEtapa2 = document.getElementById('etapa2');

    if (divEtapa2) {
        // Procura se o PHP/Twig gerou algum campo com erro (is-invalid) dentro da Etapa 2
        const temErroNaEtapa2 = divEtapa2.querySelector('.is-invalid');

        // Se encontrou erro, força a abertura da Etapa 2 automaticamente
        if (temErroNaEtapa2) {
            mudarEtapa(2);
        }
    }

});

// Gerencia as etapas do formulário de Psicólogo
function mudarEtapa(etapa) {
    const formPsicologo = document.getElementById('formPsicologo');
    const divEtapa1 = document.getElementById('etapa1');
    const divEtapa2 = document.getElementById('etapa2');
    const barraProgresso = document.getElementById('barraProgresso');
    const indicadorEtapa2 = document.getElementById('indicadorEtapa2');

    // Se o usuário tentar avançar para a etapa 2, valida os campos obrigatórios da etapa 1 primeiro
    if (etapa === 2) {
        const camposEtapa1 = divEtapa1.querySelectorAll('input, select, textarea');
        let etapa1Valida = true;

        camposEtapa1.forEach(campo => {
            if (!campo.checkValidity()) {
                etapa1Valida = false;
                campo.reportValidity(); // Mostra o balão nativo do navegador avisando do campo vazio
            }
        });

        // Interrompe a mudança se houver erro
        if (!etapa1Valida) return;
    }

    if (etapa === 1) {
        divEtapa1.classList.remove('d-none');
        divEtapa2.classList.add('d-none');
        barraProgresso.style.width = '50%';
        indicadorEtapa2.classList.replace('btn-primary', 'btn-secondary');
    } else if (etapa === 2) {
        divEtapa1.classList.add('d-none');
        divEtapa2.classList.remove('d-none');
        barraProgresso.style.width = '100%';
        indicadorEtapa2.classList.replace('btn-secondary', 'btn-primary');
    }
}