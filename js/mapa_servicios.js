const API_KEY = '4572a7318c164b3b873097a1f7b2c618';

export async function buscarDestino(direccion, departamento, origen) {
    const texto = `${direccion.trim()}, ${departamento}, Uruguay`;
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
    return respuesta.json();
}
