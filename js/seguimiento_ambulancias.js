import { buscarDireccionCompleta, calcularRuta } from './mapa_servicios.js';

let rutaMapa;
let marcadorOrigen;
let marcadorDestino;
let marcadorAmbulancia;
let recorridoAmbulancia;
let puntosRecorrido = [];
let intervaloUbicacion;
let trasladoEnSeguimiento = null;
let ubicacionEnCurso = false;
let seguimientoVersion = 0;
let solicitudRuta = 0;

const iconoOrigen = L.icon({
    iconUrl: "../img/ambulancia.svg",
    iconSize: [38, 45]
});
const iconoDestino = L.icon({
    iconUrl: "../img/ubicacion.png",
    iconSize: [38, 45]
});

function cargarAmbulancias() {
    let lista = document.getElementById("traslado");

    fetch("../php/seguimiento_ambulancias.php")
        .then(respuesta => respuesta.json())
        .then(datos => {
            lista.innerHTML = '<option value="">Elegí una ambulancia</option>';

            datos.forEach(a => {
                let opcion = document.createElement("option");

                opcion.value = a.ID_Traslado;
                opcion.textContent = "Traslado " + a.ID_Traslado + " - " + a.Matricula;

                opcion.dataset.ambulancia = a.Matricula + " - " + a.Modelo;
                opcion.dataset.origen = a.Origen || "";
                opcion.dataset.destino = a.Destino || "";
                opcion.dataset.estado = a.Estado || "Sin traslado";

                lista.appendChild(opcion);
            });
        });
}

document.addEventListener("DOMContentLoaded", function() {
    cargarAmbulancias();

    let boton = document.getElementById("boton-ubicacion");
    let resultado = document.getElementById("resultado-ubicacion");
    let listaTraslados = document.getElementById("traslado");

    if (boton && resultado) {
        boton.addEventListener("click", function() {
            if (trasladoEnSeguimiento) {
                detenerSeguimiento();
                resultado.textContent = "Se dejó de compartir la ubicación.";
                return;
            }

            if (!listaTraslados.value) {
                resultado.textContent = "Seleccioná un traslado antes de compartir la ubicación.";
                return;
            }

            if (!navigator.geolocation) {
                resultado.textContent = "Este dispositivo no permite obtener la ubicación.";
                return;
            }

            trasladoEnSeguimiento = listaTraslados.value;
            seguimientoVersion += 1;
            boton.textContent = "Dejar de compartir ubicación";
            resultado.textContent = "Solicitando permiso y obteniendo ubicación...";
            solicitarUbicacion();
            intervaloUbicacion = window.setInterval(solicitarUbicacion, 5000);
        });
    }

    listaTraslados.addEventListener("change", function() {
        if (trasladoEnSeguimiento) {
            detenerSeguimiento();
            resultado.textContent = "Se dejó de compartir la ubicación porque cambió el traslado.";
        }

        let opcion = this.options[this.selectedIndex];

        document.getElementById("ambulancia").textContent =
            opcion.dataset.ambulancia || "—";
        document.getElementById("origen-traslado").textContent =
            opcion.dataset.origen || "—";
        document.getElementById("destino-traslado").textContent =
            opcion.dataset.destino || "—";
        document.getElementById("estado").textContent =
            opcion.value ? opcion.dataset.estado : "Seleccioná una ambulancia";
        document.getElementById("distancia").textContent = "—";
        document.getElementById("hora").textContent = "—";

        limpiarRuta();
        limpiarRecorrido();

        if (opcion.dataset.origen && opcion.dataset.destino) {
            mostrarRuta(opcion.dataset.origen, opcion.dataset.destino);
        }
    });
});

function detenerSeguimiento() {
    window.clearInterval(intervaloUbicacion);
    intervaloUbicacion = null;
    trasladoEnSeguimiento = null;
    seguimientoVersion += 1;
    ubicacionEnCurso = false;
    document.getElementById("boton-ubicacion").textContent = "Obtener ubicación actual";
}

