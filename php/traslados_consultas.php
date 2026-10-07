<?php

function listar_traslados(mysqli $conexion): array
{
    $resultado = $conexion->query(
        "SELECT t.ID_Traslado AS id, r.origen, r.destino, t.Fecha_solicitud AS fecha,
                TIME_FORMAT(t.Hora_Salida, '%H:%i') AS hora,
                NULLIF(TIME_FORMAT(t.Hora_llegada_estimada, '%H:%i'), '00:00') AS hora_estimada,
                NULLIF(TIME_FORMAT(t.Hora_llegada_efectiva, '%H:%i'), '00:00') AS hora_efectiva,
                t.Estado AS estado, t.Observaciones AS observaciones, t.ID_Conductor AS conductor_id,
                t.ID_ambulancia AS ambulancia_id, p.paciente, p.paciente_id, c.Nombre AS conductor,
                CONCAT(a.Modelo, ' (', a.Matricula, ')') AS ambulancia, ac.acompanante
         FROM traslado t
         LEFT JOIN (
             SELECT ID_Traslado, MAX(CASE WHEN Tipo = 'Origen' THEN Descripcion END) AS origen,
                    MAX(CASE WHEN Tipo = 'Destino' THEN Descripcion END) AS destino
             FROM ruta WHERE Tipo IN ('Origen', 'Destino') GROUP BY ID_Traslado
         ) r ON r.ID_Traslado = t.ID_Traslado
         LEFT JOIN (
             SELECT pt.ID_Traslado, GROUP_CONCAT(CONCAT(p.Nombre, ' ', p.Apellido) SEPARATOR ', ') AS paciente,
                    MIN(p.ID_Paciente) AS paciente_id
             FROM paciente_traslado pt INNER JOIN paciente p ON p.ID_Paciente = pt.ID_Paciente
             GROUP BY pt.ID_Traslado
         ) p ON p.ID_Traslado = t.ID_Traslado
         LEFT JOIN conductor c ON c.ID_Conductor = t.ID_Conductor
         LEFT JOIN ambulancia a ON a.ID_ambulancia = t.ID_ambulancia
         LEFT JOIN (SELECT ID_Traslado, MAX(Nombre) AS acompanante FROM acompañante GROUP BY ID_Traslado)
            ac ON ac.ID_Traslado = t.ID_Traslado
         ORDER BY t.Fecha_solicitud DESC, t.Hora_Salida DESC, t.ID_Traslado DESC"
    );
    return $resultado->fetch_all(MYSQLI_ASSOC);
}

function listar_catalogos(mysqli $conexion): array
{
    return [
        'pacientes' => $conexion->query(
            "SELECT ID_Paciente AS id, CONCAT(Nombre, ' ', Apellido, ' (', Cedula, ')') AS nombre
             FROM paciente ORDER BY Apellido, Nombre"
        )->fetch_all(MYSQLI_ASSOC),
        'conductores' => $conexion->query(
            'SELECT ID_Conductor AS id, Nombre AS nombre FROM conductor ORDER BY Nombre'
        )->fetch_all(MYSQLI_ASSOC),
        'ambulancias' => $conexion->query(
            "SELECT ID_ambulancia AS id, CONCAT(Modelo, ' (', Matricula, ')') AS nombre
             FROM ambulancia ORDER BY Matricula"
        )->fetch_all(MYSQLI_ASSOC)
    ];
}
