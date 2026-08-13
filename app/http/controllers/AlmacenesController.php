<?php

require_once "utils/lib/exel/vendor/autoload.php";

class AlmacenesController extends Controller
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = (new Conexion())->getConexion();
    }

    public function index()
    {
        $respuesta = ["res" => false];
        $sql = "SELECT * FROM almacenes";
        $result = $this->conexion->query($sql);
        foreach ($result as $item) {
            $row[] = $item;
        }
        $respuesta["res"] = true;
        $respuesta["data"] = $row;

        return json_encode($respuesta);
    }
}
