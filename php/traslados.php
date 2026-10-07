<?php
session_start();
require_once __DIR__ . '/conexion.php';

header('Content-Type: application/json; charset=utf-8');

function responder_json(bool $status, string $mensaje, array $datos = [], int $codigo = 200): void
{
    http_response_code($codigo);
    echo json_encode(array_merge(['status' => $status, 'mensaje' => $mensaje], $datos), JSON_UNESCAPED_UNICODE);
    exit;
}

function entero_positivo($valor): int
{
    $entero = filter_var($valor, FILTER_VALIDATE_INT);
    return $entero !== false && $entero > 0 ? $entero : 0;
}

function fecha_valida(string $fecha): bool
{
    $formato = DateTime::createFromFormat('!Y-m-d', $fecha);
    return $formato !== false && $formato->format('Y-m-d') === $fecha;
}

function hora_valida(string $hora): bool
{
    return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $hora) === 1;
}

if (!isset($_SESSION['funcionario_id'])) {
    responder_json(false, 'Iniciá sesión para gestionar traslados.', [], 401);
}

$accion = $_SERVER['REQUEST_METHOD'] === 'GET'
    ? ($_GET['action'] ?? '')
    : '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $conexion = conectar_bd();

        if ($accion === 'list') {
            $resultado = $conexion->query(
                "SELECT t.ID_Traslado AS id, r.origen, r.destino,
                        t.Fecha_solicitud AS fecha, TIME_FORMAT(t.Hora_Salida, '%H:%i') AS hora,
                        NULLIF(TIME_FORMAT(t.Hora_llegada_estimada, '%H:%i'), '00:00') AS hora_estimada,
                        NULLIF(TIME_FORMAT(t.Hora_llegada_efectiva, '%H:%i'), '00:00') AS hora_efectiva,
                        t.Estado AS estado, t.Observaciones AS observaciones,
                        t.ID_Conductor AS conductor_id, t.ID_ambulancia AS ambulancia_id,
                        p.paciente, p.paciente_id,
                        c.Nombre AS conductor, CONCAT(a.Modelo, ' (', a.Matricula, ')') AS ambulancia,
                        ac.acompanante
                 FROM traslado t
                 LEFT JOIN (
                     SELECT ID_Traslado,
                            MAX(CASE WHEN Tipo = 'Origen' THEN Descripcion END) AS origen,
                            MAX(CASE WHEN Tipo = 'Destino' THEN Descripcion END) AS destino
                     FROM ruta
                     WHERE Tipo IN ('Origen', 'Destino')
                     GROUP BY ID_Traslado
                 ) r ON r.ID_Traslado = t.ID_Traslado
                 LEFT JOIN (
                     SELECT pt.ID_Traslado,
                            GROUP_CONCAT(CONCAT(p.Nombre, ' ', p.Apellido) SEPARATOR ', ') AS paciente,
                            MIN(p.ID_Paciente) AS paciente_id
                     FROM paciente_traslado pt
                     INNER JOIN paciente p ON p.ID_Paciente = pt.ID_Paciente
                     GROUP BY pt.ID_Traslado
                 ) p ON p.ID_Traslado = t.ID_Traslado
                 LEFT JOIN conductor c ON c.ID_Conductor = t.ID_Conductor
                 LEFT JOIN ambulancia a ON a.ID_ambulancia = t.ID_ambulancia
                 LEFT JOIN (
                     SELECT ID_Traslado, MAX(Nombre) AS acompanante
                     FROM acompañante
                     GROUP BY ID_Traslado
                 ) ac ON ac.ID_Traslado = t.ID_Traslado
                 ORDER BY t.Fecha_solicitud DESC, t.Hora_Salida DESC, t.ID_Traslado DESC"
            );
            responder_json(true, '', [
                'traslados' => $resultado->fetch_all(MYSQLI_ASSOC),
                'csrf_token' => $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32))
            ]);
        }

        if ($accion === 'catalogs') {
            $pacientes = $conexion->query("SELECT ID_Paciente AS id, CONCAT(Nombre, ' ', Apellido, ' (', Cedula, ')') AS nombre FROM paciente ORDER BY Apellido, Nombre")->fetch_all(MYSQLI_ASSOC);
            $conductores = $conexion->query('SELECT ID_Conductor AS id, Nombre AS nombre FROM conductor ORDER BY Nombre')->fetch_all(MYSQLI_ASSOC);
            $ambulancias = $conexion->query("SELECT ID_ambulancia AS id, CONCAT(Modelo, ' (', Matricula, ')') AS nombre FROM ambulancia ORDER BY Matricula")->fetch_all(MYSQLI_ASSOC);
            responder_json(true, '', [
                'pacientes' => $pacientes,
                'conductores' => $conductores,
                'ambulancias' => $ambulancias,
                'csrf_token' => $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32))
            ]);
        }

        responder_json(false, 'Operación no válida.', [], 400);
    } catch (mysqli_sql_exception $error) {
        error_log('traslados.php GET: ' . $error->getMessage());
        responder_json(false, 'No se pudieron consultar los traslados. Verificá que la migración del módulo esté aplicada.', [], 500);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder_json(false, 'Método no permitido.', [], 405);
}

