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

});

/* ===================================================
       TABELA PSICÓLOGO
    =================================================== */
document.addEventListener("DOMContentLoaded", function () {

    function formatarCRP(valor) {
        valor = valor.replace(/\D/g, "").substring(0, 7);

        if (valor.length > 2) {
            return valor.substring(0, 2) + "/" + valor.substring(2);
        }

        return valor;
    }

    function formatarTelefone(valor) {
        valor = valor.replace(/\D/g, "").substring(0, 11);

        if (valor.length > 10) {
            valor = valor.replace(/^(\d{2})(\d)/, "($1) $2");
            valor = valor.replace(/(\d{5})(\d)/, "$1-$2");
        } else {
            valor = valor.replace(/^(\d{2})(\d)/, "($1) $2");
            valor = valor.replace(/(\d{4})(\d)/, "$1-$2");
        }

        return valor;
    }

    // CRP
    document.querySelectorAll(".crp").forEach(td => {
        td.textContent = formatarCRP(td.textContent);
    });

    // Contato
    document.querySelectorAll(".contato").forEach(td => {
        td.textContent = formatarTelefone(td.textContent);
    });

});