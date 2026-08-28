<?php

require_once 'conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	$usuario = trim($_POST['n_usuario'] ?? '');
	$correo = trim($_POST['c_electronico'] ?? '');
	$telefono = trim($_POST['n_telefono'] ?? '');
	$contrasenia = $_POST['contraseña'] ?? '';
	$direccion = trim($_POST['direccion'] ?? '');
	$nacimiento = $_POST['f_nacimiento'] ?? '';
	$cedula = trim($_POST['cedula'] ?? '');
	$estado = trim($_POST['estado'] ?? '');
	$ingreso = $_POST['f_ingreso'] ?? '';

	if ($usuario === '' || $correo === '' || $telefono === '' || $contrasenia === '' ||
	$direccion === '' || $nacimiento === '' || $cedula === '' || $estado === '' || $ingreso === '') {

		echo json_encode([
			'status' => false,
			'mensaje' => 'Completá todos los campos.'
			]);
		exit;
	}

	if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {

		echo json_encode([
			'status' => false,
			'mensaje' => 'El correo no es válido.'
			]);
		exit;
	}

	try {

		$conexion = conectar_bd();
		$contrasenia = password_hash($contrasenia, PASSWORD_DEFAULT);
		
		$consulta = $conexion->prepare(
			'INSERT INTO funcionario
			(n_usuario, contrasenia, c_electronico, direccion,
			f_nacimineto, cedula, n_telefono, f_Ingreso, estado)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
		);

		$consulta->bind_param(
			'sssssssss',
			$usuario,
			$contrasenia,
			$correo,
			$direccion,
			$nacimiento,
			$cedula,
			$telefono,
			$ingreso,
			$estado
		);

		$consulta->execute();

		$consulta->close();

		echo json_encode([
			'status' => true,
			'mensaje' => 'Registro creado correctamente.'
			]);
		exit;

	} catch (mysqli_sql_exception $error) {
		
		echo json_encode([
			'status' => false,
			'mensaje' => $error->getCode() === 1062
				? 'El usuario, correo o cédula ya está registrado.'
				: 'No se pudo guardar el registro.'
		]);
		exit;
	}

}