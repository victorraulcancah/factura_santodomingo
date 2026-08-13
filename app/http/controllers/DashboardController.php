<?php

require_once PATH_APP . "clases/DashboardTotales.php";

class DashboardController extends Controller
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = (new Conexion())->getConexion();
    }

    /**
     * Devuelve los totales de las tarjetas para el rango Desde/Hasta que
     * seleccione el usuario en la tabla del dashboard.
     */
    public function totales()
    {
        $desde = trim($_POST['desde'] ?? '');
        $hasta = trim($_POST['hasta'] ?? '');

        // Solo se aceptan fechas YYYY-MM-DD; cualquier otra cosa se ignora
        $valida = function ($fecha) {
            return preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) ? $fecha : '';
        };
        $desde = $valida($desde);
        $hasta = $valida($hasta);

        $esVendedor = (($_SESSION['rol'] ?? 0) == 2);

        $data = DashboardTotales::obtener(
            $this->conexion,
            $_SESSION['id_empresa'] ?? 0,
            $_SESSION['sucursal'] ?? 0,
            $_SESSION['usuario_fac'] ?? 0,
            $esVendedor,
            $desde,
            $hasta
        );

        $respuesta = [
            'res'        => true,
            'esVendedor' => $esVendedor,
            'cnt_cli'      => $data['cnt_cli'],
            'totalCajas'   => $data['totalCajas'],
            'totalRegalos' => $data['totalRegalos'],
            'costoRegalos' => $data['costoRegalos'],
        ];

        if ($esVendedor) {
            $g = DashboardTotales::ganancias($this->conexion, $_SESSION['usuario_fac'] ?? 0, $data['ventaTotal']);
            $respuesta['ventaTotal']      = $data['ventaTotal'];
            $respuesta['sueldo_base']     = $g['sueldo_base'];
            $respuesta['bono_meta']       = $g['bono_meta'];
            $respuesta['total_ganancias'] = $g['total_ganancias'];
        } else {
            $respuesta['totalv']  = $data['totalv'];
            $respuesta['totalvF'] = $data['totalvF'];
            $respuesta['totalvB'] = $data['totalvB'];
        }

        return json_encode($respuesta);
    }
}
