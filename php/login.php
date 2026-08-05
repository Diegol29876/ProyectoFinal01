<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Hospital de Clínicas</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>

    <header>
        <div class="logo">
            <img src="../Logo_Hc.png" alt="Logo Hospital de Clínicas">
        </div>
        
        <a href="../index.html" class="btn-login">Volver al inicio</a>
    </header>

    <main>
        <div class="contenedor-login">

            <div class="bienvenida">
                <h2>Iniciar Sesión</h2>
                <p>Ingresá tus datos para continuar</p>
            </div>

            <form class="formulario">
                <label>Cedula</label>
                <input type="text" id="cedula" name="cedula" maxlength="8" inputmode="numeric" pattern="[0-9]*" placeholder="Ingresá tu Cedula">

                <label>Contraseña</label>
                <input type="password" id="password" name="password" placeholder="Ingresá tu contraseña">

                <button type="submit">Iniciar sesión</button>
            </form>

        </div>
    </main>

    <footer>
        <strong>Tu información está protegida</strong>
        <p>Sitio oficial del Hospital de Clínicas</p>
    </footer>

    <script src="../login.js"></script>
</body>

</html>