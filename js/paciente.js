function controlarResponsavel() {

    let tipo = document.querySelector('input[name="tipoResponsavel"]:checked').value;
    let box = document.getElementById('boxResponsavel');

    let nome = document.querySelector('[name="responsavelNome"]');
    let contato = document.querySelector('[name="responsavelContato"]');
    let vinculo = document.querySelector('[name="responsavelVinculo"]');

    if (tipo === 'PROPRIO') {

        box.style.display = 'none';

        nome.required = false;
        contato.required = false;
        vinculo.required = false;

    } else {

        box.style.display = 'block';

        nome.required = true;
        contato.required = true;
        vinculo.required = true;

    }

}

document.querySelectorAll('input[name="tipoResponsavel"]').forEach(radio => {
    radio.addEventListener('change', controlarResponsavel);
});

window.onload = controlarResponsavel;