<form id="frmEditarPersonal" onsubmit="return editarPersonal()">
<!-- Modal Editar -->
<div class="modal fade" id="modalEditarPersonal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Editar Personal</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        
      <input type="text" id="idTrabajador" name="idTrabajador" hidden>
        <!-- Formulario en modal -->
        <div class="modal-body">

        <!-- Formulario reducido -->
        <div class="row">
          <div class="col-sm-12">
            <label for="nombre">Nombre</label>
            <input type="text" class="form-control" id="Enombre" name="Enombre" required>
          </div>
        </div>

        <div class="row mt-3">
            <div class="col-sm-12">
                <label for="nombre">Ubicacion</label>
                <select name="Eubicacion" id="Eubicacion" class="form-control custom-select styled-select" required>
                <option value="" disabled selected>Seleccione una opción</option>
                <option value="Centro de computo">Centro de cómputo</option>
                <option value="Mantenimiento">Mantenimiento de Equipos</option>
                <option value="Materiales">Recursos Materiales y Servicios</option>
                </select>
            </div>
        </div>

      </div>
      </div>
      <div class="modal-footer">
        <span class="btn btn-secondary" data-dismiss="modal" id="button_cerrarE">Cerrar</span>
        <button type="submit" class="btn btn-warning">Editar</button>
      </div>
    </div>
  </div>
</div>
</form>