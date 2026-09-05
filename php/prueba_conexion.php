<?php
require_once __DIR__ . '/conexion.php';

$conn = conectar_bd();
$resultado = $conn->query('SELECT n_usuario FROM funcionario');

if ($resultado->num_rows > 0) {
    echo "Conexión exitosa. Funcionarios encontrados:<br><br>";

    while ($fila = $resultado->fetch_assoc()) {
        echo "- " . htmlspecialchars($fila['n_usuario'] ?? 'Sin nombre') . "<br>";
    }
} else {
    echo "La conexión funciona, pero no hay funcionarios registrados.";
}

$conn->close();
?>
