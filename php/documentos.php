<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Documentos</title>

<link rel="stylesheet" href="/ProyectoFinal01/documentos.css">

</head>

<body>

    <header>

        <div class="logo-header">

            <img src="../Logo_Hc.png" alt="Logo Hospital" height="50">


        </div>
        
        <a href="../index.html" class="boton-inicio">
            Volver al inicio
        </a>

    </header>

    <main>

        <h1>Gestión de Documentos</h1>

        <form>

            <label>Nombre del documento</label>

            <input type="text" placeholder="Ej: Preparación para estudios">

            <label>Categoría</label>

            <select>

                <option>Radiología</option>
                <option>Cardiología</option>
                <option>Nefrología</option>
                <option>Otro</option>

            </select>

            <label>Seleccionar archivo PDF</label>

            <input type="file">

            <button type="submit">Guardar Documento</button>

        </form>

        <h2>Documentos registrados</h2>

        <div class="documento">

            <h3>Preparación para estudios imagenológicos</h3>

            <p>Categoría: Radiología</p>

            <button>Editar</button>

            <button>Eliminar</button>

        </div>

        <div class="documento">

            <h3>Indicaciones para pacientes con Warfarina</h3>

            <p>Categoría: Cardiología</p>

            <button>Editar</button>

            <button>Eliminar</button>

        </div>

    </main>

</body>

</html>