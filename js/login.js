const formulario = document.getElementById('form-login');
const cedula = document.getElementById('cedula');

if (cedula) {
    cedula.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8);
    });
}

if (formulario) {
    formulario.addEventListener('submit', function (evento) {
        evento.preventDefault();

        const datos = new FormData(formulario);
        const numeroCedula = datos.get('cedula');
        const contrasenia = datos.get('password');

        if (!numeroCedula || !contrasenia) {
            alert('Por favor completa todos los campos');
            return;
        }

        fetch('../php/login.php', {
            method: 'POST',
            body: datos
        })
            .then((respuesta) => respuesta.json())
            .then((resultado) => {
                if (!resultado.status) {
                    alert('Error: ' + resultado.mensaje);
                    return;
                }

                alert(resultado.mensaje);
                window.location.href = './panel_fun.html';
            })
            .catch(() => alert('No se pudo conectar con el servidor'));
    });
}