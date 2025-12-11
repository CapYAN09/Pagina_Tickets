<?php 
  include "header.php"; 
  if(isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] == 2){
?>

<!-- loader -->
<div id="loaderPrincipal" class="loadingPrincipal">
  <div class="spinner-border text-info" style="width: 5rem; height: 5rem;" role="status"></div>
</div>

<!-- Contenido de la página -->
<div class="container">
  <div class="card border-0 shadow my-5">
    <div class="card-body p-5">
      <h1 class="fw-light">Servicios Compeltados</h1>
      <p class="lead">
      
        
        <div id="cargartablareportes">
          
        </div>
      </p>
    </div>
  </div>
</div>

<?php 
    include "Reportes/modalRecogerReporte.php";
    include "Reportes/modalTerminarReporte.php"; 
    include "footer.php";
  }else
  {
    header("location:../index.html");
  }
?>

<script src="../public/js-usuarios/reportesT_Admin.js"></script>

<!-- Evento para desaparecer el loader -->
<script>
  const loadingSpinner = document.getElementById("loaderPrincipal");

  window.addEventListener("load", async function (e) {
    setTimeout(function(){
      loadingSpinner.classList.add('d-none');
      // Cargar todos los reportes automáticamente
      $('#cargartablareportes').load("Reportes/tablareportesT_Admin.php");
    }, 1000);
  });

</script>