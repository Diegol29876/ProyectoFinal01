const listaDocumentos = document.getElementById('lista-documentos-publicos');

function mostrarDocumentos(documentos) {
    listaDocumentos.innerHTML = '';

    if (documentos.length === 0) {
        listaDocumentos.innerHTML = '<p>No hay documentos disponibles.</p>';
        return;
    }

    documentos.forEach(function (documento) {
        const tarjeta = document.createElement('div');
        tarjeta.className = 'documento';
        tarjeta.innerHTML = `
            <h3></h3>
            <p></p>
            <a target="_blank" rel="noopener"><button>Ver Documento</button></a>
            <a download><button>Descargar</button></a>`;

        tarjeta.querySelector('h3').textContent = documento.nombre;
        tarjeta.querySelector('p').textContent = documento.tipo;
        tarjeta.querySelectorAll('a').forEach(function (enlace) {
            enlace.href = documento.archivo;
        });

        listaDocumentos.appendChild(tarjeta);
    });
}

fetch('../php/documentos_publicos.php')
    .then((respuesta) => respuesta.json())
    .then(mostrarDocumentos)
    .catch(() => { listaDocumentos.innerHTML = '<p>No se pudieron cargar los documentos.</p>'; });