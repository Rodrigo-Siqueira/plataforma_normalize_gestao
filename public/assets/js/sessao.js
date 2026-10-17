document.addEventListener("DOMContentLoaded", function () {
    const pacienteInput = document.getElementById("pacienteNome");
    const pacienteHidden = document.getElementById("pacienteId");

    const psicologoInput = document.getElementById("psicologoNome");
    const psicologoHidden = document.getElementById("psicologoId");

    const list = document.getElementById("lista-pacientes");

    function carregarDadosIniciais() {
        // Pega o ID que veio do banco através do Twig
        const idAtual = pacienteHidden.value;

        if (idAtual && list) {
            const options = list.options;

            // Varre a lista procurando quem tem esse ID
            for (let i = 0; i < options.length; i++) {
                if (options[i].getAttribute("data-id") === idAtual) {

                    // Preenche os campos visíveis na tela
                    pacienteInput.value = options[i].value;

                    // Preenche os dados do psicolgo
                    psicologoHidden.value = options[i].getAttribute("data-psi-id");
                    psicologoInput.value = options[i].getAttribute("data-psi-nome");
                    break;
                }
            }
        }
    }

    // Executa a função imediatamente ao carregar a página
    carregarDadosIniciais();

    pacienteInput.addEventListener("input", function () {
        const options = list.options;
        const typedValue = this.value;

        // Limpa os IDs ocultos enquanto o usuário está digitando/apagando
        pacienteHidden.value = "";
        psicologoHidden.value = "";
        psicologoInput.value = "";

        // Procura na lista se o texto digitado bate exatamente com algum nome
        for (let i = 0; i < options.length; i++) {
            if (options[i].value === typedValue) {
                // Encontrou! Preenche o ID oculto do Paciente
                pacienteHidden.value = options[i].getAttribute("data-id");

                // Repete a sua regra de negócio antiga: auto-preenche o responsável!
                psicologoHidden.value = options[i].getAttribute("data-psi-id");
                psicologoInput.value = options[i].getAttribute("data-psi-nome");
                break;
            }
        }
    });
});