$entrada = json_decode(file_get_contents('php://input'), true);
if (!is_array($entrada)) {
    responder_json(false, 'La solicitud no contiene datos válidos.', [], 400);
}

if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string) ($entrada['csrf_token'] ?? ''))) {
    responder_json(false, 'La sesión venció o la solicitud no es válida. Recargá la página.', [], 403);
}

$accion = (string) ($entrada['action'] ?? '');
$id = entero_positivo($entrada['id'] ?? 0);
if ($accion === 'delete') {
    if (!$id) {
        responder_json(false, 'El traslado indicado no es válido.', [], 422);
    }

    try {
        $conexion = conectar_bd();
        $conexion->begin_transaction();
        foreach (['paciente_traslado', 'acompañante', 'ruta', 'traslado_elemento'] as $tabla) {
            $consulta = $conexion->prepare("DELETE FROM `$tabla` WHERE ID_Traslado = ?");
            $consulta->bind_param('i', $id);
            $consulta->execute();
            $consulta->close();
        }
        $consulta = $conexion->prepare('DELETE FROM traslado WHERE ID_Traslado = ?');
        $consulta->bind_param('i', $id);
        $consulta->execute();
        $afectados = $consulta->affected_rows;
        $consulta->close();
        if (!$afectados) {
            $conexion->rollback();
            responder_json(false, 'No se encontró el traslado.', [], 404);
        }
        $conexion->commit();
        responder_json(true, 'Traslado eliminado correctamente.');
    } catch (mysqli_sql_exception $error) {
        if (isset($conexion) && $conexion->thread_id) {
            $conexion->rollback();
        }
        error_log('traslados.php delete: ' . $error->getMessage());
        responder_json(false, 'No se pudo eliminar el traslado.', [], 500);
    }
}

if (!in_array($accion, ['create', 'update'], true)) {
    responder_json(false, 'Operación no válida.', [], 400);
}

$pacienteId = entero_positivo($entrada['paciente_id'] ?? 0);
$conductorId = entero_positivo($entrada['conductor_id'] ?? 0);
$ambulanciaId = entero_positivo($entrada['ambulancia_id'] ?? 0);
$origen = trim((string) ($entrada['origen'] ?? ''));
$destino = trim((string) ($entrada['destino'] ?? ''));
$fecha = (string) ($entrada['fecha'] ?? '');
$hora = (string) ($entrada['hora'] ?? '');
$horaEstimada = (string) ($entrada['hora_estimada'] ?? '');
$horaEfectiva = (string) ($entrada['hora_efectiva'] ?? '');
$horaEstimadaDb = $horaEstimada === '' ? '00:00:00' : $horaEstimada;
$horaEfectivaDb = $horaEfectiva === '' ? '00:00:00' : $horaEfectiva;
$acompananteNombre = trim((string) ($entrada['acompanante'] ?? ''));
$estado = trim((string) ($entrada['estado'] ?? ''));
$observaciones = trim((string) ($entrada['observaciones'] ?? ''));
$estadosPermitidos = ['Pendiente', 'En camino', 'Finalizado', 'Cancelado'];

