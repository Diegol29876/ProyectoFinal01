document.addEventListener('DOMContentLoaded', () => {
    const formRegistro = document.getElementById('form-registro');

    if (!formRegistro) return;

    formRegistro.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(formRegistro);

        try {
            const respuesta = await fetch('../php/registro.php', {
                method: 'POST',
                body: formData
            });

            const resultado = await respuesta.json();

            if (resultado.status) {
                alert('Funcionario registrado con éxito.');
                formRegistro.reset();
                window.location.href = 'panel_fun.html';
            } else {
                alert(resultado.mensaje || 'No se pudo completar el registro.');
            }
        } catch (error) {
            console.error('Error al registrar:', error);
            alert('Error de conexión con el servidor.');
        }
    });
});