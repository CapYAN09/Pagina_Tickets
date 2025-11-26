<?php
// Incluir configuración común de sesión
include_once '../config/session_config.php';

// Iniciar sesión con la MISMA configuración
iniciarSesionSegura();

error_log("=== HEADER.PHP - Verificación ===");
error_log("Session ID: " . session_id());
error_log("Usuario en sesión: " . (isset($_SESSION['usuario']) ? 'SÍ' : 'NO'));

if (!isset($_SESSION['usuario'])) {
    error_log("❌ No hay usuario en sesión - Redirigiendo");
    header("location:../procesos/usuarios/login/salir.php");
    exit();
}

// Verificar que el usuario tenga rol válido
$roles_permitidos = [1, 2, 3, 4, 5];
if (!in_array($_SESSION['usuario']['rol'], $roles_permitidos)) {
    error_log("❌ Rol no permitido: " . $_SESSION['usuario']['rol']);
    header("location:../procesos/usuarios/login/salir.php");
    exit();
}

error_log("✅ Usuario válido: " . $_SESSION['usuario']['nombre']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorkTrack</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="../public/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css"
          integrity="sha384-zCbKRCUGaJDkqS1kPbPd7TveP5iyJE0EjAuZQTgFLD2ylzuqKfdKlfG/eSrtxUkn"
          crossorigin="anonymous">

    <!-- DataTables -->
    <link rel="stylesheet" href="../public/datatable/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../public/datatable/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="../public/datatable/buttons.dataTables.min.css">
    
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/cb918a26fb.js" crossorigin="anonymous"></script>

    <!-- Custom Styles -->
    <link rel="stylesheet" href="../public/css/styles-navbar.css">
    <link rel="stylesheet" href="../public/css/styles-loader.css">
    <link rel="stylesheet" href="../public/css/plantilla.css">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">WorkTrack</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse"
            data-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent"
            aria-expanded="false"
            aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">

            <li class="nav-item active">
                <a class="nav-link" href="inicio.php">Inicio</a>
            </li>

            <!-- Vistas del usuario -->
            <?php if ($_SESSION['usuario']['rol'] == 1) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="reportesP_Usuario.php">Servicios Pendientes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reportesT_Usuario.php">Servicios Terminados</a>
                </li>
            <?php } ?>

            <!-- Vistas del administrador -->
            <?php if ($_SESSION['usuario']['rol'] == 2) { ?>
                
                <li class="nav-item">
                    <a class="nav-link" href="reportesP_Admin.php">Servicios Pendientes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reportesT_Admin.php">Servicios Terminados</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="Resumen.php">Resumen de trabajos</a>
                </li>


            <?php } ?>

            <!-- Vistas del observador -->
            <?php if ($_SESSION['usuario']['rol'] == 3) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="Observar.php">Observar</a>
                </li>
                
            <?php } ?>

                <!-- Vistas del Personal-->
            <?php if ($_SESSION['usuario']['rol'] == 4) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="trabajadorAdmin.php">Solisitudes Asignadas</a>
                </li>
            <?php } ?>
                <!-- Vistas del Super usuario -->
            <?php if ($_SESSION['usuario']['rol'] == 5) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="usuariosAdmin.php">Agregar Usuarios</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="ReportesSA.php">Ver todas las Solicitudes</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="OrdenesSA.php">Ver todas las Ordenes</a>
                </li>
            <?php } ?>

        </ul>

        <div class="my-2 my-lg-0">
            <a class="btn btn-outline-info" href="#">
                <?php echo $_SESSION['usuario']['nombre']; ?> <i class="fas fa-user"></i>
            </a>
            <a class="btn btn-outline-danger" href="../procesos/usuarios/login/salir.php">
                Cerrar sesión <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</nav>

</body>
</html>


<style>
  body {
    background-image: url('https://img.freepik.com/vector-premium/fondo-gris-mejores-fotos-stock-imagenes-hd-fondos-pantalla-gratis-descargar_703129-290.jpg');
    background-size: cover;
    background-repeat: no-repeat;
    background-attachment: fixed;
    background-position: center;
    color: black !important;
    
  }
</style>
