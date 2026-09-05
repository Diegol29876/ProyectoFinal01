const botones = document.querySelectorAll('.boton-panel');
const secciones = document.querySelectorAll('.seccion-panel');

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