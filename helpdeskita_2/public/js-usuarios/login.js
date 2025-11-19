function loginusuario() {
    event.preventDefault();
    
    console.log("🔍 === DEBUG DOMINIO CORRECTO ===");
    console.log("URL actual:", window.location.href);
    console.log("Hostname:", window.location.hostname);
    
    var usuario = $('#login').val();
    var password = $('#password').val();
    
    // URL base automática para el dominio correcto
    var baseUrl = 'https://sistematickets.aguascalientes.tecnm.mx';
    console.log("🎯 Usando dominio:", baseUrl);
    
    var url = baseUrl + "/helpdeskita_2/procesos/usuarios/login/loginUsuarios.php";
    console.log("🔗 URL AJAX:", url);
    
    $.ajax({
        type: "POST",
        url: url,
        data: {
            login: usuario,
            password: password
        },
        success: function(respuesta) {
            console.log("✅ Respuesta del servidor:", respuesta.trim());
            
            if(respuesta.trim() === "1") {
                var redirectUrl = baseUrl + "/helpdeskita_2/vistas/inicio.php";
                console.log("🔄 Redirigiendo a:", redirectUrl);
                window.location.href = redirectUrl;
            } else {
                console.log("❌ Login fallido");
                Swal.fire("Error", "Credenciales incorrectas", "error");
            }
        },
        error: function(xhr, status, error) {
            console.error("💥 Error AJAX:");
            console.error("Status:", status);
            console.error("Error:", error);
            console.error("HTTP Status:", xhr.status);
            console.error("Response:", xhr.responseText);
            
            Swal.fire("Error de conexión", "No se pudo conectar con el servidor", "error");
        }
    });
    
    return false;
}