const formulario = document.getElementById('formulario-muestra');
const estadoFormulario = document.getElementById('estado-formulario');
const estadoListado = document.getElementById('estado-listado');
const filas = document.getElementById('filas-muestras');
const csrfToken = document.getElementById('csrf-token');

async function solicitar(url, opciones) {
    const respuesta = await fetch(url, opciones);
    const datos = await respuesta.json();
    if (!respuesta.ok || !datos.status) {
        throw new Error(datos.mensaje || 'No se pudo completar la operación.');
    }
    return datos;
}

function mostrarMuestras(muestras) {
    filas.replaceChildren();

    if (muestras.length === 0) {
        const fila = filas.insertRow();
        const celda = fila.insertCell();
        celda.colSpan = 5;
        celda.textContent = 'Todavía no hay muestras registradas.';
        return;
    }

    muestras.forEach(function (muestra) {
        const fila = filas.insertRow();
        [muestra.nombre, muestra.apellido, muestra.cedula, muestra.muestra, muestra.fecha]
            .forEach(function (valor) {
                fila.insertCell().textContent = valor;
            });
    });
}

async function cargarMuestras() {
    estadoListado.textContent = 'Cargando muestras...';
    try {
        const datos = await solicitar('../php/muestras_paciente.php?action=list', {});
        csrfToken.value = datos.csrf_token;
        mostrarMuestras(datos.muestras);
        estadoListado.textContent = datos.muestras.length +
            (datos.muestras.length === 1 ? ' muestra registrada.' : ' muestras registradas.');
    } catch (error) {
        estadoListado.textContent = error.message;
        filas.replaceChildren();
        const fila = filas.insertRow();
        const celda = fila.insertCell();
        celda.colSpan = 5;
        celda.textContent = 'No se pudieron cargar las muestras.';
        if (error.message === 'Iniciá sesión para gestionar muestras.') {
            window.location.href = 'login.html';
        }
    }
}

formulario.addEventListener('submit', async function (evento) {
    evento.preventDefault();
    estadoFormulario.textContent = 'Guardando muestra...';
    const datos = Object.fromEntries(new FormData(formulario).entries());

    try {
        const resultado = await solicitar('../php/muestras_paciente.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        });
        formulario.reset();
        csrfToken.value = resultado.csrf_token;
        estadoFormulario.textContent = resultado.mensaje;
        await cargarMuestras();
    } catch (error) {
        estadoFormulario.textContent = error.message;
    }
});

cargarMuestras();
