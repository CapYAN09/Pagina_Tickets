<?php
     
    $datos= array(
       "id_usuario" => $_POST['idUsuario'],
       "area_solicitante" => $_POST['areaSolicitante'],
       "nombre_solicitante" => $_POST['nombreSolicitante'],
       "id_depa" => $_POST['departamento'],
       "fecha_elaboracion" => $_POST['fechaElaboracion'],
       "descripcion" => $_POST['descripcion'],
       "edificio" => $_POST['edificio'],
       "cubi" => $_POST['cubi'],
       "folio" => generarID()
    );

    // Verificar si los campos opcionales están vacíos
    if (empty($datos['edificio'])) {
        $datos['edificio'] = '';
    }
    
    if (empty($datos['cubi'])) {
        $datos['cubi'] = '';
    }

    include "../../../clases/Reportes.php";
    $Reportes = new Reportes();
    echo $Reportes->crearReporte($datos);

    function generarID (){
      $servidor = "172.30.247.185";
            $usuario = "ccomputo";
            $password = "Jarjar0904$";
            $puerto = 3306;
            $db = "b1o04dzhm1guhvmjcrwb";
      
      $con = mysqli_connect($servidor, $usuario, $password, $db);
      $con->set_charset("utf8");

      $consulta = "SELECT folio FROM `t_reportes` ORDER BY folio DESC LIMIT 1";
      $result = mysqli_query($con, $consulta);

      $data = array();

      while($row = mysqli_fetch_assoc($result)){
        $data[] = $row;
      }

      return intval($data[0]['folio'])+1;
    }
?>