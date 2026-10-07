<?php
require_once __DIR__ . '/traslados_validaciones.php';

function ejecutar_sql(mysqli $conexion, string $sql, string $tipos = '', array $parametros = []): mysqli_stmt
{
    $consulta = $conexion->prepare($sql);
    if ($tipos !== '') {
        $argumentos = [$tipos];
        foreach ($parametros as &$parametro) $argumentos[] = &$parametro;
        call_user_func_array([$consulta, 'bind_param'], $argumentos);
    }
    $consulta->execute();
    return $consulta;
}

function insertar_id(mysqli $conexion, string $sql, string $tipos, array $parametros): int
{
    $consulta = ejecutar_sql($conexion, $sql, $tipos, $parametros);
    $id = $conexion->insert_id;
    $consulta->close();
    return $id;
}

function existe_registro(mysqli $conexion, string $tabla, string $columna, int $id): bool
{
    $consulta = ejecutar_sql($conexion, "SELECT `$columna` FROM `$tabla` WHERE `$columna` = ?", 'i', [$id]);
    $existe = $consulta->get_result()->num_rows > 0;
    $consulta->close();
    return $existe;
}

function verificar_relacion(mysqli $conexion, string $tabla, string $columna, int $id): void
{
    if (!existe_registro($conexion, $tabla, $columna, $id)) {
        throw new InvalidArgumentException('Uno de los registros seleccionados ya no existe.');
    }
}

function verificar_unico(mysqli $conexion, string $tabla, string $columna, string $valor, string $mensaje): void
{
    $consulta = ejecutar_sql($conexion, "SELECT `$columna` FROM `$tabla` WHERE `$columna` = ? LIMIT 1", 's', [$valor]);
    $duplicado = $consulta->get_result()->num_rows > 0;
    $consulta->close();
    if ($duplicado) throw new InvalidArgumentException($mensaje);
}

function resolver_paciente(mysqli $conexion, array $datos): int
{
    if (!$datos['nuevo_paciente']) {
        verificar_relacion($conexion, 'paciente', 'ID_Paciente', $datos['paciente_id']);
        return $datos['paciente_id'];
    }
    verificar_unico($conexion, 'paciente', 'Cedula', $datos['paciente_cedula'],
        'La cédula del paciente ya está registrada. Seleccioná el paciente existente.');
    $encuesta = $conexion->query('SELECT ID_Encuesta FROM encuesta ORDER BY ID_Encuesta LIMIT 1')->fetch_assoc();
    if (!$encuesta) throw new RuntimeException('No hay una encuesta registrada para asociar al paciente.');
    return insertar_id($conexion,
        'INSERT INTO paciente (F_Nacimiento, Cedula, Nombre, Apellido, Email, ID_Encuesta) VALUES (?, ?, ?, ?, ?, ?)',
        'sssssi', [$datos['paciente_nacimiento'], $datos['paciente_cedula'], $datos['paciente_nombre'],
            $datos['paciente_apellido'], $datos['paciente_email'], (int) $encuesta['ID_Encuesta']]
    );
}

function resolver_conductor(mysqli $conexion, array $datos): int
{
    if (!$datos['nuevo_conductor']) {
        verificar_relacion($conexion, 'conductor', 'ID_Conductor', $datos['conductor_id']);
        return $datos['conductor_id'];
    }
    verificar_unico($conexion, 'conductor', 'Documento', $datos['conductor_documento'],
        'El documento del chofer ya está registrado. Seleccioná el chofer existente.');
    return insertar_id($conexion, 'INSERT INTO conductor (Nombre, Documento, Licencia, Telefono) VALUES (?, ?, ?, ?)',
        'ssss', [$datos['conductor_nombre'], $datos['conductor_documento'], $datos['conductor_licencia'], $datos['conductor_telefono']]);
}

function resolver_ambulancia(mysqli $conexion, array $datos): int
{
    if (!$datos['nueva_ambulancia']) {
        verificar_relacion($conexion, 'ambulancia', 'ID_ambulancia', $datos['ambulancia_id']);
        return $datos['ambulancia_id'];
    }
    verificar_unico($conexion, 'ambulancia', 'Matricula', $datos['ambulancia_matricula'],
        'La matrícula ya está registrada. Seleccioná la ambulancia existente.');
    return insertar_id($conexion, 'INSERT INTO ambulancia (Estado, Año, Modelo, Matricula) VALUES (?, ?, ?, ?)',
        'siss', [$datos['ambulancia_estado'], $datos['ambulancia_anio'], $datos['ambulancia_modelo'], $datos['ambulancia_matricula']]);
}

