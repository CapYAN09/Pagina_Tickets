function loginusuario() {
    event.preventDefault();
    
    console.log("🔍 === DEBUG PROXY REVERSO ===");
    console.log("Hostname:", window.location.hostname);
    console.log("URL completa:", window.location.href);
    
    var usuario = $('#login').val();
    var password = $('#password').val();
    
    // URL relativa - funcionará en ambos entornos
    var url = "/helpdeskita_2/procesos/usuarios/login/loginUsuarios.php";
    console.log("🔗 URL AJAX (relativa):", url);
    
    $.ajax({
        type: "POST",
        url: url,
        data: {
            login: usuario,
            password: password
        },
        success: function(respuesta) {
            console.log("✅ Respuesta:", respuesta.trim());
            
            if(respuesta.trim() === "1") {
                console.log("🎉 Login exitoso - Redirigiendo...");
                // Usar URL relativa para la redirección también
                window.location.href = "/helpdeskita_2/vistas/inicio.php";
            } else {
                console.log("❌ Login fallido - Respuesta:", respuesta);
                Swal.fire("Error", "Credenciales incorrectas", "error");
            }
        },
        error: function(xhr, status, error) {
            console.error("💥 Error AJAX:");
            console.error("Status:", status);
            console.error("Error:", error);
            console.error("HTTP Status:", xhr.status);
            
            Swal.fire("Error de conexión", 
                "No se pudo conectar con el servidor. Error: " + error, 
                "error");
        }
    });
    
    return false;
}