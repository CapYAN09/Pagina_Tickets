
<form id="frmEditarUsuario" method="POST" onsubmit="return editarUsuario()">

<!-- Modal Agregar -->
<div class="modal fade" id="modalEditarUsuarios" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Editar usuario</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        
      <input type="text" id="idUsuario" name="idUsuario" hidden>
        <!-- Formulario en modal -->
        <div class="row mb-3">
            <div class="col-sm-4">
                <label for="paternou"> Apellido Paterno </label>
                <input type="text" class="form-control" id="paternou" name="paternou" required>
            </div>
            <div class="col-sm-4">
                <label for="maternou"> Apellido Materno </label>
                <input type="text" class="form-control" id="maternou" name="maternou" required>
            </div>
            <div class="col-sm-4">
                <label for="nombreu"> Nombre/s </label>
                <input type="text" class="form-control" id="nombreu" name="nombreu" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-sm-4">
                <label for="fechaInu"> Fecha de alta </label>
                <input type="date" class="form-control" id="fechaInu" name="fechaInu" required readonly>
            </div>
            <div class="col-sm-4">
                <label for="telefonou"> Telefono </label>
                <input type="text" class="form-control" id="telefonou" name="telefonou" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-sm-4">
                <label for="correou"> Correo </label>
                <input type="email" class="form-control" id="correou" name="correou" required>
            </div>
            <div class="col-sm-4">
                <label for="usuariou"> Usuario </label>
                <input type="text" class="form-control" id="usuariou" name="usuariou" required>
            </div>
            <div class="col-sm-4">
                <label for="contraseñau"> Contraseña </label>
                <div class="input-group">
                    <input type="password" class="form-control" id="contraseñau" name="contraseñau" required>
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="togglePasswordu">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-sm-12">
                <label for="idRolu">Rol de usuario</label>
                <select name="idRolu" id="idRolu" class="form-control" required>
                    <option value="1">Cliente</option>
                    <option value="2">Administrador</option>
                    <option value="3">Auditor</option>
                    <option value="4">Trabajador</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-sm-12">
                <label for="ubicacionu">Departamento</label>
                <div class="input-group mb-2">
                    <input type="text" id="ubicacionSearchu" class="form-control" placeholder="Buscar departamento...">
                    <div class="input-group-append">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                </div>
                <select name="ubicacionu" id="ubicacionu" class="form-control" size="5" required>
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

      </div>
      <div class="modal-footer">
        <span class="btn btn-secondary" data-dismiss="modal" id="button_cerrarE">Cerrar</span>
        <button type="submit" class="btn btn-warning">Editar</button>
      </div>
    </div>
  </div>
</div>

</form>

<script>
// Función para establecer la fecha de hoy en el campo de fecha
function establecerFechaHoy() {
    const hoy = new Date();
    const año = hoy.getFullYear();
    let mes = hoy.getMonth() + 1;
    let dia = hoy.getDate();
    
    // Formatear mes y día para que tengan 2 dígitos
    if (mes < 10) mes = '0' + mes;
    if (dia < 10) dia = '0' + dia;
    
    const fechaHoy = `${año}-${mes}-${dia}`;
    document.getElementById('fechaInu').value = fechaHoy;
}

// Función para filtrar las opciones del select de ubicación
function filtrarUbicacionesEditar() {
    const input = document.getElementById('ubicacionSearchu');
    const filter = input.value.toLowerCase();
    const select = document.getElementById('ubicacionu');
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

// Función para editar usuario con validación de correo
function editarUsuario() {
    const correo = document.getElementById('correou').value.trim();
    const dominioValido = '@aguascalientes.tecnm.mx';
    
    // Validación estricta del correo
    if (!correo.toLowerCase().endsWith(dominioValido.toLowerCase())) {
        Swal.fire({
            icon: 'error',
            title: 'Error de validación',
            text: 'Solo se permiten correos institucionales del dominio @aguascalientes.tecnm.mx',
            confirmButtonText: 'Entendido'
        });
        document.getElementById('correou').focus();
        document.getElementById('correou').select();
        return false; // Esto detiene el envío del formulario
    }
    
    // Si pasa la validación, hacer el AJAX
    $.ajax({
        type: "POST",
        data: $('#frmEditarUsuario').serialize(),
        url: "../procesos/usuarios/crud/editarUsuario.php",
        success: function(respuesta) {   
            respuesta = respuesta.trim();
            
            if(respuesta == "1") {
                $('#cargartablausuarios').load("Usuarios/tablausuarios.php");
                $('#frmEditarUsuario')[0].reset();
                document.getElementById("button_cerrarE").click();
                Swal.fire("Operación realizada", "Editado con éxito!", "success");
            } else {
                Swal.fire("Operación no realizada", "Error al editar", "error");
            }
        },
        error: function() {
            Swal.fire("Error", "Error en la conexión", "error");
        }
    });
    
    return false; // Prevenir envío normal del formulario
}

// Inicializar eventos cuando el documento esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Establecer la fecha de hoy
    establecerFechaHoy();
    
    // Agregar evento al campo de búsqueda
    document.getElementById('ubicacionSearchu').addEventListener('input', filtrarUbicacionesEditar);
    
    // Agregar evento para seleccionar una opción del select
    document.getElementById('ubicacionu').addEventListener('change', function() {
        document.getElementById('ubicacionSearchu').value = this.options[this.selectedIndex].text;
    });
    
    // También establecer la fecha cuando se abra el modal
    const modal = document.getElementById('modalEditarUsuarios');
    if (modal) {
        modal.addEventListener('show.bs.modal', function() {
            establecerFechaHoy();
        });
    }
});

// Función para mostrar/ocultar contraseña
function togglePasswordVisibility() {
    const passwordField = document.getElementById('contraseñau');
    const toggleButton = document.getElementById('togglePasswordu');
    const icon = toggleButton.querySelector('i');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Agregar el evento en el DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    // ... código existente ...
    
    // Agregar evento para mostrar/ocultar contraseña
    document.getElementById('togglePasswordu').addEventListener('click', togglePasswordVisibility);
});

</script>

<style>
.option-highlight {
    background-color: yellow;
    font-weight: bold;
}
</style>

