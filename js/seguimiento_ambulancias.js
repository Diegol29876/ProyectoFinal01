let rutaMapa;
let marcadorOrigen;
let marcadorDestino;
let marcadorAmbulancia;

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

    if (boton && resultado) {
        boton.addEventListener("click", function() {
            resultado.textContent = "Obteniendo ubicación...";

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    let lat = position.coords.latitude;
                    let lon = position.coords.longitude;

                    resultado.innerHTML =
                        "Latitud: " + lat + "<br>" +
                        "Longitud: " + lon;

                    if (marcadorAmbulancia) {
                        window.mapa.removeLayer(marcadorAmbulancia);
                    }

                    marcadorAmbulancia = L.marker([lat, lon])
                        .addTo(window.mapa)
                        .bindPopup("Ubicación actual del dispositivo");

                    window.mapa.setView([lat, lon], 15);
                },
                function(error) {
                    resultado.textContent = "No se pudo obtener la ubicación.";

                    console.log("Error de geolocalización:", error);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 0
                }
            );
        });
    }

    document.getElementById("traslado").addEventListener("change", function() {
        let opcion = this.options[this.selectedIndex];

        document.getElementById("ambulancia").textContent =
            opcion.dataset.ambulancia || "—";+
        document.getElementById("origen-traslado").textContent =
            opcion.dataset.origen || "—";
        document.getElementById("destino-traslado").textContent =
            opcion.dataset.destino || "—";
        document.getElementById("estado").textContent =
            opcion.value ? opcion.dataset.estado : "Seleccioná una ambulancia";
        document.getElementById("distancia").textContent = "—";
        document.getElementById("hora").textContent = "—";

        if (opcion.dataset.origen && opcion.dataset.destino) {
            mostrarRuta(opcion.dataset.origen, opcion.dataset.destino);
        }
    });
});

function mostrarRuta(origen, destino) {
    let destinoCorregido = destino.replace(/Hostpital/gi, "Hospital");
    let origenLimpio = [...new Set(origen.split(",").map(p => p.trim()))].join(", ");
    let destinoLimpio = [...new Set(destinoCorregido.split(",").map(p => p.trim()))].join(", ");

    fetch("https://nominatim.openstreetmap.org/search?format=json&limit=1&q=" + encodeURIComponent(origen))
        .then(respuesta => respuesta.json())
        .then(punto1 => {
            return fetch("https://nominatim.openstreetmap.org/search?format=json&limit=1&q=" + encodeURIComponent(destino))
                .then(respuesta => respuesta.json())
                .then(punto2 => {

                    console.log("Origen recibido:", origen);
                    console.log("Destino recibido:", destinoCorregido);
                    console.log("Resultado origen:", punto1);
                    console.log("Resultado destino:", punto2);

                    if (!punto1.length || !punto2.length) {
                        throw new Error("No se encontraron las direcciones");
                    }

                    let inicio = punto1[0].lon + "," + punto1[0].lat;
                    let fin = punto2[0].lon + "," + punto2[0].lat;

                    let url = "https://router.project-osrm.org/route/v1/driving/" +
                        inicio + ";" + fin + "?overview=full&geometries=geojson";

                    return fetch(url);
                });
        })
        .then(respuesta => respuesta.json())
        .then(datos => {
            if (!datos.routes || !datos.routes.length) {
                throw new Error("No se encontró el recorrido");
            }

            let ruta = datos.routes[0];
            let minutos = Math.round(ruta.duration / 60);
            
            document.getElementById("distancia").textContent =
                (ruta.distance / 1000).toFixed(1) + " km";
            document.getElementById("hora").textContent =
                minutos >= 60
                    ? Math.floor(minutos / 60) + " h " + minutos % 60 + " min"
                    : minutos + " minutos";

            let puntos = ruta.geometry.coordinates.map(p => [p[1], p[0]]);

            if (rutaMapa) {
                window.mapa.removeLayer(rutaMapa);
            }

            if (marcadorOrigen) {
                window.mapa.removeLayer(marcadorOrigen);
            }

            if (marcadorDestino) {
                window.mapa.removeLayer(marcadorDestino);
            }

            rutaMapa = L.polyline(puntos, {
                color: "blue",
                weight: 5
            }).addTo(window.mapa);

            marcadorOrigen = L.marker(puntos[0])
                .addTo(window.mapa)
                .bindPopup("Punto de origen");

            marcadorDestino = L.marker(puntos[puntos.length - 1])
                .addTo(window.mapa)
                .bindPopup("Destino");

            window.mapa.fitBounds(rutaMapa.getBounds());
        })
        .catch(error => {
            console.log("Error al mostrar recorrido:", error);

            document.getElementById("distancia").textContent = "No disponible";
            document.getElementById("hora").textContent = "No disponible";
        });
}