if (!$id && $accion === 'update') {
    responder_json(false, 'El traslado indicado no es válido.', [], 422);
}
if ($origen === '' || $destino === '' || strlen($origen) > 255 || strlen($destino) > 255 ||
    !fecha_valida($fecha) || !hora_valida($hora) ||
    ($horaEstimada !== '' && !hora_valida($horaEstimada)) ||
    ($horaEfectiva !== '' && !hora_valida($horaEfectiva)) ||
    !in_array($estado, $estadosPermitidos, true) || strlen($observaciones) > 255 ||
    $acompananteNombre === '' || strlen($acompananteNombre) > 100) {
    responder_json(false, 'Revisá los campos obligatorios, el formato de fecha y hora, y los valores permitidos.', [], 422);
}

$nuevoPaciente = ($entrada['paciente_id'] ?? '') === 'nuevo';
$nuevoConductor = ($entrada['conductor_id'] ?? '') === 'nuevo';
$nuevaAmbulancia = ($entrada['ambulancia_id'] ?? '') === 'nuevo';
$pacienteNombre = trim((string) ($entrada['paciente_nombre'] ?? ''));
$pacienteApellido = trim((string) ($entrada['paciente_apellido'] ?? ''));
$pacienteCedula = trim((string) ($entrada['paciente_cedula'] ?? ''));
$pacienteNacimiento = (string) ($entrada['paciente_nacimiento'] ?? '');
$pacienteEmail = trim((string) ($entrada['paciente_email'] ?? ''));
$conductorNombre = trim((string) ($entrada['conductor_nombre'] ?? ''));
$conductorDocumento = trim((string) ($entrada['conductor_documento'] ?? ''));
$conductorLicencia = trim((string) ($entrada['conductor_licencia'] ?? ''));
$conductorTelefono = trim((string) ($entrada['conductor_telefono'] ?? ''));
$ambulanciaModelo = trim((string) ($entrada['ambulancia_modelo'] ?? ''));
$ambulanciaMatricula = trim((string) ($entrada['ambulancia_matricula'] ?? ''));
$ambulanciaAnio = entero_positivo($entrada['ambulancia_anio'] ?? 0);
$ambulanciaEstado = trim((string) ($entrada['ambulancia_estado'] ?? 'Disponible'));

if ((!$nuevoPaciente && !$pacienteId) || (!$nuevoConductor && !$conductorId) || (!$nuevaAmbulancia && !$ambulanciaId)) {
    responder_json(false, 'Seleccioná un paciente, un chofer y una ambulancia.', [], 422);
}
if ($nuevoPaciente && ($pacienteNombre === '' || $pacienteApellido === '' || $pacienteCedula === '' ||
    !fecha_valida($pacienteNacimiento) || !filter_var($pacienteEmail, FILTER_VALIDATE_EMAIL) ||
    strlen($pacienteNombre) > 100 || strlen($pacienteApellido) > 100 || strlen($pacienteCedula) > 20 || strlen($pacienteEmail) > 100)) {
    responder_json(false, 'Completá correctamente todos los datos obligatorios del paciente.', [], 422);
}
if ($nuevoConductor && ($conductorNombre === '' || $conductorDocumento === '' || $conductorLicencia === '' ||
    $conductorTelefono === '' || strlen($conductorNombre) > 100 || strlen($conductorDocumento) > 20 ||
    strlen($conductorLicencia) > 50 || strlen($conductorTelefono) > 20)) {
    responder_json(false, 'Completá los datos obligatorios del chofer.', [], 422);
}
if ($nuevaAmbulancia && ($ambulanciaModelo === '' || $ambulanciaMatricula === '' || !$ambulanciaAnio ||
    $ambulanciaAnio < 1900 || $ambulanciaAnio > (int) date('Y') + 1 || $ambulanciaEstado === '' ||
    strlen($ambulanciaModelo) > 100 || strlen($ambulanciaMatricula) > 30 || strlen($ambulanciaEstado) > 50)) {
    responder_json(false, 'Completá correctamente los datos de la ambulancia.', [], 422);
}

