<?php

include "conexion.php";

$conexion = conectar_bd();

$sql = "select
            t.ID_Traslado,
            a.ID_ambulancia,
            a.Matricula,
            a.Modelo,
            t.Estado,
            origen.Descripcion as Origen,
            destino.Descripcion as Destino

        from traslado t

        inner join ambulancia a
        on t.ID_ambulancia = a.ID_ambulancia

        left join ruta origen
        on t.ID_Traslado = origen.ID_Traslado
        and origen.Tipo = 'Origen'

        left join ruta destino
        on t.ID_Traslado = destino.ID_Traslado
        and destino.Tipo = 'Destino'

        order by t.ID_Traslado desc";

$resultado = $conexion->query($sql);

$lista = [];

while ($fila = $resultado->fetch_assoc()) {
    $lista[] = $fila;
}

echo json_encode($lista);

$conexion->close();

?>