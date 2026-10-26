document.addEventListener("DOMContentLoaded", function () {
    const pacienteInput = document.getElementById("pacienteNome");
    const pacienteHidden = document.getElementById("pacienteId");

    const responsavelInput = document.getElementById("responsavelNome");
    const responsavelHidden = document.getElementById("responsavelPessoaId");

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

                    // Preenche os dados do responsável
                    responsavelHidden.value = options[i].getAttribute("data-resp-id");
                    responsavelInput.value = options[i].getAttribute("data-resp-nome");
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
        responsavelHidden.value = "";
        responsavelInput.value = "";

        // Procura na lista se o texto digitado bate exatamente com algum nome
        for (let i = 0; i < options.length; i++) {
            if (options[i].value === typedValue) {
                // Encontrou! Preenche o ID oculto do Paciente
                pacienteHidden.value = options[i].getAttribute("data-id");

                // Repete a sua regra de negócio antiga: auto-preenche o responsável!
                responsavelHidden.value = options[i].getAttribute("data-resp-id");
                responsavelInput.value = options[i].getAttribute("data-resp-nome");
                break;
            }
        }
    });
});

function atualizarPreco() {
    const selectProduto = document.getElementById("produtoId");
    const inputPreco = document.getElementById("valorCobrado");

    // Pega a opção que foi selecionada
    const opcaoSelecionada = selectProduto.options[selectProduto.selectedIndex];

    // Extrai o preço e atualiza o input
    const preco = opcaoSelecionada.getAttribute("data-preco");
    if (preco) {
        inputPreco.value = preco;
    }
}