<?php

class UnidadesDerivadasController extends Controller
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = (new Conexion())->getConexion();
    }

    /**
     * Lista todas las unidades derivadas activas de la empresa actual.
     */
    public function listar()
    {
        $respuesta = ["res" => false, "data" => []];
        $idEmpresa = $_SESSION['id_empresa'] ?? 0;

        $sql = "SELECT id_unidad, nombre, descripcion
                FROM unidades_derivadas
                WHERE id_empresa = $idEmpresa AND estado = '1'
                ORDER BY nombre ASC";

        $result = $this->conexion->query($sql);
        $lista = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $lista[] = $row;
            }
        }
        $respuesta["res"] = true;
        $respuesta["data"] = $lista;
        return json_encode($respuesta);
    }

    /**
     * Crea una nueva unidad derivada.
     */
    public function agregar()
    {
        $respuesta = ["res" => false];
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $idEmpresa = (int)($_SESSION['id_empresa'] ?? 0);

        if ($nombre === '' || $idEmpresa <= 0) {
            $respuesta["error"] = "Nombre requerido";
            return json_encode($respuesta);
        }

        $nombreEsc = $this->conexion->real_escape_string($nombre);
        $descEsc = $this->conexion->real_escape_string($descripcion);

        // Validar no duplicar nombre en la misma empresa
        $check = $this->conexion->query("SELECT id_unidad FROM unidades_derivadas
                                          WHERE nombre = '$nombreEsc' AND id_empresa = $idEmpresa");
        if ($check && $check->num_rows > 0) {
            $respuesta["error"] = "Ya existe una unidad con ese nombre";
            return json_encode($respuesta);
        }

        $sql = "INSERT INTO unidades_derivadas SET
                nombre = '$nombreEsc',
                descripcion = '$descEsc',
                estado = '1',
                id_empresa = $idEmpresa";

        if ($this->conexion->query($sql)) {
            $respuesta["res"] = true;
            $respuesta["id_unidad"] = $this->conexion->insert_id;
            $respuesta["nombre"] = $nombre;
        } else {
            $respuesta["error"] = $this->conexion->error;
        }
        return json_encode($respuesta);
    }

    /**
     * Actualiza una unidad derivada existente.
     */
    public function actualizar()
    {
        $respuesta = ["res" => false];
        $idUnidad = (int)($_POST['id_unidad'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $idEmpresa = (int)($_SESSION['id_empresa'] ?? 0);

        if ($idUnidad <= 0 || $nombre === '') {
            $respuesta["error"] = "Datos inválidos";
            return json_encode($respuesta);
        }

        $nombreEsc = $this->conexion->real_escape_string($nombre);
        $descEsc = $this->conexion->real_escape_string($descripcion);

        $sql = "UPDATE unidades_derivadas SET
                nombre = '$nombreEsc',
                descripcion = '$descEsc'
                WHERE id_unidad = $idUnidad AND id_empresa = $idEmpresa";

        if ($this->conexion->query($sql)) {
            $respuesta["res"] = true;
        } else {
            $respuesta["error"] = $this->conexion->error;
        }
        return json_encode($respuesta);
    }

    /**
     * Soft-delete (estado = 0). No borramos para no romper FK en productos.
     */
    public function eliminar()
    {
        $respuesta = ["res" => false];
        $idUnidad = (int)($_POST['id_unidad'] ?? 0);
        $idEmpresa = (int)($_SESSION['id_empresa'] ?? 0);

        if ($idUnidad <= 0) {
            $respuesta["error"] = "ID inválido";
            return json_encode($respuesta);
        }

        // Verificar si algún producto está usando esta unidad
        $check = $this->conexion->query("SELECT COUNT(*) AS n FROM productos
                                          WHERE id_unidad_derivada = $idUnidad");
        $enUso = (int)($check->fetch_assoc()['n'] ?? 0);

        if ($enUso > 0) {
            $respuesta["error"] = "No se puede eliminar: $enUso producto(s) usan esta unidad";
            return json_encode($respuesta);
        }

        $sql = "UPDATE unidades_derivadas SET estado = '0'
                WHERE id_unidad = $idUnidad AND id_empresa = $idEmpresa";

        if ($this->conexion->query($sql)) {
            $respuesta["res"] = true;
        } else {
            $respuesta["error"] = $this->conexion->error;
        }
        return json_encode($respuesta);
    }
}