function actualizar_traslado(mysqli $conexion, array $datos): void
{
    $consulta = ejecutar_sql($conexion,
        'UPDATE traslado SET Fecha_solicitud = ?, Hora_Salida = ?, Hora_llegada_estimada = ?,
         Hora_llegada_efectiva = ?, Estado = ?, Observaciones = ?, ID_ambulancia = ?, ID_Conductor = ?
         WHERE ID_Traslado = ?',
        'ssssssiii', [$datos['fecha'], $datos['hora'], $datos['hora_estimada_db'], $datos['hora_efectiva_db'],
            $datos['estado'], $datos['observaciones'], $datos['ambulancia_id'], $datos['conductor_id'], $datos['id']]
    );
    $actualizado = $consulta->affected_rows > 0;
    $consulta->close();
    if (!$actualizado && !existe_registro($conexion, 'traslado', 'ID_Traslado', $datos['id'])) {
        throw new OutOfBoundsException('No se encontró el traslado.');
    }
    foreach (["DELETE FROM ruta WHERE ID_Traslado = ? AND Tipo IN ('Origen', 'Destino')",
        'DELETE FROM paciente_traslado WHERE ID_Traslado = ?', 'DELETE FROM acompañante WHERE ID_Traslado = ?'] as $sql) {
        ejecutar_sql($conexion, $sql, 'i', [$datos['id']])->close();
    }
}

function crear_relaciones_traslado(mysqli $conexion, array $datos): void
{
    ejecutar_sql($conexion, 'INSERT INTO paciente_traslado (ID_Paciente, ID_Traslado) VALUES (?, ?)', 'ii',
        [$datos['paciente_id'], $datos['id']])->close();
    ejecutar_sql($conexion, 'INSERT INTO acompañante (Nombre, Documento, Telefono, Rol, ID_Traslado) VALUES (?, ?, ?, ?, ?)',
        'ssssi', [$datos['acompanante'], '', '', 'Acompañante', $datos['id']])->close();
    foreach (['Origen' => $datos['origen'], 'Destino' => $datos['destino']] as $tipo => $descripcion) {
        ejecutar_sql($conexion, 'INSERT INTO ruta (Descripcion, Tipo, Local_Nacional, ID_Traslado) VALUES (?, ?, ?, ?)',
            'sssi', [$descripcion, $tipo, 'Local', $datos['id']])->close();
    }
}

function eliminar_traslado(mysqli $conexion, int $id): bool
{
    $conexion->begin_transaction();
    foreach (['paciente_traslado', 'acompañante', 'ruta', 'traslado_elemento'] as $tabla) {
        ejecutar_sql($conexion, "DELETE FROM `$tabla` WHERE ID_Traslado = ?", 'i', [$id])->close();
    }
    $consulta = ejecutar_sql($conexion, 'DELETE FROM traslado WHERE ID_Traslado = ?', 'i', [$id]);
    $eliminado = $consulta->affected_rows > 0;
    $consulta->close();
    if (!$eliminado) {
        $conexion->rollback();
        return false;
    }
    $conexion->commit();
    return true;
}

function guardar_traslado(mysqli $conexion, array $entrada, string $accion, int $funcionarioId): void
{
    $datos = validar_datos_traslado($entrada, $accion);
    $conexion->begin_transaction();
    try {
        $datos['paciente_id'] = resolver_paciente($conexion, $datos);
        $datos['conductor_id'] = resolver_conductor($conexion, $datos);
        $datos['ambulancia_id'] = resolver_ambulancia($conexion, $datos);
        if ($accion === 'create') {
            $datos['id'] = insertar_id($conexion,
                'INSERT INTO traslado (Fecha_solicitud, Hora_Salida, Hora_llegada_estimada, Hora_llegada_efectiva,
                 Estado, Observaciones, ID_ambulancia, ID_Conductor, ID_Funcionario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                'ssssssiii', [$datos['fecha'], $datos['hora'], $datos['hora_estimada_db'], $datos['hora_efectiva_db'],
                    $datos['estado'], $datos['observaciones'], $datos['ambulancia_id'], $datos['conductor_id'], $funcionarioId]
            );
        } else {
            actualizar_traslado($conexion, $datos);
        }
        crear_relaciones_traslado($conexion, $datos);
        $conexion->commit();
    } catch (Throwable $error) {
        $conexion->rollback();
        throw $error;
    }
}
