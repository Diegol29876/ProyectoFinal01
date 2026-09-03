const documentosIniciales = [
    { id: 1, nombre: 'Preparación para estudios imagenológicos', tipo: 'Manual de preparación', archivo: '../pdf/preparacion_estudios.pdf' },
    { id: 2, nombre: 'Indicaciones para pacientes con Warfarina', tipo: 'Pauta de enfermería', archivo: '../pdf/warfarina.pdf' },
    { id: 3, nombre: 'Prevención de infecciones', tipo: 'Documento de paciente', archivo: '../pdf/infecciones.pdf' },
];

let documentos = [...documentosIniciales];
const lista = document.getElementById('lista-documentos');
const contador = document.getElementById('contador-documentos');
const formulario = document.getElementById('form-documento');
const estado = document.getElementById('estado-carga');
const busqueda = document.getElementById('buscar-documento');

function renderizarDocumentos() {
    const termino = busqueda.value.trim().toLowerCase();
    const visibles = documentos.filter((documento) => documento.nombre.toLowerCase().includes(termino));
    contador.textContent = `${documentos.length} ${documentos.length === 1 ? 'documento' : 'documentos'}`;
    lista.innerHTML = visibles.length ? visibles.map((documento) => `
        <article class="documento-card">
            <span class="tipo">${documento.tipo}</span>
            <h3>${documento.nombre}</h3>
            <div class="acciones">
                <a class="accion" href="${documento.archivo}" target="_blank" rel="noopener">Ver documento</a>
                <button class="accion" data-accion="editar" data-id="${documento.id}">Modificar</button>
                <button class="accion eliminar" data-accion="eliminar" data-id="${documento.id}">Eliminar</button>
                <button class="accion" data-accion="qr" data-id="${documento.id}">Generar QR</button>
            </div>
        </article>
    `).join('') : '<p class="sin-resultados">No se encontraron documentos.</p>';
}

async function cargarDocumento(event) {
    event.preventDefault();
    const archivo = document.getElementById('archivo-documento').files[0];
    const datos = new FormData(formulario);
    estado.textContent = 'Cargando documento...';

    try {
        const respuesta = await fetch('../php/carga.php', { method: 'POST', body: datos });
        if (!respuesta.ok) throw new Error('No se pudo conectar con el servidor.');
        const mensaje = await respuesta.text();
        documentos.unshift({
            id: Date.now(),
            nombre: datos.get('nombre'),
            tipo: datos.get('tipo'),
            archivo: URL.createObjectURL(archivo),
        });
        formulario.reset();
        estado.textContent = mensaje || 'Documento cargado correctamente.';
        renderizarDocumentos();
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
    const documento = documentos.find((item) => item.id === Number(boton.dataset.id));
    if (boton.dataset.accion === 'qr') generarQr(documento);
    if (boton.dataset.accion === 'eliminar' && confirm(`¿Eliminar “${documento.nombre}”?`)) {
        documentos = documentos.filter((item) => item.id !== documento.id);
        renderizarDocumentos();
    }
    if (boton.dataset.accion === 'editar') {
        const nombre = prompt('Nuevo nombre del documento:', documento.nombre);
        if (nombre && nombre.trim()) documento.nombre = nombre.trim();
        renderizarDocumentos();
    }
});

formulario.addEventListener('submit', cargarDocumento);
busqueda.addEventListener('input', renderizarDocumentos);
renderizarDocumentos();