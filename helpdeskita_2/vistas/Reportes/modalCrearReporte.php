<form id="frmcrearReporte" method="POST" onsubmit="return crearReporte()">

<!-- Modal Agregar -->
<div class="modal fade" id="modalcrearReporte" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Crear un nuevo reporte</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

        <input type="text" id="idUsuario" name="idUsuario" value="<?php echo $idUsuario;?>" hidden>

        <div class="row">
          <div class="col-sm-12">
            <label for="areaSolicitante">Área Solicitante</label>
            <div class="input-group mb-2">
              <input type="text" id="areaSolicitanteSearch" class="form-control" placeholder="Buscar área solicitante...">
              <div class="input-group-append">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
              </div>
            </div>
            <select name="areaSolicitante" id="areaSolicitante" class="form-control" size="5" required>
              <option value="Departamento de planeación programación presupuestal">Departamento de planeación programación presupuestal</option>
              <option value="Departamento de gestión tecnológica y vinculación">Departamento de gestión tecnológica y vinculación</option>
              <option value="Departamento de comunicación y difusión">Departamento de comunicación y difusión</option>
              <option value="Departamento de actividades extraescolares">Departamento de actividades extraescolares</option>
              <option value="Departamento de servicios escolares">Departamento de servicios escolares</option>
              <option value="Centro de computo">Centro de computo</option>
              <option value="Mantenimiento de equipos">Mantenimiento de equipos</option>
              <option value="Recursos materiales y servicios">Recursos materiales y servicios</option>
              <option value="Departamento de ciencias básicas">Departamento de ciencias básicas</option>
              <option value="Departamento de sistemas de computación">Departamento de sistemas de computación</option>
              <option value="Departamento de metal-mecánica">Departamento de metal-mecánica</option>
              <option value="Departamento de ciencias de la tierra">Departamento de ciencias de la tierra</option>
              <option value="Departamento de ingeniería química y bioquímica">Departamento de ingeniería química y bioquímica</option>
              <option value="Departamento de ingeniería industrial">Departamento de ingeniería industrial</option>
              <option value="Departamento de ingeniería eléctrica y electrónica">Departamento de ingeniería eléctrica y electrónica</option>
              <option value="Departamento de desarrollo académico">Departamento de desarrollo académico</option>
              <option value="División de desarrollo académico">División de desarrollo académico</option>
              <option value="División de estudios profesionales">División de estudios profesionales</option>
              <option value="División de estudios de posgrado e investigación">División de estudios de posgrado e investigación</option>
              <option value="Departamento de recursos humanos">Departamento de recursos humanos</option>
              <option value="Departamento de recursos financieros">Departamento de recursos financieros</option>
            </select>
          </div>
        </div>

        <div class="row">
          <div class="col-sm-7" style="margin-top: 10px">
            <label for="nombreSolicitante">Nombre del solicitante</label>
            <input type="text" class="form-control" id="nombreSolicitante" name="nombreSolicitante" required>
          </div>

          <div class="col-sm-5" style="margin-top: 10px">
            <label for="fechaElaboracion">Fecha de elaboración</label>
            <input type="date" class="form-control" id="fechaElaboracion" name="fechaElaboracion" required>
            <div class="invalid-feedback">No puedes seleccionar una fecha anterior a hoy.</div>
          </div>
        </div>

        <div class="row">
          <div class="col-sm-6" style="margin-top: 10px">
            <label for="edificio">Edificio o área exterior</label>
            <input type="text" class="form-control" id="edificio" name="edificio" placeholder="Ej. Edificio A, Edificio Principal, etc.">
          </div>

          <div class="col-sm-6" style="margin-top: 10px">
            <label for="cubi">Área o Cubiculo</label>
            <input type="text" class="form-control" id="cubi" name="cubi" placeholder="Ej. Cubículo 101, Oficina 203, etc.">
          </div>
        </div>

        <div class="row">
          <div class="col-sm-10" style="margin-top: 10px">
            <label for="departamento">Departamento</label>
            <select class="form-control" id="departamento" name="departamento" required>
              <option value="">-- Selecciona un departamento --</option>
              <?php
                include "../../clases/conexion.php";
                $con = new conexion();
                $conexion = $con->conectar();

                $sql = "SELECT id_depa, Nombre_depa FROM cat_depa";
                $resultado = mysqli_query($conexion, $sql);

                while($fila = mysqli_fetch_assoc($resultado)) {
                  echo '<option value="'.$fila['id_depa'].'">'.$fila['Nombre_depa'].'</option>';
                }
              ?>
            </select>
          </div>
        </div>

        <div class="row">
          <div class="col-sm-12" style="margin-top: 10px">
            <label for="descripcion">Descripción del servicio solicitado o falla a reparar</label>
            <textarea name="descripcion" id="descripcion" class="form-control" required></textarea>
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <span class="btn btn-secondary" data-dismiss="modal">Cerrar</span>
        <button class="btn btn-primary">Crear</button>
      </div>
    </div>
  </div>
