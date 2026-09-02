<?php

session_start();

require_once __DIR__ . '/conexion.php';


if (
    !isset(
        $_SESSION['funcionario_id']
    )
) {

    header(
        'Location: ../pages/login.html'
    );

    exit;
}


header(
    'Content-Type: text/html; charset=utf-8'
);


try {

    $conexion =
        conectar_bd();


    $resultado =
        $conexion->query(
            'SELECT
                ID_Funcionario,
                n_usuario,
                c_electronico,
                direccion,
                f_nacimineto,
                cedula,
                n_telefono,
                f_Ingreso,
                estado
             FROM funcionario
             ORDER BY ID_Funcionario DESC'
        );


} catch (
    mysqli_sql_exception $error
) {

    http_response_code(500);

    exit(
        'No se pudo consultar la lista de funcionarios.'
    );

}

?>


<!DOCTYPE html>

<html lang="es">


<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Funcionarios -
        Hospital de Clínicas
    </title>


    <link
        rel="stylesheet"
        href="../css/panel_fun.css"
    >


    <style>

        .tabla-contenedor {

            max-width: 1100px;

            margin: 30px auto;

            background: #fff;

            padding: 20px;

            border-radius: 15px;

            box-shadow: 0 5px 15px #ddd;

            overflow-x: auto;

        }


        table {

            width: 100%;

            border-collapse: collapse;

        }


        th,
        td {

            padding: 12px;

            border-bottom:
                1px solid #e4edf7;

            text-align: left;

        }


        th {

            color: #0b5fa5;

        }


        a.volver {

            display: inline-block;

            margin: 20px;

            color: #0b5fa5;

            text-decoration: none;

            font-weight: bold;

        }

    </style>


</head>


<body>


<header class="encabezado-panel">


    <div class="logo-panel">

        <img
            src="../img/Logo_Hc_small.png"
            alt="Logo Hospital de Clínicas"
        >

    </div>


    <div class="titulo-panel">

        <h1>
            Hospital de Clínicas
        </h1>

        <h2>
            Funcionarios
        </h2>

    </div>


    <div class="perfil-header">

        <div class="perfil-texto">

            <strong>

                <?= htmlspecialchars(
                    $_SESSION['usuario']
                ) ?>

            </strong>


            <span>
                Funcionario
            </span>

        </div>

    </div>


</header>


<main>


    <a
        class="volver"
        href="../pages/panel_fun.php"
    >
        ← Volver al panel
    </a>


    <div class="tabla-contenedor">


        <h2>
            Funcionarios registrados
        </h2>


        <br>


        <table>
            <thead>
                <tr>
                    <th>
                        ID
                    </th>
                    <th>
                        Usuario
                    </th>
                    <th>
                        Correo
                    </th>
                    <th>
                        Cédula
                    </th>
                    <th>
                        Teléfono
                    </th>
                    <th>
                        Ingreso
                    </th>
                    <th>
                        Estado
                    </th>
                </tr>
            </thead>


            <tbody>


                <?php
                while (
                    $fila =
                        $resultado->fetch_assoc()
                ):
                ?>


                <tr>


                    <td>

                        <?= htmlspecialchars(
                            $fila['ID_Funcionario']
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $fila['n_usuario']
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $fila['c_electronico']
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $fila['cedula']
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $fila['n_telefono']
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $fila['f_Ingreso']
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $fila['estado']
                        ) ?>

                    </td>


                </tr>


                <?php
                endwhile;
                ?>


            </tbody>


        </table>


    </div>


</main>


<footer>

    <p>
        Hospital de Clínicas -
        Sistema de gestión para funcionarios
    </p>

</footer>


</body>


</html>


<?php

$conexion->close();

?>