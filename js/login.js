const formulario = document.getElementById('form-login');
const cedulaInput = document.getElementById('cedula');

// Filtrar solo números en cédula
if (cedulaInput) {
	cedulaInput.addEventListener("input", function() {
		this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8);
	});
}

// Enviar formulario
if (formulario) {
	formulario.addEventListener('submit', function(e) {
		e.preventDefault();

		const cedula = document.getElementById('cedula').value;
		const password = document.getElementById('password').value;

		if (!cedula || !password) {
			alert('Por favor completa todos los campos');
			return;
		}

		// Crear FormData
		const datos = new FormData();
		datos.append('cedula', cedula);
		datos.append('password', password);

		// Enviar al servidor
		fetch('../php/login.php', {
			method: 'POST',
			body: datos
		})
		.then(respuesta => respuesta.json())
		.then(resultado => {
			console.log('Respuesta:', resultado);
			
			if (resultado.status) {
				alert(resultado.mensaje);
				// Redirigir después de 1 segundo
				setTimeout(() => {
					window.location.href = './panel_fun.html';
				}, 1000);
			} else {
				alert('Error: ' + resultado.mensaje);
			}
		})
		.catch(error => {
			console.error('Error:', error);
			alert('No se pudo conectar con el servidor');
		});
	});
} else {
	console.error('Formulario no encontrado');
}