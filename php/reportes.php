<?php
session_start();
require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['funcionario_id'])) {
    header('Location: ../pages/login.html');
    exit;
}

header('Content-Type: text/html; charset=utf-8');

try {
    $conexion = conectar_bd();
    
    // Consulta que une los traslados con los datos de la ambulancia, conductor y funcionario
    $sql = "SELECT 
                t.ID_Traslado,
                t.Fecha_solicitud,
                t.Hora_Salida,
                t.Hora_llegada_efectiva,
                t.Estado,
                t.Observaciones,
                a.Matricula,
                a.Modelo,
                c.Nombre AS Conductor,
                f.n_usuario AS Funcionario
            FROM traslado t
            LEFT JOIN ambulancia a ON t.ID_ambulancia = a.ID_ambulancia
            LEFT JOIN conductor c ON t.ID_Conductor = c.ID_Conductor
            LEFT JOIN funcionario f ON t.ID_Funcionario = f.ID_Funcionario
            ORDER BY t.Fecha_solicitud DESC, t.Hora_Salida DESC";
            
    $resultado = $conexion->query($sql);
} catch (mysqli_sql_exception $error) {
    http_response_code(500);
    exit('Error al generar el reporte de traslados.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ambulancias y Traslados</title>
    <link rel="stylesheet" href="../css/panel_fun.css">
    <style>
        .contenedor-reportes {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .acciones-reporte {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .btn-imprimir {
            background-color: #28a745;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-imprimir:hover {
            background-color: #218838;
        }
        .tabla-reporte {
            width: 100%;
            border-collapse: collapse;
        }
        .tabla-reporte th, .tabla-reporte td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 14px;
        }
        .tabla-reporte th {
            background-color: #0056b3;
            color: white;
        }
        @media print {
            header, .btn-login, .btn-imprimir, footer {
                display: none !important;
            }
            .contenedor-reportes {
                box-shadow: none;
                margin: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <header>
        <div class="logo">
            <img src="../img/Logo_Hc_small.png" alt="Logo Hospital de Clínicas">
        </div>
        <a href="../pages/panel_fun.html" class="btn-login">Volver al panel</a>
    </header>

    <main class="contenedor-reportes">
        <div class="acciones-reporte">
            <h2>Reporte de Traslados y Ambulancias</h2>
            <button onclick="window.print()" class="btn-imprimir">🖨️ Imprimir / Descargar PDF</button>
        </div>

        <table class="tabla-reporte">
            <thead>
                <tr>
                    <th># ID</th>
                    <th>Fecha</th>
                    <th>Salida</th>
                    <th>Llegada</th>
                    <th>Ambulancia</th>
                    <th>Conductor</th>
                    <th>Funcionario</th>
                    <th>Estado</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while ($fila = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($fila['ID_Traslado']) ?></td>
                            <td><?= htmlspecialchars($fila['Fecha_solicitud']) ?></td>
                            <td><?= htmlspecialchars($fila['Hora_Salida']) ?></td>
                            <td><?= htmlspecialchars($fila['Hora_llegada_efectiva']) ?></td>
                            <td><?= htmlspecialchars(($fila['Modelo'] ?? '') . ' (' . ($fila['Matricula'] ?? 'Sin asignado') . ')') ?></td>
                            <td><?= htmlspecialchars($fila['Conductor'] ?? 'No asignado') ?></td>
                            <td><?= htmlspecialchars($fila['Funcionario'] ?? 'Sistema') ?></td>
                            <td><?= htmlspecialchars($fila['Estado']) ?></td>
                            <td><?= htmlspecialchars($fila['Observaciones']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center;">No hay registros de traslados en la base de datos.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    <footer>
        <strong>Hospital de Clínicas</strong>
        <p>Sistema Interno de Gestión - Reporte Oficial</p>
    </footer>

</body>
</html>