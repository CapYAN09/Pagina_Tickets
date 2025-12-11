<form id="frmterminarReporte" method="POST" onsubmit="return validarFormulario()">

<!-- Modal Agregar -->
<div class="modal fade" id="modalterminarReporte" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Terminar un reporte</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        
        <!-- Formulario en modal -->
        <div class="row">
            <div class="col-sm-4 d-none">
                <label for="idReporte"> ID reporte </label>
                <input type="text" class="form-control" id="idReporte" name="idReporte" required>
            </div>
            
            <!-- NUEVO CAMPO: Fecha de elaboración (solo lectura) -->
            
            <div class="col-sm-5">
                <label for="mantenimiento">Mantenimiento</label>
                <select name="mantenimiento" id="mantenimiento" class="form-control" required>
                    <option value="">Seleccionar tipo</option>
                    <option value="1">Interno</option>
                    <option value="2">Externo</option>
                </select>
            </div>
            <div class="col-sm-6">
                <label for="tipoServicio"> Tipo de servicio: </label>
                <select name="tipoServicio" id="tipoServicio" class="form-control" required>
                    <option value="">Seleccionar tipo</option>
                    <option value="Preventivo">Preventivo</option>
                    <option value="Correctivo">Correctivo</option>
                    <option value="Apoyo de area">Apoyo de area</option>
                </select>
            </div>
            <!-- ELIMINADO: Campo asignado manual -->
        </div>

        <div class="row">
            <div class="col-sm-5">
                <label for="fechaRealizacion"> Fecha de realizacion </label>
                <input type="date" class="form-control" id="fechaRealizacion" name="fechaRealizacion" required>
            </div>
            <div class="col-sm-12">
                <label for="trabajoRealizado"> Trabajo Realizado </label>
                <textarea name="trabajoRealizado" id="trabajoRealizado" class="form-control" required></textarea>
            </div>

            <div class="col-sm-12">
                <label for="material"> Material Utilizados </label>
                <textarea name="material" id="material" class="form-control" required></textarea>
            </div>

           
        </div>

        <div class="row">
            <!--<div class="col-sm-5">
                <label for="fechaVerificacion"> Fecha de verificación </label>
                <input type="date" class="form-control" id="fechaVerificado" name="fechaVerificado" required>
            </div>-->
            <div class="col-sm-5">
                <label for="fechaAprobado"> Fecha de aprobación: </label>
                <input type="date" class="form-control" id="fechaAprobado" name="fechaAprobado" required>
            </div>
        </div>

      </div>
      <div class="modal-footer">
        <span class="btn btn-secondary" data-dismiss="modal" id="button_cerrar">Cerrar</span>
        <a class="btn btn-primary" href="#" id="button-terminar_reporte">Terminar reporte</a>
      </div>
    </div>
  </div>
</div>

</form>

<script>
    // Función para cargar la fecha de elaboración cuando se abre el modal
    function cargarFechaElaboracion(idReporte) {
        // Hacer una petición AJAX para obtener la fecha de elaboración
        fetch('../procesos/reportes/obtenerFechaElaboracion.php?id_reporte=' + idReporte)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('fechaElaboracion').value = data.fecha_elaboracion;
                } else {
                    console.error('Error al cargar fecha de elaboración:', data.message);
                    document.getElementById('fechaElaboracion').value = '';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('fechaElaboracion').value = '';
            });
    }

    // Función para validar el formulario antes de enviar
    function validarFormulario() {
        const camposRequeridos = [
            'mantenimiento', 'tipoServicio', 'fechaRealizacion',
            'trabajoRealizado','material' , 'fechaAprobado'
        ];

        let camposVacios = [];

        // Verificar cada campo requerido
        camposRequeridos.forEach(campo => {
            const elemento = document.getElementById(campo);
            if (!elemento.value.trim()) {
                camposVacios.push(campo);
                elemento.classList.add('is-invalid');
            } else {
                elemento.classList.remove('is-invalid');
            }
        });

        // Verificar fechas futuras (SOLO ESTA VALIDACIÓN)
        const fechaRealizacion = new Date(document.getElementById('fechaRealizacion').value);
        const fechaAprobado = new Date(document.getElementById('fechaAprobado').value);
        const hoy = new Date();

        if (fechaRealizacion > hoy) {
            Swal.fire("Error", "La fecha de realización no puede ser futura", "error");
            document.getElementById('fechaRealizacion').classList.add('is-invalid');
            return false;
        }

        if (fechaAprobado > hoy) {
            Swal.fire("Error", "La fecha de aprobación no puede ser futura", "error");
            document.getElementById('fechaAprobado').classList.add('is-invalid');
            return false;
        }

        // Si hay campos vacíos, mostrar error
        if (camposVacios.length > 0) {
            Swal.fire("Campos incompletos", "Por favor complete todos los campos requeridos", "error");
            return false;
        }

        return true;
    }

    // Event listener para el botón de terminar reporte
    document.getElementById("button-terminar_reporte").addEventListener("click", async function (e) {
        e.preventDefault();
        
        if (!validarFormulario()) {
            return;
        }

        const form = document.getElementById("frmterminarReporte");
        const dataForm = new FormData(form);

        Swal.fire({
            title: 'Procesando...',
            text: 'Terminando reporte',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        try {
            const res = await fetch("../procesos/reportes/crud/terminarReporte.php", {        
                method: "POST",
                body: dataForm
            });

            const data = await res.json();
            Swal.close();

            if(data == 1 || data === true) {
                Swal.fire("Operación realizada", "¡Reporte terminado!", "success");
                $('#cargartablareportes').load("Reportes/tablareportesP_Admin.php");
                document.getElementById("button_cerrar").click();
                form.reset();
            } else {
                Swal.fire("Operación no realizada", "Error al terminar el reporte", "error");
            }

        } catch (error) {
            Swal.close();
            Swal.fire("Error", "Error de conexión: " + error.message, "error");
        }
    });

    // Quitar la clase de error cuando el usuario empiece a escribir
    document.querySelectorAll('input, select, textarea').forEach(elemento => {
        elemento.addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });
    });
</script>