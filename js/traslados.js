const filas = document.getElementById('filas-traslados');
const estadoPagina = document.getElementById('estado-pagina');
const dialogo = document.getElementById('dialogo-traslado');
const formulario = document.getElementById('formulario-traslado');
const estadoFormulario = document.getElementById('estado-formulario');
const selects = {
    paciente: document.getElementById('paciente'),
    conductor: document.getElementById('conductor'),
    ambulancia: document.getElementById('ambulancia')
};
let traslados = [];

function esTrasladoActivo(traslado) {
    return ['pendiente', 'en camino'].includes(String(traslado.estado).trim().toLowerCase());
}

function agregarOpcion(select, valor, texto) {
    const opcion = document.createElement('option');
    opcion.value = valor;
    opcion.textContent = texto;
    select.appendChild(opcion);
}

function cargarOpciones(select, items, etiquetaNueva) {
    select.replaceChildren();
    agregarOpcion(select, '', 'Seleccioná una opción');
    items.forEach(function (item) {
        agregarOpcion(select, item.id, item.nombre);
    });
    agregarOpcion(select, 'nuevo', etiquetaNueva);
}

function alternarCamposNuevos() {
    document.getElementById('nuevo-paciente').classList.toggle('oculto', selects.paciente.value !== 'nuevo');
    document.getElementById('nuevo-conductor').classList.toggle('oculto', selects.conductor.value !== 'nuevo');
    document.getElementById('nueva-ambulancia').classList.toggle('oculto', selects.ambulancia.value !== 'nuevo');
}

async function solicitar(url, opciones) {
    const respuesta = await fetch(url, opciones);
    const datos = await respuesta.json();
    if (!respuesta.ok || !datos.status) {
        throw new Error(datos.mensaje || 'No se pudo completar la operación.');
    }
    return datos;
}

async function cargarDatos() {
    estadoPagina.textContent = 'Cargando traslados...';
    try {
        const datos = await solicitar('../php/traslados.php?action=list', {});
        traslados = datos.traslados;
        formulario.elements.csrf_token.value = datos.csrf_token;
        renderizarTraslados();
        const cantidadActivos = traslados.filter(esTrasladoActivo).length;
        estadoPagina.textContent = cantidadActivos + (cantidadActivos === 1 ? ' traslado activo.' : ' traslados activos.');
    } catch (error) {
        estadoPagina.textContent = error.message;
        if (error.message === 'Iniciá sesión para gestionar traslados.') {
            window.location.href = 'login.html';
            return;
        }
        filas.replaceChildren();
        const fila = filas.insertRow();
        const celda = fila.insertCell();
        celda.colSpan = 9;
        celda.textContent = 'No se pudo cargar la lista de traslados.';
    }
}

function renderizarTraslados() {
    filas.replaceChildren();
    const trasladosActivos = traslados.filter(esTrasladoActivo);
    if (trasladosActivos.length === 0) {
        const fila = filas.insertRow();
        const celda = fila.insertCell();
        celda.colSpan = 9;
        celda.textContent = 'No hay traslados activos en este momento.';
        return;
    }

    trasladosActivos.forEach(function (traslado) {
        const fila = filas.insertRow();
        [
            traslado.paciente,
            traslado.origen,
            traslado.destino,
            traslado.fecha,
            traslado.hora,
            traslado.conductor,
            traslado.acompanante,
            traslado.estado
        ].forEach(function (valor) {
            fila.insertCell().textContent = valor || '—';
        });

        const acciones = fila.insertCell();
        acciones.className = 'acciones';
        [
            ['Ver', 'ver'],
            ['Modificar', 'editar'],
            ['Mapa', 'mapa'],
            ['Eliminar', 'eliminar']
        ].forEach(function (definicion) {
            const boton = document.createElement('button');
            boton.type = 'button';
            boton.className = 'accion' + (definicion[1] === 'eliminar' ? ' eliminar' : '');
            boton.textContent = definicion[0];
            boton.dataset.accion = definicion[1];
            boton.dataset.id = traslado.id;
            acciones.appendChild(boton);
        });
    });
}

