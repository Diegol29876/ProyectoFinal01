const selectorTraslado = document.getElementById('traslado');
const elementoMapa = document.getElementById('mapa');
const elementoEstado = document.getElementById('estado');
const elementoTiempo = document.getElementById('hora');
const elementoDistancia = document.getElementById('distancia');
const elementoAmbulancia = document.getElementById('ambulancia');
const elementoOrigen = document.getElementById('origen-traslado');
const elementoDestino = document.getElementById('destino-traslado');

if (!selectorTraslado || !elementoMapa || !elementoEstado || !elementoTiempo ||
    !elementoDistancia || !elementoAmbulancia || !elementoOrigen || !elementoDestino) {
    throw new Error('Faltan elementos de seguimiento en la página.');
}

let mapa = null;
let capasRuta = null;
let traslados = [];
let solicitudActual = 0;
let intervaloSimulacion = null;
const apiGeoapify = '4572a7318c164b3b873097a1f7b2c618';

function esTrasladoVigente(traslado) {
    return ['pendiente', 'en camino'].includes(
        String(traslado.estado || '').trim().toLowerCase()
    );
}

function prepararMapa() {
    if (mapa) return;
    if (typeof L === 'undefined') {
        throw new Error('No se pudo cargar el mapa. Revisá tu conexión a internet y recargá la página.');
    }

    mapa = L.map(elementoMapa).setView([-34.89119, -56.15182], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(mapa);
    capasRuta = L.layerGroup().addTo(mapa);
}

function mostrarDuracion(segundos) {
    const minutosTotales = Math.ceil(segundos / 60);
    const horas = Math.floor(minutosTotales / 60);
    const minutos = minutosTotales % 60;

    return horas === 0 ? minutos + ' min' : horas + ' h ' + minutos + ' min';
}

function distanciaEntre(puntoA, puntoB) {
    const radioTierra = 6371000;
    const latitudA = puntoA[0] * Math.PI / 180;
    const latitudB = puntoB[0] * Math.PI / 180;
    const diferenciaLatitud = (puntoB[0] - puntoA[0]) * Math.PI / 180;
    const diferenciaLongitud = (puntoB[1] - puntoA[1]) * Math.PI / 180;
    const calculo = Math.sin(diferenciaLatitud / 2) ** 2 +
        Math.cos(latitudA) * Math.cos(latitudB) *
        Math.sin(diferenciaLongitud / 2) ** 2;
    return 2 * radioTierra * Math.atan2(Math.sqrt(calculo), Math.sqrt(1 - calculo));
}

function crearTramos(puntos) {
    let distanciaTotal = 0;
    const tramos = [];

    for (let indice = 1; indice < puntos.length; indice += 1) {
        const distancia = distanciaEntre(puntos[indice - 1], puntos[indice]);
        if (distancia === 0) continue;

        tramos.push({
            inicio: puntos[indice - 1],
            fin: puntos[indice],
            desde: distanciaTotal,
            distancia: distancia
        });
        distanciaTotal += distancia;
    }

    return { tramos, distanciaTotal };
}

function posicionEnRuta(tramos, distanciaRecorrida) {
    const tramo = tramos.find(function (item) {
        return distanciaRecorrida <= item.desde + item.distancia;
    }) || tramos[tramos.length - 1];
    const avance = Math.min(1, Math.max(0,
        (distanciaRecorrida - tramo.desde) / tramo.distancia));

    return [
        tramo.inicio[0] + (tramo.fin[0] - tramo.inicio[0]) * avance,
        tramo.inicio[1] + (tramo.fin[1] - tramo.inicio[1]) * avance
    ];
}

async function buscarDireccionCompleta(direccion, origen) {
    const texto = /uruguay/i.test(direccion) ? direccion.trim() : direccion.trim() + ', Uruguay';
    const parametros = new URLSearchParams({
        text: texto,
        filter: 'countrycode:uy',
        lang: 'es',
        limit: '1',
        format: 'json',
        apiKey: apiGeoapify
    });

    if (origen) {
        parametros.set('bias', 'proximity:' + origen[1] + ',' + origen[0]);
    }

    const respuesta = await fetch('https://api.geoapify.com/v1/geocode/search?' + parametros);
    if (!respuesta.ok) throw new Error('Falló la búsqueda de una dirección.');
    const datos = await respuesta.json();
    return datos.results && datos.results[0] ? datos.results[0] : null;
}

async function calcularRuta(origen, destino) {
    const waypoints = origen[0] + ',' + origen[1] + '|' + destino[0] + ',' + destino[1];
    const url = 'https://api.geoapify.com/v1/routing?waypoints=' +
        encodeURIComponent(waypoints) +
        '&mode=drive&units=metric&lang=es&apiKey=' + apiGeoapify;
    const respuesta = await fetch(url);
    if (!respuesta.ok) throw new Error('Falló el cálculo de la ruta.');
    return respuesta.json();
}

function mostrarDatosTraslado(traslado) {
    elementoAmbulancia.textContent = traslado.ambulancia || 'Sin ambulancia asignada';
    elementoOrigen.textContent = traslado.origen || 'Sin origen registrado';
    elementoDestino.textContent = traslado.destino || 'Sin destino registrado';
    elementoEstado.textContent = traslado.estado || 'Sin estado';
    elementoTiempo.textContent = 'Calculando...';
    elementoDistancia.textContent = 'Calculando...';
    if (capasRuta) capasRuta.clearLayers();
}

async function mostrarRutaSeleccionada() {
    if (intervaloSimulacion) {
        clearInterval(intervaloSimulacion);
        intervaloSimulacion = null;
    }

    const idSeleccionado = selectorTraslado.value;
    const traslado = traslados.find(function (item) {
        return String(item.id) === idSeleccionado;
    });
    const idSolicitud = ++solicitudActual;

    if (capasRuta) capasRuta.clearLayers();
    if (!traslado) {
        elementoEstado.textContent = 'No hay traslados registrados.';
        elementoTiempo.textContent = '—';
        elementoDistancia.textContent = '—';
        elementoAmbulancia.textContent = '—';
        elementoOrigen.textContent = '—';
        elementoDestino.textContent = '—';
        return;
    }

    mostrarDatosTraslado(traslado);
    if (!traslado.origen || !traslado.destino) {
        elementoEstado.textContent = 'El traslado no tiene origen o destino registrados.';
        return;
    }

    elementoEstado.textContent = 'Buscando las direcciones registradas...';
    try {
        prepararMapa();
        const ubicacionOrigen = await buscarDireccionCompleta(traslado.origen);
        if (idSolicitud !== solicitudActual) return;
        if (!ubicacionOrigen) {
            throw new Error('No se pudo encontrar el origen registrado.');
        }

        const coordenadasOrigen = [Number(ubicacionOrigen.lat), Number(ubicacionOrigen.lon)];
        const ubicacionDestino = await buscarDireccionCompleta(traslado.destino, coordenadasOrigen);
        if (idSolicitud !== solicitudActual) return;
        if (!ubicacionDestino) {
            throw new Error('No se pudo encontrar el destino registrado.');
        }

        const coordenadasDestino = [Number(ubicacionDestino.lat), Number(ubicacionDestino.lon)];
        const datosRuta = await calcularRuta(coordenadasOrigen, coordenadasDestino);
        if (idSolicitud !== solicitudActual) return;

        const feature = datosRuta.features && datosRuta.features[0];
        const geometria = feature && feature.geometry;
        const propiedades = feature && feature.properties;
        if (!geometria || !Array.isArray(geometria.coordinates) ||
            !propiedades || !Number.isFinite(propiedades.time) ||
            !Number.isFinite(propiedades.distance)) {
            throw new Error('El servicio no devolvió una ruta válida.');
        }

        const coordenadas = geometria.type === 'MultiLineString'
            ? geometria.coordinates.flat()
            : geometria.coordinates;
        const puntos = coordenadas.map(function (coordenada) {
            return [coordenada[1], coordenada[0]];
        });
        if (puntos.length < 2) {
            throw new Error('La ruta calculada no tiene suficientes puntos.');
        }

        const linea = L.polyline(puntos, {
            color: '#0b5fa5',
            weight: 5
        }).addTo(capasRuta);

        L.marker(puntos[0])
            .bindPopup('Origen registrado')
            .addTo(capasRuta);
        L.marker(puntos[puntos.length - 1])
            .bindPopup('Destino registrado')
            .addTo(capasRuta);

        const marcadorAmbulancia = L.marker(puntos[0], {
            title: 'Posición simulada de la ambulancia'
        }).bindPopup('Ambulancia · posición simulada').addTo(capasRuta);

        mapa.fitBounds(linea.getBounds(), { padding: [20, 20] });
        elementoDistancia.textContent = (propiedades.distance / 1000).toFixed(1) + ' km';
        elementoTiempo.textContent = mostrarDuracion(propiedades.time);

        if (String(traslado.estado).trim().toLowerCase() === 'en camino') {
            const resultadoTramos = crearTramos(puntos);
            if (!resultadoTramos.tramos.length || propiedades.time <= 0) {
                throw new Error('No se pudo iniciar la simulación de esta ruta.');
            }

            const inicioSimulacion = Date.now();
            elementoEstado.textContent = 'Simulación en curso según el tiempo estimado. No es GPS real.';
            intervaloSimulacion = setInterval(function () {
                const segundosTranscurridos = (Date.now() - inicioSimulacion) / 1000;
                const avance = Math.min(segundosTranscurridos / propiedades.time, 1);
                marcadorAmbulancia.setLatLng(posicionEnRuta(
                    resultadoTramos.tramos,
                    resultadoTramos.distanciaTotal * avance
                ));

                if (avance >= 1) {
                    clearInterval(intervaloSimulacion);
                    intervaloSimulacion = null;
                    elementoEstado.textContent = 'Simulación finalizada. No representa una ubicación GPS real.';
                }
            }, 1000);
        } else {
            elementoEstado.textContent = 'Traslado pendiente. El marcador permanece en el origen hasta que figure En camino. No es GPS real.';
        }
    } catch (error) {
        if (idSolicitud !== solicitudActual) return;
        elementoEstado.textContent = 'No se pudo mostrar la ruta: ' + error.message;
        elementoTiempo.textContent = 'No disponible';
        elementoDistancia.textContent = 'No disponible';
    }
}

async function cargarTraslados() {
    selectorTraslado.disabled = true;
    selectorTraslado.replaceChildren(new Option('Cargando traslados...', ''));

    try {
        const respuesta = await fetch('../php/traslados.php?action=list', {
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { Accept: 'application/json' }
        });
        let datos;
        try {
            datos = await respuesta.json();
        } catch (error) {
            throw new Error('El servidor no devolvió una respuesta válida. Verificá que XAMPP esté activo y que hayas iniciado sesión.');
        }
        if (!respuesta.ok || !datos.status) {
            throw new Error(datos.mensaje || 'No se pudieron cargar los traslados.');
        }

        traslados = Array.isArray(datos.traslados)
            ? datos.traslados.filter(esTrasladoVigente)
            : [];
        selectorTraslado.replaceChildren();

        if (traslados.length === 0) {
            selectorTraslado.appendChild(new Option('No hay traslados vigentes', ''));
            selectorTraslado.disabled = true;
            await mostrarRutaSeleccionada();
            return;
        }

        selectorTraslado.appendChild(new Option('Seleccioná un traslado', ''));
        traslados.forEach(function (traslado) {
            const texto = [
                traslado.ambulancia || 'Sin ambulancia',
                traslado.fecha || 'Sin fecha',
                traslado.estado || 'Sin estado'
            ].join(' · ');
            selectorTraslado.appendChild(new Option(texto, traslado.id));
        });

        selectorTraslado.disabled = false;
        selectorTraslado.addEventListener('change', mostrarRutaSeleccionada);
        selectorTraslado.value = String(traslados[0].id);
        await mostrarRutaSeleccionada();
    } catch (error) {
        selectorTraslado.replaceChildren(new Option('No se pudieron cargar los traslados', ''));
        selectorTraslado.disabled = true;
        elementoEstado.textContent = error.message;
        elementoTiempo.textContent = 'No disponible';
        elementoDistancia.textContent = 'No disponible';
        if (error.message === 'Iniciá sesión para gestionar traslados.') {
            window.location.href = 'login.html';
        }
    }
}

cargarTraslados();
