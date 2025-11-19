function loginusuario() {
    event.preventDefault();
    
    console.log("🔍 === INICIANDO LOGIN ===");
    
    var usuario = $('#login').val();
    var password = $('#password').val();
    
    console.log("📤 Enviando:", {
        usuario: usuario,
        password: password
    });
    
    $.ajax({
        type: "POST",
        url: "/helpdeskita_2/procesos/usuarios/login/loginUsuarios.php",
        data: {
            login: usuario,
            password: password
        },
        success: function(respuesta) {
            console.log("📥 Respuesta recibida:", respuesta);
            console.log("Tipo de respuesta:", typeof respuesta);
            
            var respuestaTrim = respuesta.trim();
            console.log("Respuesta trimmeada:", respuestaTrim);
            
            if(respuestaTrim === "1") {
                console.log("✅ Login exitoso - Redirigiendo...");
                window.location.href = "/helpdeskita_2/vistas/inicio.php";
            } else {
                console.log("❌ Login fallido - Código:", respuestaTrim);
                Swal.fire("Error", "Usuario o contraseña incorrectos", "error");
            }
        },
        error: function(xhr, status, error) {
            console.error("💥 Error AJAX:", error);
            Swal.fire("Error de conexión", "No se pudo conectar con el servidor", "error");
        }
    });
    
    return false;
}