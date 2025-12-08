<?php
 
    include "conexion.php";

    class Reportes extends conexion
    {

        //Funcion para agregar los datos de un reporte a la base de datos
        public function crearReporte($datos)
        {
            $servidor = "172.30.247.185";
            $usuario = "ccomputo";
            $password = "Jarjar0904$";
            $puerto = 3306;
            $db = "b1o04dzhm1guhvmjcrwb";

            $con = mysqli_connect($servidor, $usuario, $password, $db);
            $con->set_charset("utf8");

            // Escapar todos los valores para evitar SQL injection
            $folio = mysqli_real_escape_string($con, $datos['folio']);
            $id_usuario = mysqli_real_escape_string($con, $datos['id_usuario']);
            $area_solicitante = mysqli_real_escape_string($con, $datos['area_solicitante']);
            $id_depa = mysqli_real_escape_string($con, $datos['id_depa']);
            $nombre_solicitante = mysqli_real_escape_string($con, $datos['nombre_solicitante']);
            $fecha_elaboracion = mysqli_real_escape_string($con, $datos['fecha_elaboracion']);
            $descripcion = mysqli_real_escape_string($con, $datos['descripcion']);
            $edificio = mysqli_real_escape_string($con, $datos['edificio']);
            $cubi = mysqli_real_escape_string($con, $datos['cubi']);
            
            $sql = "INSERT INTO t_reportes(folio, id_usuario, area_solicitante, id_depa, nombre_solicitante, fecha_elaboracion, descripcion, edificio, cubi)
        VALUES (
            '".$datos['folio']."',
            '".$datos['id_usuario']."',
            '".$datos['area_solicitante']."',
            '".$datos['id_depa']."',
            '".$datos['nombre_solicitante']."',
            '".$datos['fecha_elaboracion']."',
            '".$datos['descripcion']."',
            '".$datos['edificio']."',
            '".$datos['cubi']."'
        )";

            $result = mysqli_query($con, $sql);

            if ($result) {
            
                $id_reporte = mysqli_insert_id($con);

                // Determinar la tabla destino según el departamento
                $tablaDestino = "";
                $campoAuto = "";
                $prefijo = "";

                switch ($id_depa) {
                    case 1:
                        $tablaDestino = "cat_cc";
                        $campoAuto = "id_reporte_CC";
                        $prefijo = "CComputo-";
                        break;
                    case 2:
                        $tablaDestino = "cat_me";
                        $campoAuto = "id_reporte_ME";
                        $prefijo = "MantenimientoE-";
                        break;
                    case 3:
                        $tablaDestino = "cat_rms";
                        $campoAuto = "id_reporte_RMS";
                        $prefijo = "RMateriales-";
                        break;
                }

                // Si se determinó una tabla destino, insertar el nuevo ID
                if ($tablaDestino != "") {

                    // Obtener el último código generado en esa tabla
                    $consultaUltimo = "SELECT New_ID FROM $tablaDestino ORDER BY $campoAuto DESC LIMIT 1";
                    $resUltimo = mysqli_query($con, $consultaUltimo);

                    $nuevoNumero = 1;
                    if ($resUltimo && mysqli_num_rows($resUltimo) > 0) {
                        $row = mysqli_fetch_assoc($resUltimo);
                        $ultimo = intval(preg_replace('/[^0-9]/', '', $row['New_ID']));
                        $nuevoNumero = $ultimo + 1;
                    }

                    // Generar el nuevo código con formato (por ejemplo: CComputo-0001)
                    $newID = $prefijo . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);

                    // Insertar el nuevo ID en la tabla destino
                    $sqlInsert = "INSERT INTO $tablaDestino (id_reporte, New_ID) VALUES ($id_reporte, '$newID')";
                    mysqli_query($con, $sqlInsert);
                }

                return "1";
            } else {
                // Para debug: mostrar el error
                return "Error: " . mysqli_error($con);
            }
        }




        //Funcion para proporcionar el ID del usuario que inció sesión
        public function obteneridUsuario($idUsuario)
        {
            $conexion = Conexion::conectar();
            $sql = "SELECT
                        usuarios.id_usuario AS idUsuario
                    FROM  t_usuarios AS usuarios
                         /*INNER JOIN
                        t_reportes AS reportes ON usuarios.id_usuario = reportes.id_usuario*/
                    WHERE usuarios.id_usuario = '$idUsuario'";
            $respuesta = mysqli_query($conexion, $sql);
            $idusuario = mysqli_fetch_array($respuesta)['idUsuario'];
            return $idusuario;
        }


        



        //funcion para extraer y usar el id de usuario en la funcion obteneridUsuario
        public function obtenerDatosUsuario($idUsuario)
        {
            $conexion = Conexion::conectar();
            $sql = "SELECT DISTINCT
                            usuarios.id_usuario AS idUsuario
                    FROM
                            t_usuarios AS usuarios
                        /*INNER JOIN
                            t_reportes AS reportes ON usuarios.id_usuario = reportes.id_usuario*/
                        WHERE usuarios.id_usuario = '$idUsuario'";

            $respuesta = mysqli_query($conexion, $sql);
            $usuario = mysqli_fetch_array($respuesta);

            $datos = array (
                'idUsuario' => $usuario['idUsuario']
            );
            return $datos;
        }

public function FirmarReporte($datos)
{
    $conexion = Conexion::conectar();
    
    // Obtener la fecha actual de la computadora en formato YYYY-MM-DD
    $fecha_actual = date('Y-m-d');
    
    $sql = "UPDATE t_reportes_finalizados
            SET firma_verificacion = 2,
                fecha_verificado = '".mysqli_real_escape_string($conexion, $fecha_actual)."'
            WHERE id_reporte = '".$datos['idReporte']."' ";
    
    $resultado = mysqli_query($conexion, $sql);
    
    if($resultado)
    {
        return 1;
    }
    else{
        return 0;
    }
}
        


    }


?>
