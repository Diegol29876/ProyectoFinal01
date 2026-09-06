document.addEventListener('DOMContentLoaded', () => {
    const formLogin = document.getElementById('form-login');

    if (!formLogin) return;

    formLogin.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(formLogin);

        try {
            const respuesta = await fetch('../php/login.php', {
                method: 'POST',
                body: formData
            });

            const resultado = await respuesta.json();

            if (resultado.status) {
                alert(`¡Bienvenido/a, ${resultado.usuario}!`);
                window.location.href = 'panel_fun.html';
            } else {
                alert(resultado.mensaje || 'Error al iniciar sesión.');
            }
        } catch (error) {
            console.error('Error en la petición:', error);
            alert('Ocurrió un error al conectar con el servidor.');
        }
    });
});