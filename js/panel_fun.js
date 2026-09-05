const botones = document.querySelectorAll('.boton-panel');
const secciones = document.querySelectorAll('.seccion-panel');
const nombreUsuario = document.getElementById('nombre-usuario');

async function mostrarNombreUsuario() {
    if (!nombreUsuario) return;

    try {
        const respuesta = await fetch('../php/usuario_actual.php');
        const usuario = await respuesta.json();
        nombreUsuario.textContent = usuario.nombre || 'Usuario';
    } catch (error) {
        nombreUsuario.textContent = 'Usuario';
    }
}

mostrarNombreUsuario();

botones.forEach(function (boton) {
    boton.addEventListener('click', function (evento) {
        const destino = boton.getAttribute('href');

        if (!destino.startsWith('#')) {
            return;
        }

        evento.preventDefault();

        secciones.forEach(function (seccion) {
            seccion.classList.toggle('active', '#' + seccion.id === destino);
        });

        botones.forEach(function (otroBoton) {
            otroBoton.classList.toggle('active', otroBoton === boton);
        });
    });
});