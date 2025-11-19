<?php 
// Incluir configuración común de sesión
include_once '../config/session_config.php';
iniciarSesionSegura();

error_log("=== INICIO.PHP ===");
error_log("Session ID: " . session_id());
error_log("Usuario en sesión: " . (isset($_SESSION['usuario']) ? $_SESSION['usuario']['nombre'] : 'NO'));

if(isset($_SESSION['usuario']) && in_array($_SESSION['usuario']['rol'], [1, 2, 3, 4, 5])){
    include "header.php"; 
?>

<!-- loader -->
<div id="loaderPrincipal" class="loadingPrincipal">
  <div class="spinner-border text-info" style="width: 5rem; height: 5rem;" role="status"></div>
</div>

<!-- Contenido de la página -->
<div class="container">
  <div class="card border-0 shadow my-5">
    <div class="card-body p-5">
      <h1 class="fw-light">Inicio</h1>
      <hr>
      <p class="h2">Bienvenido <strong><?php echo $_SESSION['usuario']['nombre']; ?>.</strong></p>
      <p>Tipo de usuario: <strong>
        <?php 
          $roles = [
            1 => "Usuario Normal",
            2 => "Administrador", 
            3 => "Auditor",
            4 => "Personal",
            5 => "Super Usuario"
          ];
          echo $roles[$_SESSION['usuario']['rol']] ?? "Desconocido";
        ?>
      </strong></p>
      
      <p>Ubicación: <strong><?php echo $_SESSION['usuario']['ubicacion'] ?? 'No disponible'; ?></strong></p>
      
      <!-- Debug info (quitar en producción) -->
      <div class="mt-4 p-3 bg-light border rounded">
        <small class="text-muted">
          Session ID: <?php echo session_id(); ?><br>
          Session Name: <?php echo session_name(); ?>
        </small>
      </div>
    </div>
  </div>
</div>

<?php 
  include "footer.php"; 
} else {
    error_log("❌ INICIO.PHP - Redirigiendo por falta de sesión");
    header("location:../index.html");
    exit;
}
?>

<script>
  const loadingSpinner = document.getElementById("loaderPrincipal");
  window.addEventListener("load", function() {    
    loadingSpinner.classList.add('d-none');
  });
</script>