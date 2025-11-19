function loginusuario() {
    event.preventDefault();
    
    console.log("🔍 === LOGIN CON REDIRECCIÓN MEJORADA ===");
    
    var usuario = $('#login').val();
    var password = $('#password').val();
    
    console.log("Datos:", {usuario: usuario, password: password});
    
    var url = "/helpdeskita_2/procesos/usuarios/login/loginUsuarios.php";
    
    // Mostrar loading
    Swal.fire({
        title: 'Iniciando sesión...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        type: "POST",
        url: url,
        data: {
            login: usuario,
            password: password
        },
        success: function(respuesta) {
            console.log("✅ Respuesta servidor:", respuesta);
            
            var respuestaTrim = respuesta.trim();
            console.log("Respuesta trimmeada:", respuestaTrim);
            
            Swal.close();
            
            if(respuestaTrim === "1") {
                console.log("🎉 Login exitoso - Redirigiendo...");
                
                // Esperar un momento para asegurar que la sesión se guarde
                setTimeout(function() {
                    // Verificar que podemos acceder a la página de inicio
                    $.get("/helpdeskita_2/vistas/inicio.php")
                        .done(function() {
                            console.log("✅ Página inicio accesible - Redirigiendo");
                            window.location.href = "/helpdeskita_2/vistas/inicio.php";
                        })
                        .fail(function(xhr, status, error) {
                            console.error("❌ Error al acceder a inicio:", error);
                            console.log("Status:", xhr.status);
                            console.log("Response:", xhr.responseText);
                            
                            // Intentar redirección directa de todas formas
                            Swal.fire({
                                icon: 'warning',
                                title: 'Redirección manual necesaria',
                                text: 'Por favor haz clic en el botón para continuar',
                                confirmButtonText: 'Ir al Inicio'
                            }).then((result) => {
                                window.location.href = "/helpdeskita_2/vistas/inicio.php";
                            });
                        });
                }, 500);
                
            } else {
                console.log("❌ Login fallido - Código:", respuestaTrim);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Usuario o contraseña incorrectos'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("💥 Error AJAX:", error);
            console.error("Status:", status);
            console.error("HTTP Status:", xhr.status);
            
            Swal.close();
            
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor: ' + error
            });
        }
    });
    
    return false;
}