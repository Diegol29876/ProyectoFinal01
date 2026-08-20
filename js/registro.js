const formulario = document.querySelector('#form-registro');

formulario.addEventListener('submit', async function (evento) {
	evento.preventDefault();

	try {
		const respuesta = await fetch('../php/registro.php', {
			method: 'POST',
			body: new FormData(formulario)
		});
		const resultado = await respuesta.json();
        console.log("Error:" , resultado.status);
		alert(resultado.mensaje);
        
		if (resultado.ok) {
			formulario.reset();
		}
	} catch (error) {
		alert('No se pudo conectar con el servidor.');
	}
});
