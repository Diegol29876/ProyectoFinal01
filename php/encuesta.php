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

	$consulta = $conexion->prepare(
		'INSERT INTO Respuesta
		(ID_Encuesta, Fecha, Clasificacion, Respuesta_Texto)
		VALUES (?, NOW(), ?, ?)'
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
		$consulta->bind_param('iss', $id_encuesta, $clasificacion, $respuesta_texto);
		$consulta->execute();
	}

	$consulta->close();
	$conexion->commit();
	$conexion->close();

	echo 'Encuesta enviada correctamente.';
} catch (mysqli_sql_exception $error) {
	if (isset($conexion)) {
		$conexion->rollback();
	}
	echo 'No se pudo guardar la encuesta.';
}

?>