</div>

</form>

<script>
// Función para filtrar las opciones del select de área solicitante
function filtrarAreasSolicitantes() {
    const input = document.getElementById('areaSolicitanteSearch');
    const filter = input.value.toLowerCase();
    const select = document.getElementById('areaSolicitante');
    const options = select.getElementsByTagName('option');
    
    for (let i = 0; i < options.length; i++) {
        options[i].style.display = '';
        options[i].classList.remove('option-highlight');
    }
    
    if (filter) {
        for (let i = 0; i < options.length; i++) {
            const text = options[i].textContent || options[i].innerText;
            if (text.toLowerCase().includes(filter)) {
                options[i].style.display = '';
                const regex = new RegExp(`(${filter})`, 'gi');
                options[i].innerHTML = text.replace(regex, '<span class="option-highlight">$1</span>');
            } else {
                options[i].style.display = 'none';
            }
        }
    } else {
        for (let i = 0; i < options.length; i++) {
            options[i].innerHTML = options[i].textContent;
        }
    }
}

// Agregar evento al campo de búsqueda
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('areaSolicitanteSearch').addEventListener('input', filtrarAreasSolicitantes);
    
    // Agregar evento para seleccionar una opción del select
    document.getElementById('areaSolicitante').addEventListener('change', function() {
        document.getElementById('areaSolicitanteSearch').value = this.options[this.selectedIndex].text;
    });
});

document.addEventListener("DOMContentLoaded", function () {
  const fechaElab = document.getElementById('fechaElaboracion');
  if (!fechaElab) return;

  // obtiene la fecha de hoy en formato YYYY-MM-DD
  function hoyString() {
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const d = String(now.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
  }

  // establece min al abrir la página / modal (por si cambió el día)
  function actualizarMin() {
    const minHoy = hoyString();
    fechaElab.setAttribute('min', minHoy);
  }

  actualizarMin();

  // Función que limpia el campo si la fecha ingresada es anterior a hoy
  function limpiarSiEsPasada() {
    const minHoy = fechaElab.getAttribute('min') || hoyString();
    if (!fechaElab.value) {
      fechaElab.classList.remove('is-invalid');
      return;
    }
    // la comparación de strings funciona porque el formato es YYYY-MM-DD
    if (fechaElab.value < minHoy) {
      // limpia el campo como pediste
      fechaElab.value = '';
      // muestra feedback visual temporalmente
      fechaElab.classList.add('is-invalid');
      // opcional: quitar la clase después de 2 segundos
      setTimeout(() => fechaElab.classList.remove('is-invalid'), 2000);
    } else {
      // fecha válida -> quitar feedback
      fechaElab.classList.remove('is-invalid');
    }
  }

  // Captura selección por datepicker, tipeo manual o pegado
  fechaElab.addEventListener('input', limpiarSiEsPasada);
  fechaElab.addEventListener('change', limpiarSiEsPasada);

  // Si usas Bootstrap modal, actualiza min al abrir modal (por si el día cambió)
  // Requiere jQuery + Bootstrap JS
  if (window.jQuery) {
    $('#modalcrearReporte').on('show.bs.modal', function () {
      actualizarMin();
    });
  }

  // Validación adicional al submit: bloquea envío si la fecha está vacía o inválida
  const form = document.getElementById('frmcrearReporte');
  if (form) {
    form.addEventListener('submit', function (e) {
      // si está vacío o menor que hoy, evitar submit
      const minHoy = fechaElab.getAttribute('min') || hoyString();
      if (!fechaElab.value || fechaElab.value < minHoy) {
        e.preventDefault();
        fechaElab.classList.add('is-invalid');
        alert('Ingrese una fecha válida. No se permiten fechas anteriores a hoy.');
        return false;
      }
      // si tienes otra validación en crearReporte(), esa seguirá funcionando
    });
  }
});
</script>

<style>
.option-highlight {
    background-color: yellow;
    font-weight: bold;
}
</style>