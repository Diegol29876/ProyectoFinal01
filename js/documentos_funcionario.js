let documentos = [];
const lista = document.getElementById('lista-documentos');
const contador = document.getElementById('contador-documentos');
const formulario = document.getElementById('form-documento');
const estado = document.getElementById('estado-carga');
const busqueda = document.getElementById('buscar-documento');

function renderizarDocumentos() {
    const termino = busqueda.value.trim().toLowerCase();
    const visibles = documentos.filter(function (documento) {
        return documento.nombre.toLowerCase().includes(termino);
    });

    contador.textContent = documentos.length + (documentos.length === 1 ? ' documento' : ' documentos');
    lista.innerHTML = '';

    if (visibles.length === 0) {
        lista.innerHTML = '<p class="sin-resultados">No se encontraron documentos.</p>';
        return;
    }

    visibles.forEach(function (documento) {
        const tarjeta = document.createElement('article');
        tarjeta.className = 'documento-card';
        tarjeta.innerHTML = `
            <span class="tipo"></span>
            <h3></h3>
            <div class="acciones">
                <a class="accion" target="_blank" rel="noopener">Ver documento</a>
                <button class="accion" data-accion="editar">Modificar</button>
                <button class="accion eliminar" data-accion="eliminar">Eliminar</button>
                <button class="accion" data-accion="qr">Generar QR</button>
            </div>`;

        tarjeta.querySelector('.tipo').textContent = documento.tipo;
        tarjeta.querySelector('h3').textContent = documento.nombre;
        tarjeta.querySelector('a').href = documento.archivo;

        tarjeta.querySelectorAll('[data-accion]').forEach(function (boton) {
            boton.dataset.id = documento.id;
        });

        lista.appendChild(tarjeta);
    });
}

async function cargarCatalogo() {
    try {
        const respuesta = await fetch('../php/documentos_publicos.php');
        if (!respuesta.ok) throw new Error('No se pudo cargar el catálogo.');
        documentos = await respuesta.json();
        renderizarDocumentos();
    } catch (error) {
        lista.innerHTML = '<p class="sin-resultados">No se pudieron cargar los documentos.</p>';
    }
}

async function cargarDocumento(event) {
    event.preventDefault();
    const datos = new FormData(formulario);
    estado.textContent = 'Cargando documento...';

    try {
        const respuesta = await fetch('../php/carga.php', { method: 'POST', body: datos });
        if (!respuesta.ok) throw new Error('No se pudo conectar con el servidor.');
        const mensaje = await respuesta.text();
        formulario.reset();
        estado.textContent = mensaje || 'Documento cargado correctamente.';
        await cargarCatalogo();
    } catch (error) {
        estado.textContent = `No se pudo cargar el documento: ${error.message}`;
    }
}

function generarQr(documento) {
    const vista = document.getElementById('qr-preview');
    const descarga = document.getElementById('descargar-qr');
    vista.innerHTML = '';
    new QRCode(vista, { text: new URL(documento.archivo, window.location.href).href, width: 164, height: 164 });
    document.getElementById('qr-ayuda').textContent = `Código generado para: ${documento.nombre}`;
    setTimeout(() => {
        const imagen = vista.querySelector('img');
        if (imagen) {
            descarga.href = imagen.src;
            descarga.classList.remove('oculto');
        }
    }, 100);
}

lista.addEventListener('click', (event) => {
    const boton = event.target.closest('[data-accion]');
    if (!boton) return;
    const documento = documentos.find(function (item) {
        return String(item.id) === boton.dataset.id;
    });

    if (!documento) return;

    if (boton.dataset.accion === 'qr') {
        generarQr(documento);
    }

    if (boton.dataset.accion === 'eliminar') {
        if (confirm('¿Eliminar "' + documento.nombre + '"?')) {
            documentos = documentos.filter(function (item) {
                return item.id !== documento.id;
            });
            renderizarDocumentos();
        }
    }

    if (boton.dataset.accion === 'editar') {
        const nombreNuevo = prompt('Nuevo nombre del documento:', documento.nombre);

        if (nombreNuevo && nombreNuevo.trim()) {
            documento.nombre = nombreNuevo.trim();
            renderizarDocumentos();
        }
    }
});

formulario.addEventListener('submit', cargarDocumento);
busqueda.addEventListener('input', renderizarDocumentos);
cargarCatalogo();