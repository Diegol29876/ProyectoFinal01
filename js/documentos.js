const formulario = document.getElementById('form-documentos');

if (formulario) {
    formulario.addEventListener('submit', async function (evento) {
        evento.preventDefault();

        try {
            const respuesta = await fetch('../php/carga.php', {
                method: 'POST',
                body: new FormData(formulario)
            });
            const mensaje = await respuesta.text();
            alert(mensaje);
        } catch (error) {
            alert('No se pudo cargar el documento.');
        }
    });
}
