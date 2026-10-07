<?php

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

function validar_datos_traslado(array $entrada, string $accion): array
{
    $valor = static fn(string $clave): string => trim((string) ($entrada[$clave] ?? ''));
    $id = entero_positivo($entrada['id'] ?? 0);
    if ($accion === 'update' && !$id) throw new InvalidArgumentException('El traslado indicado no es válido.');

    $datos = [
        'id' => $id,
        'paciente_id' => entero_positivo($entrada['paciente_id'] ?? 0),
        'conductor_id' => entero_positivo($entrada['conductor_id'] ?? 0),
        'ambulancia_id' => entero_positivo($entrada['ambulancia_id'] ?? 0),
        'origen' => $valor('origen'), 'destino' => $valor('destino'),
        'fecha' => $valor('fecha'), 'hora' => $valor('hora'),
        'hora_estimada' => $valor('hora_estimada'), 'hora_efectiva' => $valor('hora_efectiva'),
        'estado' => $valor('estado'), 'observaciones' => $valor('observaciones'),
        'acompanante' => $valor('acompanante'),
        'nuevo_paciente' => ($entrada['paciente_id'] ?? '') === 'nuevo',
        'nuevo_conductor' => ($entrada['conductor_id'] ?? '') === 'nuevo',
        'nueva_ambulancia' => ($entrada['ambulancia_id'] ?? '') === 'nuevo'
    ];
    $datos['hora_estimada_db'] = $datos['hora_estimada'] === '' ? '00:00:00' : $datos['hora_estimada'];
    $datos['hora_efectiva_db'] = $datos['hora_efectiva'] === '' ? '00:00:00' : $datos['hora_efectiva'];
    $datos += [
        'paciente_nombre' => $valor('paciente_nombre'), 'paciente_apellido' => $valor('paciente_apellido'),
        'paciente_cedula' => $valor('paciente_cedula'), 'paciente_nacimiento' => $valor('paciente_nacimiento'),
        'paciente_email' => $valor('paciente_email'), 'conductor_nombre' => $valor('conductor_nombre'),
        'conductor_documento' => $valor('conductor_documento'), 'conductor_licencia' => $valor('conductor_licencia'),
        'conductor_telefono' => $valor('conductor_telefono'), 'ambulancia_modelo' => $valor('ambulancia_modelo'),
        'ambulancia_matricula' => $valor('ambulancia_matricula'),
        'ambulancia_anio' => entero_positivo($entrada['ambulancia_anio'] ?? 0),
        'ambulancia_estado' => $valor('ambulancia_estado') ?: 'Disponible'
    ];

    if ($datos['origen'] === '' || $datos['destino'] === '' || strlen($datos['origen']) > 255 ||
        strlen($datos['destino']) > 255 || !fecha_valida($datos['fecha']) || !hora_valida($datos['hora']) ||
        ($datos['hora_estimada'] !== '' && !hora_valida($datos['hora_estimada'])) ||
        ($datos['hora_efectiva'] !== '' && !hora_valida($datos['hora_efectiva'])) ||
        !in_array($datos['estado'], ['Pendiente', 'En camino', 'Finalizado', 'Cancelado'], true) ||
        strlen($datos['observaciones']) > 255 || $datos['acompanante'] === '' || strlen($datos['acompanante']) > 100) {
        throw new InvalidArgumentException('Revisá los campos obligatorios, el formato de fecha y hora, y los valores permitidos.');
    }
    if ((!$datos['nuevo_paciente'] && !$datos['paciente_id']) ||
        (!$datos['nuevo_conductor'] && !$datos['conductor_id']) ||
        (!$datos['nueva_ambulancia'] && !$datos['ambulancia_id'])) {
        throw new InvalidArgumentException('Seleccioná un paciente, un chofer y una ambulancia.');
    }
    if ($datos['nuevo_paciente'] && (!$datos['paciente_nombre'] || !$datos['paciente_apellido'] ||
        !$datos['paciente_cedula'] || !fecha_valida($datos['paciente_nacimiento']) ||
        !filter_var($datos['paciente_email'], FILTER_VALIDATE_EMAIL) || strlen($datos['paciente_nombre']) > 100 ||
        strlen($datos['paciente_apellido']) > 100 || strlen($datos['paciente_cedula']) > 20 ||
        strlen($datos['paciente_email']) > 100)) {
        throw new InvalidArgumentException('Completá correctamente todos los datos obligatorios del paciente.');
    }
    if ($datos['nuevo_conductor'] && (!$datos['conductor_nombre'] || !$datos['conductor_documento'] ||
        !$datos['conductor_licencia'] || !$datos['conductor_telefono'] ||
        strlen($datos['conductor_nombre']) > 100 || strlen($datos['conductor_documento']) > 20 ||
        strlen($datos['conductor_licencia']) > 50 || strlen($datos['conductor_telefono']) > 20)) {
        throw new InvalidArgumentException('Completá los datos obligatorios del chofer.');
    }
    if ($datos['nueva_ambulancia'] && (!$datos['ambulancia_modelo'] || !$datos['ambulancia_matricula'] ||
        !$datos['ambulancia_anio'] || $datos['ambulancia_anio'] < 1900 ||
        $datos['ambulancia_anio'] > (int) date('Y') + 1 || !$datos['ambulancia_estado'] ||
        strlen($datos['ambulancia_modelo']) > 100 || strlen($datos['ambulancia_matricula']) > 30 ||
        strlen($datos['ambulancia_estado']) > 50)) {
        throw new InvalidArgumentException('Completá correctamente los datos de la ambulancia.');
    }
    return $datos;
}
