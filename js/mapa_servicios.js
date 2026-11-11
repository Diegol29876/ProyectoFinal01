const API_KEY = '4572a7318c164b3b873097a1f7b2c618';

export async function buscarDestino(direccion, departamento, origen) {
    const texto = `${direccion.trim()}, ${departamento}, Uruguay`;
    return buscarDireccion(texto, origen);
}

export async function buscarDireccionCompleta(direccion, origen) {
    const texto = /uruguay/i.test(direccion) ? direccion.trim() : `${direccion.trim()}, Uruguay`;
    return buscarDireccion(texto, origen);
}

async function buscarDireccion(texto, origen) {
    const parametros = new URLSearchParams({
        text: texto,
        filter: 'countrycode:uy',
        lang: 'es',
        limit: '1',
        format: 'json',
        apiKey: API_KEY
    });
    if (origen && origen.length === 2) {
        parametros.set('bias', `proximity:${origen[1]},${origen[0]}`);
    }
    const url = `https://api.geoapify.com/v1/geocode/search?${parametros}`;
    const respuesta = await fetch(url);
    if (!respuesta.ok) throw new Error('Falló la búsqueda de la dirección.');
    const datos = await respuesta.json();
    return datos.results[0] || null;
}

export async function calcularRuta(origen, destino) {
    const url = `https://api.geoapify.com/v1/routing?` +
        `waypoints=${origen[0]},${origen[1]}|${destino[0]},${destino[1]}` +
        `&mode=drive&units=metric&lang=es&apiKey=${API_KEY}`;
    const respuesta = await fetch(url);
    if (!respuesta.ok) throw new Error('Falló el cálculo de la ruta.');

    const datos = await respuesta.json();
    const featureRuta = datos.features && datos.features[0];
    const geometria = featureRuta && featureRuta.geometry;
    const coordenadas = geometria && (geometria.type === 'MultiLineString'
        ? geometria.coordinates[0]
        : geometria.coordinates);

    if (!Array.isArray(coordenadas) || coordenadas.length === 0) {
        throw new Error('No se pudo calcular la ruta.');
    }
    if (!featureRuta.properties || typeof featureRuta.properties.time !== 'number' ||
        !Number.isFinite(featureRuta.properties.time) || featureRuta.properties.time < 0) {
        throw new Error('No se pudo calcular el tiempo estimado de llegada.');
    }

    return {
        puntos: coordenadas.map(function ([longitud, latitud]) {
            return [latitud, longitud];
        }),
        distanciaMetros: Number(featureRuta.properties.distance),
        duracionSegundos: featureRuta.properties.time
    };
}
