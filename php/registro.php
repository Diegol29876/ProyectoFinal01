<?php

require_once __DIR__ . '/conexion.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido.']);
	exit;
}


$usuario = trim($_POST['n_usuario'] ?? '');
$correo = trim($_POST['c_electronico'] ?? '');
$telefono = trim($_POST['n_telefono'] ?? '');
$contraseña = $_POST['contraseña'] ?? '';
$direccion = trim($_POST['direccion'] ?? '');
$nacimiento = $_POST['f_nacimiento'] ?? '';
$cedula = trim($_POST['cedula'] ?? '');
$estado = trim($_POST['estado'] ?? '');
$ingreso = $_POST['f_ingreso'] ?? '';

if ($usuario === '' || $correo === '' || $telefono === '' || $contraseña === '' ||
	$direccion === '' || $nacimiento === '' || $cedula === '' || $estado === '' || $ingreso === '') {
	echo json_encode(['ok' => false, 'mensaje' => 'Completá todos los campos.']);
	exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
	echo json_encode(['ok' => false, 'mensaje' => 'El correo no es válido.']);
	exit;
}

try {
	$conexion = conectar_bd();
	$consulta = $conexion->prepare(
		'INSERT INTO funcionarios
		(n_usuario, c_electronico, n_telefono, contraseña, direccion, f_nacimiento, cedula, estado, f_ingreso)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
	);

	$contraseña = password_hash($contraseña, PASSWORD_DEFAULT);
	$consulta->bind_param(
		'ssissssss',
		$usuario,
		$correo,
		$telefono,
		$contraseña,
		$direccion,
		$nacimiento,
		$cedula,
		$estado,
		$ingreso
	);
	$consulta->execute();

	$consulta->close();

	echo json_encode(['ok' => true, 'mensaje' => 'Registro creado correctamente.']);
} catch (mysqli_sql_exception $error) {
	http_response_code($error->getCode() === 1062 ? 409 : 500);
	echo json_encode([
		'ok' => false,
		'mensaje' => $error->getCode() === 1062
			? 'El usuario, correo o cédula ya está registrado.'
			: 'No se pudo guardar el registro.'
	]);
}
