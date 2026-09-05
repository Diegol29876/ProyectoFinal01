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
            <a download><button>Descargar</button></a>
            <button class="boton-qr">Generar QR</button>`;

        tarjeta.querySelector('h3').textContent = documento.nombre;
        tarjeta.querySelector('p').textContent = documento.tipo;
        tarjeta.querySelectorAll('a').forEach(function (enlace) {
            enlace.href = documento.archivo;
        });

        const botonQr = tarjeta.querySelector('.boton-qr');
        botonQr.dataset.archivo = documento.archivo;
        botonQr.dataset.nombre = documento.nombre;
        listaDocumentos.appendChild(tarjeta);
    });
}

listaDocumentos.addEventListener('click', (event) => {
    const boton = event.target.closest('.boton-qr');
    if (!boton) return;

    const ventana = window.open('', '_blank', 'width=320,height=360');
    if (!ventana) {
        alert('Permití las ventanas emergentes para ver el código QR.');
        return;
    }
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