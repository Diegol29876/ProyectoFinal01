const formulario = document.getElementById('form-registro');

if (formulario) {
    formulario.addEventListener('submit', async function (evento) {
        evento.preventDefault();

        try {
            const respuesta = await fetch('../php/registro.php', {
                method: 'POST',
                body: new FormData(formulario)
            });
            const resultado = await respuesta.json();

            if (!respuesta.ok || !resultado.status) {
                alert(resultado.mensaje || 'Ocurrió un error al registrar el usuario.');
                return;
            }

            alert('Usuario registrado correctamente.');
        } catch (error) {
            alert('No se pudo conectar con el servidor.');
        }
    });
}
