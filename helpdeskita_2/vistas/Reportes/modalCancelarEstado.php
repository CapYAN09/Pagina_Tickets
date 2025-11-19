<form id="frmCambiarEstadoC" method="POST" onsubmit="return CambiarEstadoC(<?php echo $mostrar['idReporte']; ?>)">

 

<!-- Modal Firmar -->
<div class="modal fade" id="modalCancelarEstado" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Aviso de confirmación</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <input type="text" id="idReporteCEC" name="idReporteCEC" hidden>
      <div class="modal-body">
        <!-- Formulario en modal -->
        <div class="row">
            <div class="col-sm-12">
                <label for="men"> Al hacer click en aceptar, estara cancelando la solicitud de trabajo escribiendo los motivos de dicho 
                </label>
            </div>
            <div class="col-sm-12">
            <label for="cc">Motivo por el cual fue cancelada la solicitud</label>
            <textarea name="cc" id="cc" class="form-control" required></textarea>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="button-cambiar">Aceptar</button>
      </div>
    </div>
  </div>
</div>

</form>

<script>
document.getElementById("button-cambiar").addEventListener("click", async function(){
  const form = document.getElementById("frmCambiarEstadoC");
  const textareaCC = document.getElementById("cc");
  
  // Validar que el campo "cc" no esté vacío
  if (!textareaCC.value.trim()) {
    Swal.fire({
      icon: 'warning',
      title: 'Campo requerido',
      text: 'Debe escribir el motivo de la cancelación antes de continuar',
      confirmButtonColor: '#3085d6'
    });
    
    // Enfocar el campo vacío
    textareaCC.focus();
    return; // Detener la ejecución
  }
  
  const dataform = new FormData(form);
  
  try {
    const res = await fetch("../procesos/reportes/crud/cambiarEstadoC.php", {
      method: "POST", 
      body: dataform
    });
    
    const data = await res.json();
    
    if(data == 1){
      // CERRAR EL MODAL CORRECTAMENTE
      $('#modalCancelarEstado').modal('hide');
      
      // Mostrar mensaje de éxito
      Swal.fire("Operación realizada", "¡La solicitud ha sido cancelada!", "success")
        .then(() => {
          // Recargar la tabla después de que el usuario cierre el SweetAlert
          $('#cargartablareportes').load("Reportes/tablareportesP_Admin.php");
        });
    } else {
      Swal.fire("Operación no realizada", "Error al realizar cambio", "error");
    }
  } catch (error) {
    console.error("Error:", error);
    Swal.fire("Error", "Error de conexión", "error");
  }
});
</script>