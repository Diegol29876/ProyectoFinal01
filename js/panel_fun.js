const botones = document.querySelectorAll('.boton-panel');
const secciones = document.querySelectorAll('.seccion-panel');

botones.forEach((boton) => {
    boton.addEventListener('click', (event) => {
        event.preventDefault();

        const destino = boton.getAttribute('href');

        secciones.forEach((seccion) => {
            const mostrar = '#' + seccion.id === destino;
            seccion.classList.toggle('active', mostrar);
        });

        botones.forEach((btn) => {
            btn.classList.toggle('active', btn === boton);
        });
    });
});