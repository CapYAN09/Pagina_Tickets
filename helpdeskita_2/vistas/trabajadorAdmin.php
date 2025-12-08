<?php 

  include "header.php"; 
  if(isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] == 4){

?>

<!-- loader -->
<div id="loaderPrincipal" class="loadingPrincipal">
  <div class="spinner-border text-info" style="width: 5rem; height: 5rem;" role="status"></div>
</div>

<!-- Contenido de la página -->
<div class="container">
  <div class="card border-0 shadow my-5">
    <div class="card-body p-5">
      <h1 class="fw-light">Administración de Trabajadores</h1>
      <p class="lead">
      

        
        <hr>
        <div id="cargartablapersonal">
          
        </div>
      </p>
    </div>
  </div>
</div>

<?php 
  include "Usuarios/modalAgregar.php";
  include "Reportes/modalTerminarReporte.php";
  include "Usuarios/modalEditarP.php";
  include "Usuarios/modalPersonal.php";
  
  include "footer.php"; 
?>

<script src="../public/js-usuarios/personal.js"></script>
<?php
  }else
    {
    header("location:../index.html");
    }
?>
<script>
  // Evento para desaparecer el loader
  const loadingSpinner = document.getElementById("loaderPrincipal");

  window.addEventListener("load", async function (e) {    
    setTimeout(function(){
      loadingSpinner.classList.add('d-none');
    }, 1000);
  });
</script>

