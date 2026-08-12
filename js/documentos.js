const archivo = document.getElementById('archivo');
const nombre = document.getElementById('nombre');
const boton = document.getElementById('boton');

if (boton && archivo && nombre) {
    boton.addEventListener('click', async (e) => {
        e.preventDefault();
        const doc = new FormData();

        let nom_bien = nombre.value.trim();
        if (nom_bien === '') {
            alert('Por favor, ingrese un nombre para el documento.');
            return;
        }else{
            doc.append('nombre', nom_bien);

        }

        
        if (archivo.files.length > 0) {
            doc.append('archivo', archivo.files[0]);
        }
        

        try {
            const respuesta = await fetch('../php/carga.php', {
                method: 'POST',
                body: doc,
            });

            const mensaje = await respuesta.text();
            console.log(mensaje);
            alert(mensaje);
        } catch (error) {
            alert(error);
            console.error('Error al subir el documento:', error);
        }
    });
} else {
    console.warn('No se encontró el formulario de carga de documentos.');
}
