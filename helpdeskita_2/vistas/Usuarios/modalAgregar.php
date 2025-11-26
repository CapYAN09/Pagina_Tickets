<form id="frmAgregarUsuario" method="POST" onsubmit="return agregarNuevoUsuario()">
    
    <!-- Modal Agregar -->
    <div class="modal fade" id="modalAgregarUsuarios" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Agregar nuevo usuario</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Formulario en modal -->
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <label for="paterno">Apellido paterno</label>
                            <input type="text" class="form-control" id="paterno" name="paterno" required>
                        </div>
                        <div class="col-sm-4">
                            <label for="materno">Apellido materno</label>
                            <input type="text" class="form-control" id="materno" name="materno" required>
                        </div>
                        <div class="col-sm-4">
                            <label for="nombre">Nombre/s</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <label for="fechaIn">Fecha de alta</label>
                            <input type="date" class="form-control" id="fechaIn" name="fechaIn" readonly onfocus="this.blur()">
                        </div>
                        <div class="col-sm-4">
                            <label for="telefono">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <label for="correo">Correo</label>
                            <input type="email" class="form-control" id="correo" name="correo" required>
                        </div>
                        <div class="col-sm-4">
                            <label for="usuario">Usuario</label>
                            <input type="text" class="form-control" id="usuario" name="usuario" required>
                        </div>
                        <div class="col-sm-4">
                            <label for="contraseña">Contraseña</label>
                            <input type="password" class="form-control" id="contraseña" name="contraseña" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-12">
                            <label for="idRol">Rol de usuario</label>
                            <select name="idRol" id="idRol" class="form-control" required>
                                <option value="1">Cliente</option>
                                <option value="2">Administrador</option>
                                <option value="3">Auditor</option>
                                <option value="4">Trabajador</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-12">
                            <label for="ubicacion">Departamento</label>
                            <div class="input-group mb-2">
                                <input type="text" id="ubicacionSearch" class="form-control" placeholder="Buscar departamento...">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                            </div>
                            <select name="ubicacion" id="ubicacion" class="form-control" size="5" required>
                                <option value="Departamento de planeación programación presupuestal">Departamento de planeación programación presupuestal</option>
                                <option value="Departamento de gestión tecnológica y vinculación">Departamento de gestión tecnológica y vinculación</option>
                                <option value="Departamento de comunicación y difusión">Departamento de comunicación y difusión</option>
                                <option value="Departamento de actividades extraescolares">Departamento de actividades extraescolares</option>
                                <option value="Departamento de servicios escolares">Departamento de servicios escolares</option>
                                <option value="Centro de computo">Centro de cómputo</option>
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
                    <span class="btn btn-secondary" data-dismiss="modal" id="button_cerrarA">Cerrar</span>
                    <button type="submit" class="btn btn-primary">Agregar</button>
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
    const campoFecha = document.getElementById('fechaIn');
    
    if (campoFecha) {
        campoFecha.value = fechaHoy;
    }
}

// Función para filtrar las opciones del select de ubicación
function filtrarUbicaciones() {
    const input = document.getElementById('ubicacionSearch');
    const filter = input.value.toLowerCase();
    const select = document.getElementById('ubicacion');
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

// Función para agregar nuevo usuario con validación de correo
function agregarNuevoUsuario() {
    const correo = document.getElementById('correo').value.trim();
    const dominioValido = '@aguascalientes.tecnm.mx';
    
    // Validación estricta del correo
    if (!correo.toLowerCase().endsWith(dominioValido.toLowerCase())) {
        Swal.fire({
            icon: 'error',
            title: 'Error de validación',
            text: 'Solo se permiten correos institucionales del dominio @aguascalientes.tecnm.mx',
            confirmButtonText: 'Entendido'
        });
        document.getElementById('correo').focus();
        document.getElementById('correo').select();
        return false;
    }
    
    // Si pasa la validación, hacer el AJAX
    $.ajax({
        type: "POST",
        data: $('#frmAgregarUsuario').serialize(),
        url: "../procesos/usuarios/crud/agregarNuevoUsuario.php",
        success: function(respuesta) {   
            respuesta = respuesta.trim();
            
            if(respuesta == "1") {
                $('#cargartablausuarios').load("Usuarios/tablausuarios.php");
                $('#frmAgregarUsuario')[0].reset();
                // Restablecer la fecha después del reset
                setTimeout(establecerFechaHoy, 100);
                document.getElementById("button_cerrarA").click();
                Swal.fire("Operación realizada", "Agregado con éxito!", "success");
            } else {
                Swal.fire("Operación no realizada", "Error al agregar", "error");
            }
        },
        error: function() {
            Swal.fire("Error", "Error en la conexión", "error");
        }
    });
    
    return false;
}

// Inicializar eventos cuando el documento esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Establecer la fecha de hoy inmediatamente
    establecerFechaHoy();
    
    // Agregar evento al campo de búsqueda
    document.getElementById('ubicacionSearch').addEventListener('input', filtrarUbicaciones);
    
    // Agregar evento para seleccionar una opción del select
    document.getElementById('ubicacion').addEventListener('change', function() {
        document.getElementById('ubicacionSearch').value = this.options[this.selectedIndex].text;
    });
    
    // Establecer la fecha cuando se abra el modal
    const modal = document.getElementById('modalAgregarUsuarios');
    if (modal) {
        modal.addEventListener('show.bs.modal', function() {
            establecerFechaHoy();
        });
        
        // También establecer la fecha cuando el modal esté completamente visible
        modal.addEventListener('shown.bs.modal', function() {
            establecerFechaHoy();
        });
    }
    
    // Establecer la fecha cada 2 segundos por si acaso (backup)
    setInterval(establecerFechaHoy, 2000);
});

// También establecer la fecha cuando la ventana se carga completamente
window.addEventListener('load', establecerFechaHoy);
</script>

<style>
.option-highlight {
    background-color: yellow;
    font-weight: bold;
}
</style>