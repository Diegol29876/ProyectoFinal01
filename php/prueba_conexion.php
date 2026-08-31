<?php
require_once __DIR__ . '/conexion.php';

$conn = conectar_bd();

$resultado = $conn->query("SELECT * FROM funcionario");

if (!$resultado) {
    echo "Error en la consulta: " . $conn->error;
    exit;
}

if ($resultado->num_rows > 0) {
    echo "Conexión exitosa. Funcionarios encontrados:<br><br>";

    while ($fila = $resultado->fetch_assoc()) {
        echo "- " . htmlspecialchars($fila['n_usuario'] ?? 'Sin nombre') . "<br>";
    }
} else {
    echo "La conexión funciona, pero la tabla funcionario no tiene registros.";
}

$conn->close();
?>