try {
    $conexion = conectar_bd();
    $conexion->begin_transaction();

    if ($nuevoPaciente) {
        $consulta = $conexion->prepare('SELECT ID_Paciente FROM paciente WHERE Cedula = ? LIMIT 1');
        $consulta->bind_param('s', $pacienteCedula);
        $consulta->execute();
        $pacienteDuplicado = $consulta->get_result()->num_rows > 0;
        $consulta->close();
        if ($pacienteDuplicado) {
            throw new InvalidArgumentException('La cédula del paciente ya está registrada. Seleccioná el paciente existente.');
        }

        $encuesta = $conexion->query('SELECT ID_Encuesta FROM encuesta ORDER BY ID_Encuesta LIMIT 1')->fetch_assoc();
        if (!$encuesta) {
            throw new RuntimeException('No hay una encuesta registrada para asociar al paciente.');
        }
        $consulta = $conexion->prepare(
            'INSERT INTO paciente (F_Nacimiento, Cedula, Nombre, Apellido, Email, ID_Encuesta)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $encuestaId = (int) $encuesta['ID_Encuesta'];
        $consulta->bind_param('sssssi', $pacienteNacimiento, $pacienteCedula, $pacienteNombre, $pacienteApellido, $pacienteEmail, $encuestaId);
        $consulta->execute();
        $pacienteId = $conexion->insert_id;
        $consulta->close();
    } else {
        verificar_relacion($conexion, 'paciente', 'ID_Paciente', $pacienteId);
    }

    if ($nuevoConductor) {
        $consulta = $conexion->prepare('SELECT ID_Conductor FROM conductor WHERE Documento = ? LIMIT 1');
        $consulta->bind_param('s', $conductorDocumento);
        $consulta->execute();
        $conductorDuplicado = $consulta->get_result()->num_rows > 0;
        $consulta->close();
        if ($conductorDuplicado) {
            throw new InvalidArgumentException('El documento del chofer ya está registrado. Seleccioná el chofer existente.');
        }

        $consulta = $conexion->prepare(
            'INSERT INTO conductor (Nombre, Documento, Licencia, Telefono) VALUES (?, ?, ?, ?)'
        );
        $consulta->bind_param('ssss', $conductorNombre, $conductorDocumento, $conductorLicencia, $conductorTelefono);
        $consulta->execute();
        $conductorId = $conexion->insert_id;
        $consulta->close();
    } else {
        verificar_relacion($conexion, 'conductor', 'ID_Conductor', $conductorId);
    }

    if ($nuevaAmbulancia) {
        $consulta = $conexion->prepare('SELECT ID_ambulancia FROM ambulancia WHERE Matricula = ? LIMIT 1');
        $consulta->bind_param('s', $ambulanciaMatricula);
        $consulta->execute();
        $ambulanciaDuplicada = $consulta->get_result()->num_rows > 0;
        $consulta->close();
        if ($ambulanciaDuplicada) {
            throw new InvalidArgumentException('La matrícula ya está registrada. Seleccioná la ambulancia existente.');
        }

        $consulta = $conexion->prepare(
            'INSERT INTO ambulancia (Estado, Año, Modelo, Matricula) VALUES (?, ?, ?, ?)'
        );
        $consulta->bind_param('siss', $ambulanciaEstado, $ambulanciaAnio, $ambulanciaModelo, $ambulanciaMatricula);
        $consulta->execute();
        $ambulanciaId = $conexion->insert_id;
        $consulta->close();
    } else {
        verificar_relacion($conexion, 'ambulancia', 'ID_ambulancia', $ambulanciaId);
    }

    if ($accion === 'create') {
        $funcionarioId = (int) $_SESSION['funcionario_id'];
        $consulta = $conexion->prepare(
            'INSERT INTO traslado
             (Fecha_solicitud, Hora_Salida, Hora_llegada_estimada, Hora_llegada_efectiva,
              Estado, Observaciones, ID_ambulancia, ID_Conductor, ID_Funcionario)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $consulta->bind_param(
            'ssssssiii',
            $fecha, $hora, $horaEstimadaDb, $horaEfectivaDb, $estado, $observaciones,
            $ambulanciaId, $conductorId, $funcionarioId
        );
        $consulta->execute();
        $id = $conexion->insert_id;
        $consulta->close();
    } else {
        $consulta = $conexion->prepare(
            'UPDATE traslado
             SET Fecha_solicitud = ?, Hora_Salida = ?, Hora_llegada_estimada = ?,
                 Hora_llegada_efectiva = ?, Estado = ?, Observaciones = ?,
                 ID_ambulancia = ?, ID_Conductor = ?
             WHERE ID_Traslado = ?'
        );
        $consulta->bind_param(
            'ssssssiii',
            $fecha, $hora, $horaEstimadaDb, $horaEfectivaDb, $estado, $observaciones,
            $ambulanciaId, $conductorId, $id
        );
        $consulta->execute();
        if (!$consulta->affected_rows && !traslado_existe($conexion, $id)) {
            $consulta->close();
            $conexion->rollback();
            responder_json(false, 'No se encontró el traslado.', [], 404);
        }
        $consulta->close();
        $consulta = $conexion->prepare("DELETE FROM ruta WHERE ID_Traslado = ? AND Tipo IN ('Origen', 'Destino')");
        $consulta->bind_param('i', $id);
        $consulta->execute();
        $consulta->close();
        $consulta = $conexion->prepare('DELETE FROM paciente_traslado WHERE ID_Traslado = ?');
        $consulta->bind_param('i', $id);
        $consulta->execute();
        $consulta->close();
        $consulta = $conexion->prepare('DELETE FROM acompañante WHERE ID_Traslado = ?');
        $consulta->bind_param('i', $id);
        $consulta->execute();
        $consulta->close();
    }

    $consulta = $conexion->prepare('INSERT INTO paciente_traslado (ID_Paciente, ID_Traslado) VALUES (?, ?)');
    $consulta->bind_param('ii', $pacienteId, $id);
    $consulta->execute();
    $consulta->close();

    $documentoAcompanante = '';
    $telefonoAcompanante = '';
    $rolAcompanante = 'Acompañante';
    $consulta = $conexion->prepare(
        'INSERT INTO acompañante (Nombre, Documento, Telefono, Rol, ID_Traslado) VALUES (?, ?, ?, ?, ?)'
    );
    $consulta->bind_param('ssssi', $acompananteNombre, $documentoAcompanante, $telefonoAcompanante, $rolAcompanante, $id);
    $consulta->execute();
    $consulta->close();

    foreach (['Origen' => $origen, 'Destino' => $destino] as $tipoRuta => $descripcionRuta) {
        $localNacional = 'Local';
        $consulta = $conexion->prepare(
            'INSERT INTO ruta (Descripcion, Tipo, Local_Nacional, ID_Traslado) VALUES (?, ?, ?, ?)'
        );
        $consulta->bind_param('sssi', $descripcionRuta, $tipoRuta, $localNacional, $id);
        $consulta->execute();
        $consulta->close();
    }

    $conexion->commit();
    responder_json(true, $accion === 'create' ? 'Traslado registrado correctamente.' : 'Traslado modificado correctamente.');
} catch (Throwable $error) {
    if (isset($conexion) && $conexion->thread_id) {
        $conexion->rollback();
    }
    $mensaje = 'No se pudo guardar el traslado.';
    if ($error instanceof InvalidArgumentException) {
        responder_json(false, $error->getMessage(), [], 422);
    } elseif ($error instanceof mysqli_sql_exception) {
        error_log('traslados.php guardar: ' . $error->getMessage());
        $mensaje = $error->getCode() === 1062
            ? 'Ya existe un registro con los mismos datos únicos (por ejemplo, cédula o matrícula).'
            : 'No se pudo guardar el traslado. Verificá los datos y que la migración esté aplicada.';
    } else {
        error_log('traslados.php guardar: ' . $error->getMessage());
        $mensaje = $error->getMessage();
    }
    responder_json(false, $mensaje, [], 500);
}

function verificar_relacion(mysqli $conexion, string $tabla, string $columna, int $id): void
{
    $consulta = $conexion->prepare("SELECT `$columna` FROM `$tabla` WHERE `$columna` = ?");
    $consulta->bind_param('i', $id);
    $consulta->execute();
    $existe = $consulta->get_result()->num_rows > 0;
    $consulta->close();
    if (!$existe) throw new InvalidArgumentException('Uno de los registros seleccionados ya no existe.');
}

function traslado_existe(mysqli $conexion, int $id): bool
{
    $consulta = $conexion->prepare('SELECT ID_Traslado FROM traslado WHERE ID_Traslado = ?');
    $consulta->bind_param('i', $id);
    $consulta->execute();
    $existe = $consulta->get_result()->num_rows > 0;
    $consulta->close();
    return $existe;
}
