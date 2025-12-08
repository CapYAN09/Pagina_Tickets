<?php

    class conexion
    {
        public function conectar() 
        {
            $servidor = "172.30.247.185";
            $usuario = "ccomputo";
            $password = "Jarjar0904$";
            $puerto = 3306;
            $db = "b1o04dzhm1guhvmjcrwb";
            $conexion = mysqli_connect($servidor,$usuario,$password,$db);

            return $conexion;
        }
    }
?> 