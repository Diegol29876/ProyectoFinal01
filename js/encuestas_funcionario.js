const listaRespuestas = document.getElementById('lista-respuestas');
const estadoRespuestas = document.getElementById('estado-respuestas');
const botonActualizar = document.getElementById('actualizar-respuestas');

function agruparRespuestas(respuestas) {
    const grupos = {};

    respuestas.forEach(function (respuesta) {
        const clave = respuesta.ID_Envio;

        if (!grupos[clave]) {
            grupos[clave] = {
                id: respuesta.ID_Encuesta,
                fecha: respuesta.Fecha,
                respuestas: []
            };
        }

        grupos[clave].respuestas.push(respuesta);
    });

    return Object.values(grupos);
}

function mostrarRespuestas(respuestas) {
    listaRespuestas.innerHTML = '';

    if (respuestas.length === 0) {
        estadoRespuestas.textContent = 'Todavía no hay respuestas guardadas.';
        return;
    }

    const encuestas = agruparRespuestas(respuestas);
    estadoRespuestas.textContent = encuestas.length + ' encuesta' + (encuestas.length === 1 ? '' : 's') + ' recibida' + (encuestas.length === 1 ? '' : 's') + '.';

    encuestas.forEach(function (grupo, indice) {
        const tarjeta = document.createElement('article');
        tarjeta.className = 'respuesta-card';
        tarjeta.innerHTML = `
            <button class="encabezado-respuesta" type="button">
                <span>Encuesta ${indice + 1}</span>
                <strong>${grupo.fecha}</strong>
                <span class="flecha">+</span>
            </button>
            <div class="detalle-respuesta"></div>`;

        const detalle = tarjeta.querySelector('.detalle-respuesta');
        grupo.respuestas.forEach(function (respuesta) {
            const fila = document.createElement('div');
            fila.className = 'fila-respuesta';
            fila.innerHTML = '<strong></strong><span></span>';
            fila.querySelector('strong').textContent = respuesta.Clasificacion;
            fila.querySelector('span').textContent = respuesta.Respuesta_Texto;
            detalle.appendChild(fila);
        });

        if (indice === 0) {
            tarjeta.classList.add('abierta');
        }

        tarjeta.querySelector('.encabezado-respuesta').addEventListener('click', function () {
            tarjeta.classList.toggle('abierta');
        });
        listaRespuestas.appendChild(tarjeta);
    });
}

async function cargarRespuestas() {
    estadoRespuestas.textContent = 'Cargando respuestas...';

    try {
        const respuesta = await fetch('../php/respuestas_encuesta.php');
        const datos = await respuesta.json();

        if (!respuesta.ok) throw new Error(datos.error);
        mostrarRespuestas(datos);
    } catch (error) {
        estadoRespuestas.textContent = error.message || 'No se pudieron cargar las respuestas.';
    }
}

botonActualizar.addEventListener('click', cargarRespuestas);
cargarRespuestas();