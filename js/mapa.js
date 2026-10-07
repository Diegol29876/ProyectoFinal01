const contenedorMapa = document.getElementById('visualizar');
contenedorMapa.style.height = '500px';
const mapa = L.map(contenedorMapa).setView([-34.89119, -56.15182], 15);

const ingresarO = document.getElementById('ingresarO');
const ubicar = document.getElementById('ubicar');
const departamento = document.getElementById('departamento');
const direccion = document.getElementById('dir');
const rutear = document.getElementById('rutear');

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(mapa);

const ImagenMarcador = L.icon({
    iconUrl: '../img/ambulancia.svg',
    iconSize: [38, 45]
})

const ImagenDestino = L.icon({
    iconUrl: '../img/des.png',
    iconSize: [38, 45]
})

const marcador = L.marker([-34.89119, -56.15182], {
    draggable: true,
    icon: ImagenMarcador
}).addTo(mapa);

const insertlat = document.getElementById('lat');
const insertlng = document.getElementById('lng');

ingresarO.addEventListener('click', () => {
    let lugarO = marcador.getLatLng();
    insertlat.value = lugarO.lat;
    insertlng.value = lugarO.lng;

    const marcadorDestino = L.marker([-34.89119, -56.15182], {
        draggable: true,
        icon: ImagenMarcador
    }).addTo(mapa);
})

ubicar.addEventListener('click', async () => {
    const texto = `${direccion.value}, ${departamento.value}, Uruguay`;
    const url = `https://api.geoapify.com/v1/geocode/search?` +
        `text=${encodeURIComponent(texto)}` +
        `&filter=countrycode:uy` +
        `&lang=es&limit=1&format=json&apiKey=4572a7318c164b3b873097a1f7b2c618`;
    const respuesta = await fetch(url);
    const dato = await respuesta.json();
    const resultado = dato.results[0];

    if (resultado) {
        const marcadorH = L.marker([`${resultado.lat}`, `${resultado.lon}`], {
            draggable: true,
            icon: ImagenDestino
        }).addTo(mapa);
        alert(`${resultado.lat}, ${resultado.lon}`);
    }

    //////////////////Desde acá comienza el routeo de mapa ATR ////////////////
    const origen = [insertlat.value, insertlng.value];
    const destino = [resultado.lat, resultado.lon];
    const urlRuta =
        `https://api.geoapify.com/v1/routing?` +
        `waypoints=${origen[0]},${origen[1]}|${destino[0]},${destino[1]}` +
        `&mode=drive` +
        `&units=metric` +
        `&lang=es` +
        `&apiKey=4572a7318c164b3b873097a1f7b2c618`;
    const resp = await fetch(urlRuta);
    const routeo = await resp.json();
    const routeoDatos = routeo.features[0].properties;
    console.log('Distancia (m):', routeoDatos.distance);
    console.log('Tiempo (s):', routeoDatos.time);
    console.log('Instrucciones:', routeoDatos.legs[0].steps);

    const dibujo = routeo.features[0].geometry.coordinates[0]
        .map(([lon, lat]) => [lat, lon]);
    L.polyline(dibujo, { color: 'blue', weight: 5 }).addTo(mapa);
    // 6) Ajustar vista a la ruta
    mapa.fitBounds(L.polyline(dibujo).getBounds());
})