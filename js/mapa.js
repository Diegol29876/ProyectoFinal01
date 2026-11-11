import { buscarDireccionCompleta, calcularRuta } from './mapa_servicios.js';

const formulario = document.getElementById('formulario-traslado');
const estadoTraslado = document.getElementById('estado-traslado');
const botonGuardar = document.getElementById('guardar-traslado');
const botonGuardarPaciente = document.getElementById('guardar-paciente');
const origen = document.getElementById('origen');
const destino = document.getElementById('destino');
const direccionOrigen = document.getElementById('origen');
const departamentoOrigen = document.getElementById('departamento-origen');
const direccionDestino = document.getElementById('dir');
const departamentoDestino = document.getElementById('departamento');
const horaSalida = document.getElementById('hora');
const horaEstimada = document.getElementById('hora-estimada');
const selects = {
    paciente: document.getElementById('paciente'),
    conductor: document.getElementById('conductor'),
    ambulancia: document.getElementById('ambulancia')
};
const gruposNuevos = {
    paciente: document.getElementById('nuevo-paciente'),
    conductor: document.getElementById('nuevo-conductor'),
    ambulancia: document.getElementById('nueva-ambulancia')
};
const posicionInicial = [-34.89119, -56.15182];
const contenedorMapa = document.getElementById('visualizar');
contenedorMapa.style.height = '500px';

const mapa = L.map(contenedorMapa).setView(posicionInicial, 15);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(mapa);

const iconoOrigen = L.icon({
    iconUrl: '../img/ambulancia.svg',
    iconSize: [38, 45]
});
const iconoDestino = L.icon({
    iconUrl: '../img/ubicacion.png',
    iconSize: [38, 45]
});
let posicionOrigen = null;
let posicionDestino = null;
let marcadorOrigen = null;
let marcadorDestino = null;
let ruta = null;
let duracionRutaSegundos = null;

function fechaActual() {
    const hoy = new Date();
    return [
        hoy.getFullYear(),
        String(hoy.getMonth() + 1).padStart(2, '0'),
        String(hoy.getDate()).padStart(2, '0')
    ].join('-');
}

function agregarOpcion(select, valor, texto) {
    const opcion = document.createElement('option');
    opcion.value = valor;
    opcion.textContent = texto;
    select.appendChild(opcion);
}

function cargarOpciones(select, elementos, etiquetaNueva) {
    select.replaceChildren();
    agregarOpcion(select, '', 'Seleccioná una opción');
    elementos.forEach(function (elemento) {
        agregarOpcion(select, elemento.id, elemento.nombre);
    });
    agregarOpcion(select, 'nuevo', etiquetaNueva);
}

function alternarCamposNuevos() {
    Object.entries(selects).forEach(function ([tipo, select]) {
        const mostrar = select.value === 'nuevo';
        const grupo = gruposNuevos[tipo];
        grupo.hidden = !mostrar;
        grupo.querySelectorAll('input').forEach(function (campo) {
            campo.disabled = !mostrar;
        });
    });
}

async function solicitar(url, opciones) {
    const respuesta = await fetch(url, opciones);
    const datos = await respuesta.json();
    if (!respuesta.ok || !datos.status) {
        throw new Error(datos.mensaje || 'No se pudo completar la operación.');
    }
    return datos;
}

async function cargarCatalogos() {
    estadoTraslado.textContent = 'Cargando pacientes, choferes y ambulancias...';
    try {
        const datos = await solicitar('../php/traslados.php?action=catalogs', {});
        cargarOpciones(selects.paciente, datos.pacientes, '+ Crear paciente');
        cargarOpciones(selects.conductor, datos.conductores, '+ Crear chofer');
        cargarOpciones(selects.ambulancia, datos.ambulancias, '+ Crear ambulancia');
        formulario.elements.csrf_token.value = datos.csrf_token;
        alternarCamposNuevos();
        estadoTraslado.textContent = '';
        return true;
    } catch (error) {
        estadoTraslado.textContent = error.message;
        if (error.message === 'Iniciá sesión para gestionar traslados.') {
            window.location.href = 'login.html';
        }
        return false;
    }
}

