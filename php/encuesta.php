<?php

require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	echo 'Método no permitido.';
	exit;
}

$id_encuesta = intval($_POST['ID_Encuesta'] ?? 0);
$servicio = $_POST['servicio'] ?? '';
$atencion = $_POST['atencion'] ?? '';
$espera = $_POST['espera'] ?? '';
$instalaciones = $_POST['instalaciones'] ?? '';
$recomendaria = $_POST['recomendaria'] ?? '';
$comentarios = trim($_POST['comentarios'] ?? '');

if ($id_encuesta === 0 || $servicio === '' || $atencion === '' || $espera === '' ||
	$instalaciones === '' || $recomendaria === '') {
	echo 'Completá todos los campos obligatorios.';
	exit;
}

try {
	$conexion = conectar_bd();
	$conexion->begin_transaction();
	$id_envio = bin2hex(random_bytes(16));

	$consulta = $conexion->prepare(
		'INSERT INTO Respuesta
		(ID_Encuesta, Fecha, Clasificacion, Respuesta_Texto, ID_Envio)
		VALUES (?, NOW(), ?, ?, ?)'
	);

	$respuestas = [
		'Servicio utilizado' => $servicio,
		'Atención recibida' => $atencion,
		'Tiempo de espera' => $espera,
		'Instalaciones' => $instalaciones,
		'Recomendaría el hospital' => $recomendaria,
		'Comentarios' => $comentarios
	];

	foreach ($respuestas as $clasificacion => $respuesta_texto) {
		$consulta->bind_param('isss', $id_encuesta, $clasificacion, $respuesta_texto, $id_envio);
		$consulta->execute();
	}

	$consulta->close();
	$conexion->commit();
	$conexion->close();

	echo 'Encuesta enviada correctamente.';
} catch (mysqli_sql_exception $error) {
	if (isset($conexion) && $conexion->thread_id) {
		$conexion->rollback();
	}
	error_log('Error al guardar encuesta: ' . $error->getMessage());
	echo 'No se pudo guardar la encuesta. Revisá que exista la encuesta con ID 1.';
}

?>
