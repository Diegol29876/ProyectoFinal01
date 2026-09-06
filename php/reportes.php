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
    exit('Error al consultar los reportes.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ambulancias - Hospital de Clínicas</title>
    <link rel="stylesheet" href="../css/panel_fun.css">
</head>
<body>

    <header>
        <div class="logo-panel">
            <img src="../img/Logo_Hc_small.png" alt="Logo Hospital de Clínicas">
        </div>
        
        <div class="titulo-panel">
            <h1>Hospital de Clínicas</h1>
            <h2>Reporte de Traslados y Ambulancias</h2>
        </div>

        <div class="perfil-header">
            <a href="../pages/panel_fun.html" style="text-decoration:none; color:#0b5fa5; font-weight:bold;">Volver al Panel</a>
        </div>
    </header>

    <main>
        <section class="panel-principal">
            <div class="acciones-reporte">
                <h2>Historial de Traslados</h2>
                <button onclick="window.print()" class="btn-imprimir">Imprimir / Descargar PDF</button>
            </div>

            <table class="tabla-reporte">
                <thead>
                    <tr>
                        <th>ID</th>
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
                            <td colspan="9">No hay registros de traslados en la base de datos.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

    <footer>
        <p>Hospital de Clínicas - Sistema Interno de Gestión</p>
    </footer>

</body>
</html>