const formulario = document.querySelector('#form-registro')

formulario.addEventListener('submit', async (e) => {
	e.preventDefault()

	try {

		const respuesta = await fetch('../php/registro.php', {
			method: 'POST',
			body: new FormData(formulario)
		})

		const resultado = await respuesta.json();

		if (!respuesta.ok || !resultado.status) {

			alert(resultado.mensaje || 'Ocurrió un error al registrar el usuario.')
			return
		}

		alert('Usuario registrado correctamente.')
		console.log(resultado.mensaje)

	} catch (error) {

		alert('No se pudo conectar con el servidor.')
		return
	}
})