function limpiarRuta() {
    if (ruta) {
        mapa.removeLayer(ruta);
        ruta = null;
    }
    if (marcadorDestino) {
        mapa.removeLayer(marcadorDestino);
        marcadorDestino = null;
    }
    posicionDestino = null;
    duracionRutaSegundos = null;
    destino.value = '';
    horaEstimada.value = '';
}

function actualizarHoraEstimada() {
    if (duracionRutaSegundos === null) {
        horaEstimada.value = '';
        return;
    }

    const duracionMinutos = Math.ceil(duracionRutaSegundos / 60);
    if (!horaSalida.value) {
        horaEstimada.value = '';
        estadoTraslado.textContent = 'Ruta trazada. Duración estimada: ' + duracionMinutos +
            ' min. Ingresá la hora de salida para calcular la llegada.';
        return;
    }

    const [horas, minutos] = horaSalida.value.split(':').map(Number);
    const minutosLlegada = (horas * 60 + minutos + duracionMinutos) % (24 * 60);
    horaEstimada.value = String(Math.floor(minutosLlegada / 60)).padStart(2, '0') + ':' +
        String(minutosLlegada % 60).padStart(2, '0');
    estadoTraslado.textContent = 'Ruta trazada. Duración estimada: ' + duracionMinutos +
        ' min. Hora estimada de llegada: ' + horaEstimada.value + '.';
}

function limpiarOrigen() {
    if (marcadorOrigen) {
        mapa.removeLayer(marcadorOrigen);
        marcadorOrigen = null;
    }
    posicionOrigen = null;
    limpiarRuta();
}

function direccionCompleta(calle, departamento) {
    const direccion = calle.trim() + ', ' + departamento + ', Uruguay';
    if (new TextEncoder().encode(direccion).length > 255) {
        throw new Error('La dirección es demasiado larga. Ingresá una dirección más corta.');
    }
    return direccion;
}

async function buscarDireccion(calle, departamento, origen) {
    const consulta = direccionCompleta(calle, departamento);
    const resultado = await buscarDireccionCompleta(consulta, origen);
    if (!resultado) {
        throw new Error('No se encontró la dirección ingresada.');
    }

    return {
        posicion: [Number(resultado.lat), Number(resultado.lon)],
        direccion: consulta
    };
}

document.getElementById('ubicar-origen').addEventListener('click', async function () {
    if (!direccionOrigen.value.trim() || !departamentoOrigen.value) {
        estadoTraslado.textContent = 'Ingresá la dirección de origen y seleccioná el departamento.';
        return;
    }

    estadoTraslado.textContent = 'Buscando la dirección de origen...';
    try {
        const resultado = await buscarDireccion(direccionOrigen.value, departamentoOrigen.value);
        limpiarOrigen();
        posicionOrigen = resultado.posicion;
        marcadorOrigen = L.marker(posicionOrigen, { icon: iconoOrigen }).addTo(mapa);
        direccionOrigen.value = resultado.direccion;
        mapa.setView(posicionOrigen, 15);
        estadoTraslado.textContent = 'Dirección de origen ubicada en el mapa.';
    } catch (error) {
        estadoTraslado.textContent = error.message;
    }
});

direccionOrigen.addEventListener('input', limpiarOrigen);
departamentoOrigen.addEventListener('change', limpiarOrigen);
direccionDestino.addEventListener('input', limpiarRuta);
departamentoDestino.addEventListener('change', limpiarRuta);
horaSalida.addEventListener('input', actualizarHoraEstimada);

document.getElementById('ubicar').addEventListener('click', async function () {
    if (!posicionOrigen || !origen.value) {
        estadoTraslado.textContent = 'Primero ubicá la dirección de origen en el mapa.';
        return;
    }
    if (!direccionDestino.value.trim() || !departamentoDestino.value) {
        estadoTraslado.textContent = 'Ingresá la dirección de destino y seleccioná el departamento.';
        return;
    }

    estadoTraslado.textContent = 'Buscando destino y trazando la ruta...';
    try {
        const resultadoDestino = await buscarDireccion(
            direccionDestino.value,
            departamentoDestino.value,
            posicionOrigen
        );
        const datosRuta = await calcularRuta(posicionOrigen, resultadoDestino.posicion);

        limpiarRuta();
        duracionRutaSegundos = datosRuta.duracionSegundos;
        posicionDestino = resultadoDestino.posicion;
        marcadorDestino = L.marker(posicionDestino, { icon: iconoDestino }).addTo(mapa);
        ruta = L.polyline(datosRuta.puntos, { color: 'blue', weight: 5 }).addTo(mapa);
        destino.value = resultadoDestino.direccion;
        actualizarHoraEstimada();
        mapa.fitBounds(ruta.getBounds());
    } catch (error) {
        estadoTraslado.textContent = error.message;
    }
});

