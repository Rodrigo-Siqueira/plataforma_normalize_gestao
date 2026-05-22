document.addEventListener("DOMContentLoaded", function () {
    const alerta = document.querySelector('#containerFlash .alert');
    if (alerta) {
        // Tempo em milissegundos: 3000 = 3 segundos
        setTimeout(() => {
            // Cria uma instância do Alerta do Bootstrap para fechar
            const bsAlert = new bootstrap.Alert(alerta);
            bsAlert.close();
        }, 3000);
    }
});