async function abrirFormulario(traslado) {
    estadoFormulario.textContent = '';
    formulario.reset();
    document.getElementById('titulo-dialogo').textContent = traslado ? 'Modificar traslado' : 'Nuevo traslado';
    document.querySelector('[name="id"]').value = traslado ? traslado.id : '';

    try {
        const datos = await solicitar('../php/traslados.php?action=catalogs', {});
        cargarOpciones(selects.paciente, datos.pacientes, '+ Crear paciente');
        cargarOpciones(selects.conductor, datos.conductores, '+ Crear chofer');
        cargarOpciones(selects.ambulancia, datos.ambulancias, '+ Crear ambulancia');
        alternarCamposNuevos();

        if (traslado) {
            Object.entries({
                paciente_id: traslado.paciente_id,
                origen: traslado.origen,
                destino: traslado.destino,
                fecha: traslado.fecha,
                hora: traslado.hora,
                hora_estimada: traslado.hora_estimada,
                hora_efectiva: traslado.hora_efectiva,
                conductor_id: traslado.conductor_id,
                ambulancia_id: traslado.ambulancia_id,
                acompanante: traslado.acompanante,
                estado: traslado.estado,
                observaciones: traslado.observaciones
            }).forEach(function (par) {
                formulario.elements[par[0]].value = par[1] || '';
            });
        } else {
            const hoy = new Date();
            formulario.elements.fecha.value = [
                hoy.getFullYear(),
                String(hoy.getMonth() + 1).padStart(2, '0'),
                String(hoy.getDate()).padStart(2, '0')
            ].join('-');
        }
        alternarCamposNuevos();
        dialogo.showModal();
    } catch (error) {
        estadoPagina.textContent = error.message;
    }
}

async function guardarTraslado(evento) {
    evento.preventDefault();
    estadoFormulario.textContent = 'Guardando...';
    const datos = Object.fromEntries(new FormData(formulario).entries());
    datos.action = datos.id ? 'update' : 'create';

    try {
        const resultado = await solicitar('../php/traslados.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        });
        dialogo.close();
        await cargarDatos();
        estadoPagina.textContent = resultado.mensaje;
    } catch (error) {
        estadoFormulario.textContent = error.message;
    }
}

filas.addEventListener('click', async function (evento) {
    const boton = evento.target.closest('[data-accion]');
    if (!boton) return;
    const traslado = traslados.find(function (item) {
        return String(item.id) === boton.dataset.id;
    });
    if (!traslado) return;

    if (boton.dataset.accion === 'ver') {
        const resumen = [
            'Paciente: ' + traslado.paciente,
            'Origen: ' + traslado.origen,
            'Destino: ' + traslado.destino,
            'Fecha y hora: ' + traslado.fecha + ' ' + traslado.hora,
            'Chofer: ' + traslado.conductor,
            'Ambulancia: ' + traslado.ambulancia,
            'Acompañante: ' + traslado.acompanante,
            'Estado: ' + traslado.estado,
            'Observaciones: ' + (traslado.observaciones || 'Sin observaciones')
        ].join('\n');
        alert(resumen);
    } else if (boton.dataset.accion === 'mapa') {
        alert('El mapa en vivo estará disponible próximamente.');
    } else if (boton.dataset.accion === 'editar') {
        await abrirFormulario(traslado);
    } else if (boton.dataset.accion === 'eliminar' &&
        confirm('¿Eliminar el traslado de ' + traslado.paciente + '? Esta acción no se puede deshacer.')) {
        try {
            const token = formulario.elements.csrf_token.value;
            const resultado = await solicitar('../php/traslados.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete', id: traslado.id, csrf_token: token })
            });
            await cargarDatos();
            estadoPagina.textContent = resultado.mensaje;
        } catch (error) {
            estadoPagina.textContent = error.message;
        }
    }
});

document.getElementById('cerrar-dialogo').addEventListener('click', function () {
    dialogo.close();
});
document.getElementById('cancelar-dialogo').addEventListener('click', function () {
    dialogo.close();
});
Object.values(selects).forEach(function (select) {
    select.addEventListener('change', alternarCamposNuevos);
});
formulario.addEventListener('submit', guardarTraslado);
cargarDatos();
