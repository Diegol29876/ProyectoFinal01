<?php

require_once 'conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	$cedula = trim($_POST['cedula'] ?? '');
	$contrasenia = $_POST['password'] ?? '';

	if ($cedula === '' || $contrasenia === '') {
		echo json_encode([
			'status' => false,
			'mensaje' => 'Completá todos los campos.'
		]);
		exit;
	}

	try {

		$conexion = conectar_bd();

		// Buscar usuario por cédula
		$consulta = $conexion->prepare('SELECT n_usuario, contrasenia FROM funcionario WHERE cedula = ?');
		$consulta->bind_param('s', $cedula);
		$consulta->execute();
		$resultado = $consulta->get_result();

		if ($resultado->num_rows === 0) {
			echo json_encode([
				'status' => false,
				'mensaje' => 'Cédula o contraseña incorrecta.'
			]);
			exit;
		}

		$fila = $resultado->fetch_assoc();

		// Verificar contraseña
		if (password_verify($contrasenia, $fila['contrasenia'])) {
			echo json_encode([
				'status' => true,
				'mensaje' => 'Inicio de sesión exitoso.',
				'usuario' => $fila['n_usuario']
			]);
		} else {
			echo json_encode([
				'status' => false,
				'mensaje' => 'Cédula o contraseña incorrecta.'
			]);
		}

		$consulta->close();
		$conexion->close();

	} catch (Exception $error) {
		echo json_encode([
			'status' => false,
			'mensaje' => 'Error en el servidor.'
		]);
		exit;
	}

} else {
	echo json_encode([
		'status' => false,
		'mensaje' => 'Método no permitido.'
	]);
}
?>
