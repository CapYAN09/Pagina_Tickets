<?php
if (isset($_SESSION['usuario'])) {
    $ubicacionUsuario = $_SESSION['usuario']['ubicacion']; // Ej: "Mantenimiento de Equipos"
}
?>

<form id="frmAgregarPersonal" method="POST" onsubmit="return agregarNuevoPersonal()">

<!-- Modal Agregar -->
<div class="modal fade" id="modalAgregarPersonal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Agregar un nuevo trabajador</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">

        <!-- Nombre -->
        <div class="row">
          <div class="col-sm-12">
            <label for="nombre">Nombre</label>
            <input type="text" class="form-control" id="Pnombre" name="Pnombre" required>
          </div>
        </div>

        <!-- Ubicación -->
        <div class="row mt-3">
          <div class="col-sm-12">
            <label for="Pubicacion">Ubicación</label>

            <?php if (isset($ubicacionUsuario)) { ?>
              <select name="Pubicacion" id="Pubicacion" class="form-control" required>
                <option value="<?= htmlspecialchars($ubicacionUsuario) ?>">
                  <?= htmlspecialchars($ubicacionUsuario) ?>
                </option>
              </select>
              <small class="text-muted">Solo puedes agregar personal de tu mismo departamento.</small>
            <?php } else { ?>
              <select name="Pubicacion" id="Pubicacion" class="form-control" required>
                <option value="" disabled selected>Seleccione una opción</option>
                <option value="Centro de computo">Centro de cómputo</option>
                <option value="Mantenimiento de Equipos">Mantenimiento de Equipos</option>
                <option value="Recursos Materiales y Servicios">Recursos Materiales y Servicios</option>
              </select>
            <?php } ?>
          </div>
        </div>

      </div>

      <div class="modal-footer">
        <span class="btn btn-secondary" data-dismiss="modal" id="button_cerrarA">Cerrar</span>
        <button class="btn btn-primary">Agregar</button>
      </div>
    </div>
  </div>
</div>

</form>
