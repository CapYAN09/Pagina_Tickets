$(document).ready(function()
{
    $('#cargartablapersonal').load("Usuarios/tablapersonal.php");
});

function agregarNuevoPersonal()
{
    $.ajax({

        type: "POST",
        data: $('#frmAgregarPersonal').serialize(),
        url: "../procesos/usuarios/crud/agregarNuevoPersonal.php",
        success:function(respuesta)
        {   
            respuesta = respuesta.trim();
            
            if(respuesta == 1)
            {
                $('#cargartablapersonal').load("Usuarios/tablapersonal.php");
                $('#frmAgregarPersonal')[0].reset();
                document.getElementById("button_cerrarA").click();
                Swal.fire("Operación realizada","Agregado con exito! ","success");
            }
            else
            {
                Swal.fire("Operación no realizada","Error al agregar", "error");
            }
        }

    });
    return false;
}
function editarPersonal() {
    // Obtener los datos del formulario
    var idTrabajador = document.getElementById('idTrabajador').value;
    var nombre = document.getElementById('Enombre').value;
    var ubicacion = document.getElementById('Eubicacion').value;

    // Validar que todos los campos estén llenos
    if (nombre === '' || ubicacion === '') {
        Swal.fire("Operación no realizada","Por favor, complete todos los campos", "error");
        return false;
    }

    // Crear objeto FormData para enviar los datos
    var formData = new FormData();
    formData.append('idTrabajador', idTrabajador);
    formData.append('nombre', nombre);
    formData.append('ubicacion', ubicacion);

    // Enviar datos mediante AJAX
    $.ajax({
        url: '../procesos/usuarios/crud/editarPersonal.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(respuesta)
        {   
            respuesta = respuesta.trim();
            
            if(respuesta == 1)
            {
                $('#cargartablapersonal').load("Usuarios/tablapersonal.php");
                document.getElementById("button_cerrarE").click();
                Swal.fire("Operación realizada","Editado con exito! ","success");
            }
            else
            {
                Swal.fire("Operación no realizada","Error al editar", "error");
            }
        }
    });
    return false;
}