// login.js - VERSIÓN DEPURADA
console.log("🔧 login.js cargado correctamente");
 
function loginusuario() {
    console.log("🎯 FUNCIÓN loginusuario INICIADA");
    
    // Prevenir el comportamiento por defecto del formulario
    if (event) {
        event.preventDefault();
    }
    
    // Verificar que jQuery esté funcionando
    if (typeof jQuery === 'undefined') {
        console.error("❌ jQuery no está disponible");
        alert("Error: jQuery no está cargado correctamente");
        return false;
    }
    
    var usuario = $('#login').val();
    var password = $('#password').val();
    
    console.log("📤 Datos a enviar:", {
        usuario: usuario,
        password: password
    });
    
    // Mostrar loading básico si SweetAlert no funciona
    if (typeof Swal === 'undefined') {
        console.log("⚠️ SweetAlert no disponible, usando alert normal");
        alert("Verificando credenciales...");
    } else {
        Swal.fire({
            title: 'Verificando...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }
    
    // Usar jQuery AJAX
    $.ajax({
        type: "POST",
        url: "/helpdeskita_2/procesos/usuarios/login/loginUsuarios.php",
        data: {
            login: usuario,
            password: password
        },
        success: function(respuesta) {
            console.log("✅ Respuesta AJAX recibida:", respuesta);
            
            // Cerrar SweetAlert si está disponible
            if (typeof Swal !== 'undefined') {
                Swal.close();
            }
            
            var respuestaTrim = respuesta.trim();
            console.log("Respuesta trimmeada:", respuestaTrim);
            
            if(respuestaTrim === "1") {
                console.log("🎉 Login exitoso - Redirigiendo a inicio.php");
                window.location.href = "/helpdeskita_2/vistas/inicio.php";
            } else {
                console.log("❌ Login fallido - Código:", respuestaTrim);
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Usuario o contraseña incorrectos'
                    });
                } else {
                    alert("Error: Usuario o contraseña incorrectos");
                }
            }
        },
        error: function(xhr, status, error) {
            console.error("💥 Error en AJAX:");
            console.error("Status:", status);
            console.error("Error:", error);
            console.error("ReadyState:", xhr.readyState);
            console.error("Status HTTP:", xhr.status);
            
            // Cerrar SweetAlert si está disponible
            if (typeof Swal !== 'undefined') {
                Swal.close();
            }
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor'
                });
            } else {
                alert("Error de conexión: " + error);
            }
        }
    });
    
    // Prevenir el envío normal del formulario
    return false;
}

console.log("🔧 Función loginusuario definida correctamente");