const listaDocumentos = document.getElementById('lista-documentos-publicos');

function mostrarDocumentos(documentos) {
    listaDocumentos.innerHTML = documentos.length ? documentos.map((documento) => `
        <div class="documento">
            <h3>${documento.nombre}</h3>
            <p>${documento.tipo}</p>
            <a href="${documento.archivo}" target="_blank" rel="noopener"><button>Ver Documento</button></a>
            <a href="${documento.archivo}" download><button>Descargar</button></a>
            <button class="boton-qr" data-archivo="${documento.archivo}" data-nombre="${documento.nombre}">Generar QR</button>
        </div>
    `).join('') : '<p>No hay documentos disponibles.</p>';
}

listaDocumentos.addEventListener('click', (event) => {
    const boton = event.target.closest('.boton-qr');
    if (!boton) return;

    const ventana = window.open('', '_blank', 'width=320,height=360');
    ventana.document.write(`<title>QR - ${boton.dataset.nombre}</title><h3>${boton.dataset.nombre}</h3><div id="qr"></div>`);
    ventana.document.close();
    new QRCode(ventana.document.getElementById('qr'), {
        text: new URL(boton.dataset.archivo, window.location.href).href,
        width: 220,
        height: 220
    });
});

fetch('../php/documentos_publicos.php')
    .then((respuesta) => respuesta.json())
    .then(mostrarDocumentos)
    .catch(() => { listaDocumentos.innerHTML = '<p>No se pudieron cargar los documentos.</p>'; });