Object.values(selects).forEach(function (select) {
    select.addEventListener('change', alternarCamposNuevos);
});

async function guardarRegistroNuevo(tipo, accion) {
    const grupo = gruposNuevos[tipo];
    const campos = grupo.querySelectorAll('input');
    for (const campo of campos) {
        if (!campo.reportValidity()) return;
    }

    const boton = grupo.querySelector('button[type="button"]');
    const select = selects[tipo];
    const etiquetas = {
        paciente: 'paciente',
        conductor: 'chofer',
        ambulancia: 'ambulancia'
    };
    estadoTraslado.textContent = 'Guardando ' + etiquetas[tipo] + '...';
    boton.disabled = true;
    const datos = Object.fromEntries(Array.from(campos, function (campo) {
        return [campo.name, campo.value];
    }));
    datos.action = accion;
    datos.csrf_token = formulario.elements.csrf_token.value;

    try {
        const resultado = await solicitar('../php/traslados.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        });
        if (!resultado.registro || resultado.registro.id == null || !resultado.registro.nombre) {
            throw new Error('El servidor no devolvió los datos del registro creado.');
        }
        const opcion = document.createElement('option');
        opcion.value = resultado.registro.id;
        opcion.textContent = resultado.registro.nombre;
        select.insertBefore(opcion, select.querySelector('option[value="nuevo"]'));
        select.value = String(resultado.registro.id);
        campos.forEach(function (campo) {
            campo.value = '';
        });
        alternarCamposNuevos();
        estadoTraslado.textContent = resultado.mensaje;
    } catch (error) {
        estadoTraslado.textContent = error.message;
        if (error.message === 'Iniciá sesión para gestionar traslados.') {
            window.location.href = 'login.html';
        }
    } finally {
        boton.disabled = false;
    }
}

botonGuardarPaciente.addEventListener('click', function () {
    guardarRegistroNuevo('paciente', 'create-patient');
});

document.getElementById('guardar-conductor').addEventListener('click', function () {
    guardarRegistroNuevo('conductor', 'create-driver');
});

document.getElementById('guardar-ambulancia').addEventListener('click', function () {
    guardarRegistroNuevo('ambulancia', 'create-ambulance');
});

formulario.addEventListener('submit', async function (evento) {
    evento.preventDefault();
    if (!posicionOrigen || !posicionDestino || !origen.value || !destino.value) {
        estadoTraslado.textContent = 'Ubicá las direcciones de origen y destino en el mapa antes de registrar.';
        return;
    }

    estadoTraslado.textContent = 'Registrando traslado...';
    botonGuardar.disabled = true;
    const datos = Object.fromEntries(new FormData(formulario).entries());
    datos.action = 'create';

    try {
        const resultado = await solicitar('../php/traslados.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        });

        formulario.reset();
        formulario.elements.fecha.value = fechaActual();
        limpiarOrigen();
        mapa.setView(posicionInicial, 15);
        alternarCamposNuevos();
        const catalogosRecargados = await cargarCatalogos();
        estadoTraslado.textContent = catalogosRecargados
            ? resultado.mensaje
            : resultado.mensaje + ' Recargá la página para actualizar los catálogos.';
    } catch (error) {
        estadoTraslado.textContent = error.message;
        if (error.message === 'Iniciá sesión para gestionar traslados.') {
            window.location.href = 'login.html';
        }
    } finally {
        botonGuardar.disabled = false;
    }
});

formulario.elements.fecha.value = fechaActual();
cargarCatalogos();
