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
    $resultado = $conexion->query(
        'SELECT ID_Funcionario, n_usuario, c_electronico, direccion, f_nacimineto, cedula, n_telefono, f_Ingreso, estado 
         FROM funcionario ORDER BY ID_Funcionario DESC'
    );
} catch (mysqli_sql_exception $error) {
    http_response_code(500);
    exit('No se pudo consultar la lista de funcionarios.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Funcionarios - Hospital de Clínicas</title>
    <link rel="stylesheet" href="../css/panel_fun.css">
</head>
<body>

    <header>
        <div class="logo">
            <img src="../img/Logo_Hc_small.png" alt="Logo Hospital de Clínicas">
        </div>
        <a href="../pages/panel_fun.html" class="btn-login">Volver al panel</a>
    </header>

    <main class="contenedor">
        <h2>Listado de Funcionarios</h2>
        <table class="tabla-funcionarios">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Cédula</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($fila['ID_Funcionario']) ?></td>
                        <td><?= htmlspecialchars($fila['n_usuario']) ?></td>
                        <td><?= htmlspecialchars($fila['cedula']) ?></td>
                        <td><?= htmlspecialchars($fila['c_electronico']) ?></td>
                        <td><?= htmlspecialchars($fila['n_telefono']) ?></td>
                        <td><?= htmlspecialchars($fila['estado']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>

    <footer>
        <strong>Hospital de Clínicas</strong>
        <p>Sistema Interno de Gestión</p>
    </footer>

</body>
</html>