function solicitarUbicacion() {
    if (ubicacionEnCurso || !trasladoEnSeguimiento) {
        return;
    }

    const versionActual = seguimientoVersion;
    const idTraslado = trasladoEnSeguimiento;
    ubicacionEnCurso = true;
    navigator.geolocation.getCurrentPosition(
        async function(position) {
            if (
                versionActual !== seguimientoVersion ||
                idTraslado !== trasladoEnSeguimiento
            ) {
                return;
            }

            const latitud = position.coords.latitude;
            const longitud = position.coords.longitude;
            const coordenadas = [latitud, longitud];

            if (marcadorAmbulancia) {
                marcadorAmbulancia.setLatLng(coordenadas);
            } else {
                marcadorAmbulancia = L.marker(coordenadas)
                    .addTo(window.mapa)
                    .bindPopup("Ubicación actual del dispositivo");
                window.mapa.setView(coordenadas, 15);
            }

            if (!window.mapa.getBounds().contains(coordenadas)) {
                window.mapa.panTo(coordenadas);
            }

            puntosRecorrido.push(coordenadas);
            if (recorridoAmbulancia) {
                recorridoAmbulancia.setLatLngs(puntosRecorrido);
            } else {
                recorridoAmbulancia = L.polyline(puntosRecorrido, {
                    color: "#e4572e",
                    weight: 5,
                    opacity: 0.9
                }).addTo(window.mapa);
            }

            try {
                const respuesta = await fetch("../php/gps_ambulancias.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        id_traslado: idTraslado,
                        latitud: latitud,
                        longitud: longitud
                    })
                });
                const datos = await respuesta.json();

                if (!respuesta.ok) {
                    throw new Error(datos.error || "No se pudo guardar la ubicación.");
                }

                if (trasladoEnSeguimiento === idTraslado) {
                    document.getElementById("resultado-ubicacion").textContent =
                        "Ubicación actualizada y guardada para el traslado " + idTraslado + ".";
                }
            } catch (error) {
                if (trasladoEnSeguimiento === idTraslado) {
                    document.getElementById("resultado-ubicacion").textContent =
                        error.message || "No se pudo enviar la ubicación.";
                }
            } finally {
                if (versionActual === seguimientoVersion) {
                    ubicacionEnCurso = false;
                }
            }
        },
        function(error) {
            if (
                versionActual !== seguimientoVersion ||
                idTraslado !== trasladoEnSeguimiento
            ) {
                return;
            }
            ubicacionEnCurso = false;

            if (error.code === 1) {
                detenerSeguimiento();
                document.getElementById("resultado-ubicacion").textContent =
                    "No se otorgó permiso para acceder a la ubicación.";
                return;
            }

            if (trasladoEnSeguimiento) {
                document.getElementById("resultado-ubicacion").textContent =
                    "No se pudo obtener la ubicación. Se volverá a intentar.";
            }
            console.log("Error de geolocalización:", error);
        },
        {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0
        }
    );
}

function limpiarRuta() {
    solicitudRuta += 1;
    [rutaMapa, marcadorOrigen, marcadorDestino].forEach(function(capa) {
        if (capa) {
            window.mapa.removeLayer(capa);
        }
    });

    rutaMapa = null;
    marcadorOrigen = null;
    marcadorDestino = null;
}

function limpiarRecorrido() {
    [recorridoAmbulancia, marcadorAmbulancia].forEach(function(capa) {
        if (capa) {
            window.mapa.removeLayer(capa);
        }
    });

    recorridoAmbulancia = null;
    marcadorAmbulancia = null;
    puntosRecorrido = [];
}

async function mostrarRuta(origen, destino) {
    const versionActual = ++solicitudRuta;

    try {
        const resultadoOrigen = await buscarDireccionCompleta(origen);
        if (!resultadoOrigen) {
            throw new Error("No se encontró la dirección del origen.");
        }

        const posicionOrigen = [Number(resultadoOrigen.lat), Number(resultadoOrigen.lon)];
        const resultadoDestino = await buscarDireccionCompleta(destino, posicionOrigen);
        if (!resultadoDestino) {
            throw new Error("No se encontró la dirección del destino.");
        }

        const posicionDestino = [Number(resultadoDestino.lat), Number(resultadoDestino.lon)];
        const datosRuta = await calcularRuta(posicionOrigen, posicionDestino);

        if (versionActual !== solicitudRuta) {
            return;
        }

        rutaMapa = L.polyline(datosRuta.puntos, {
            color: "blue",
            weight: 5
        }).addTo(window.mapa);

        marcadorOrigen = L.marker(posicionOrigen, { icon: iconoOrigen })
            .addTo(window.mapa)
            .bindPopup("Origen");
        marcadorDestino = L.marker(posicionDestino, { icon: iconoDestino })
            .addTo(window.mapa)
            .bindPopup("Destino");

        document.getElementById("distancia").textContent =
            Number.isFinite(datosRuta.distanciaMetros)
                ? (datosRuta.distanciaMetros / 1000).toFixed(1) + " km"
                : "No disponible";
        const minutos = Math.round(datosRuta.duracionSegundos / 60);
        document.getElementById("hora").textContent =
            minutos >= 60
                ? Math.floor(minutos / 60) + " h " + minutos % 60 + " min"
                : minutos + " minutos";

        window.mapa.fitBounds(L.featureGroup([
            rutaMapa,
            marcadorOrigen,
            marcadorDestino
        ]).getBounds());
    } catch (error) {
        if (versionActual !== solicitudRuta) {
            return;
        }

        console.log("Error al mostrar recorrido:", error);
        document.getElementById("distancia").textContent = "No disponible";
        document.getElementById("hora").textContent = "No disponible";
    }
}