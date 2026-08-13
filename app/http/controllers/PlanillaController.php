<?php

class PlanillaController extends Controller
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = (new Conexion())->getConexion();
    }

    /**
     * Calcula el pago de un vendedor en un rango de fechas.
     * Devuelve sueldo_base + bono_meta + total_pagar + cotizaciones incluidas.
     *
     * POST: id_usuario, desde (YYYY-MM-DD), hasta (YYYY-MM-DD)
     */
    public function calcular()
    {
        $respuesta = ["res" => false];
        $idUsuario = (int)($_POST['id_usuario'] ?? 0);
        $desde = $_POST['desde'] ?? '';
        $hasta = $_POST['hasta'] ?? '';
        $idEmpresa = (int)($_SESSION['id_empresa'] ?? 0);
        $sucursal = (int)($_SESSION['sucursal'] ?? 0);

        if ($idUsuario <= 0 || !$desde || !$hasta) {
            $respuesta["error"] = "Faltan parametros (id_usuario, desde, hasta)";
            return json_encode($respuesta);
        }

        // 1) Leer configuracion del vendedor
        $sqlUser = "SELECT usuario_id, nombres, apellidos,
                    tipo_sueldo, monto_sueldo_fijo, porcentaje_sueldo_comision,
                    meta_ventas, porcentaje_comision_meta
                    FROM usuarios WHERE usuario_id = $idUsuario LIMIT 1";
        $user = $this->conexion->query($sqlUser)->fetch_assoc();
        if (!$user) {
            $respuesta["error"] = "Vendedor no encontrado";
            return json_encode($respuesta);
        }

        $tipoSueldo  = (int)($user['tipo_sueldo'] ?? 1);
        $montoFijo   = (float)($user['monto_sueldo_fijo'] ?? 0);
        $pctComision = (float)($user['porcentaje_sueldo_comision'] ?? 0);
        $meta        = (float)($user['meta_ventas'] ?? 0);
        $pctBono     = (float)($user['porcentaje_comision_meta'] ?? 0);

        // 2) Calcular dias del periodo (para sueldo fijo proporcional)
        $diasPeriodo = $this->diasEntre($desde, $hasta);

        // 3) Obtener cotizaciones del vendedor en el periodo (no anuladas)
        $sqlCotis = "SELECT cotizacion_id, numero, fecha, total
                     FROM cotizaciones
                     WHERE id_usuario = $idUsuario
                       AND id_empresa = $idEmpresa
                       AND sucursal = $sucursal
                       AND estado <> '2'
                       AND fecha BETWEEN '$desde' AND '$hasta'
                     ORDER BY fecha ASC";
        $resCotis = $this->conexion->query($sqlCotis);

        $cotizaciones = [];
        $totalVentas = 0.0;
        if ($resCotis) {
            while ($r = $resCotis->fetch_assoc()) {
                $cotizaciones[] = [
                    'cotizacion_id' => (int)$r['cotizacion_id'],
                    'numero' => $r['numero'],
                    'fecha' => $r['fecha'],
                    'total' => (float)$r['total']
                ];
                $totalVentas += (float)$r['total'];
            }
        }

        // 4) Calcular sueldo base
        if ($tipoSueldo == 1) {
            // Fijo: proporcional al periodo (base 30 dias = mes)
            $sueldoBase = $montoFijo * ($diasPeriodo / 30.0);
        } else {
            // Comision: total_ventas * %
            $sueldoBase = $totalVentas * ($pctComision / 100.0);
        }

        // 5) Calcular bono extra (sobre el EXCEDENTE de la meta)
        $bonoMeta = 0.0;
        $metaCumplida = false;
        $excedente = 0.0;
        if ($meta > 0 && $totalVentas > $meta) {
            $excedente = $totalVentas - $meta;
            $bonoMeta = $excedente * ($pctBono / 100.0);
            $metaCumplida = true;
        }

        $totalPagar = $sueldoBase + $bonoMeta;

        // 6) Verificar si ya hay un pago aprobado que solape este periodo
        $sqlPrev = "SELECT id_pago, periodo_desde, periodo_hasta, total_pagado, total_ventas_periodo, fecha_aprobacion
                    FROM planilla_pagos
                    WHERE id_usuario = $idUsuario AND estado = '1'
                      AND periodo_desde <= '$hasta' AND periodo_hasta >= '$desde'
                    ORDER BY fecha_aprobacion DESC";
        $pagosPrevios = [];
        $resPrev = $this->conexion->query($sqlPrev);
        if ($resPrev) {
            while ($r = $resPrev->fetch_assoc()) {
                // Recalcular total real de ese periodo histórico (puede haber cambiado por anulaciones)
                $pdesde = $r['periodo_desde'];
                $phasta = $r['periodo_hasta'];
                $totReal = (float)$this->conexion->query(
                    "SELECT COALESCE(SUM(total),0) AS t FROM cotizaciones
                     WHERE id_usuario = $idUsuario AND id_empresa = $idEmpresa
                       AND sucursal = $sucursal AND estado <> '2'
                       AND fecha BETWEEN '$pdesde' AND '$phasta'"
                )->fetch_assoc()['t'];

                $diff = $totReal - (float)$r['total_ventas_periodo'];
                $pagosPrevios[] = [
                    'id_pago' => (int)$r['id_pago'],
                    'periodo_desde' => $pdesde,
                    'periodo_hasta' => $phasta,
                    'total_ventas_snapshot' => (float)$r['total_ventas_periodo'],
                    'total_ventas_real_hoy' => $totReal,
                    'diferencia' => $diff,
                    'total_pagado' => (float)$r['total_pagado'],
                    'fecha_aprobacion' => $r['fecha_aprobacion'],
                    'aviso' => $diff < 0 ? 'Se anularon cotizaciones despues del pago' : ($diff > 0 ? 'Se agregaron cotizaciones despues del pago' : 'Sin cambios')
                ];
            }
        }

        $respuesta = [
            "res" => true,
            "vendedor" => [
                "id" => (int)$user['usuario_id'],
                "nombre" => trim(($user['nombres'] ?? '') . ' ' . ($user['apellidos'] ?? '')),
            ],
            "periodo" => [
                "desde" => $desde,
                "hasta" => $hasta,
                "dias" => $diasPeriodo
            ],
            "configuracion" => [
                "tipo_sueldo" => $tipoSueldo,
                "monto_sueldo_fijo" => $montoFijo,
                "porcentaje_sueldo_comision" => $pctComision,
                "meta_ventas" => $meta,
                "porcentaje_comision_meta" => $pctBono
            ],
            "calculo" => [
                "total_ventas_periodo" => round($totalVentas, 2),
                "sueldo_base" => round($sueldoBase, 2),
                "bono_meta" => round($bonoMeta, 2),
                "excedente_meta" => round($excedente, 2),
                "meta_cumplida" => $metaCumplida,
                "total_pagar" => round($totalPagar, 2)
            ],
            "cotizaciones" => $cotizaciones,
            "pagos_previos_solapados" => $pagosPrevios
        ];

        return json_encode($respuesta);
    }

    /**
     * Aprueba el pago y lo guarda como snapshot en planilla_pagos.
     *
     * POST: id_usuario, desde, hasta, observaciones (opcional)
     */
    public function aprobar()
    {
        $respuesta = ["res" => false];
        $idUsuario = (int)($_POST['id_usuario'] ?? 0);
        $desde = $_POST['desde'] ?? '';
        $hasta = $_POST['hasta'] ?? '';
        $observaciones = $this->conexion->real_escape_string($_POST['observaciones'] ?? '');
        $idEmpresa = (int)($_SESSION['id_empresa'] ?? 0);
        $sucursal = (int)($_SESSION['sucursal'] ?? 0);
        $idAdmin = (int)($_SESSION['usuario_fac'] ?? 0);

        if ($idUsuario <= 0 || !$desde || !$hasta) {
            $respuesta["error"] = "Faltan parametros";
            return json_encode($respuesta);
        }

        // Volver a calcular (fuente unica de verdad)
        $_POST['id_usuario'] = $idUsuario;
        $_POST['desde'] = $desde;
        $_POST['hasta'] = $hasta;
        $calc = json_decode($this->calcular(), true);

        if (!($calc['res'] ?? false)) {
            $respuesta["error"] = $calc['error'] ?? "Error al calcular";
            return json_encode($respuesta);
        }

        $cfg = $calc['configuracion'];
        $res = $calc['calculo'];
        $dias = $calc['periodo']['dias'];

        $sql = "INSERT INTO planilla_pagos SET
            id_usuario = $idUsuario,
            id_empresa = $idEmpresa,
            sucursal = $sucursal,
            periodo_desde = '$desde',
            periodo_hasta = '$hasta',
            dias_periodo = $dias,
            tipo_sueldo_snap = {$cfg['tipo_sueldo']},
            monto_sueldo_fijo_snap = {$cfg['monto_sueldo_fijo']},
            pct_comision_snap = {$cfg['porcentaje_sueldo_comision']},
            meta_snap = {$cfg['meta_ventas']},
            pct_bono_snap = {$cfg['porcentaje_comision_meta']},
            total_ventas_periodo = {$res['total_ventas_periodo']},
            sueldo_base = {$res['sueldo_base']},
            bono_meta = {$res['bono_meta']},
            total_pagado = {$res['total_pagar']},
            id_admin_aprobador = $idAdmin,
            estado = '1',
            observaciones = '$observaciones'";

        if ($this->conexion->query($sql)) {
            $respuesta["res"] = true;
            $respuesta["id_pago"] = $this->conexion->insert_id;
            $respuesta["total_pagado"] = $res['total_pagar'];
        } else {
            $respuesta["error"] = $this->conexion->error;
        }
        return json_encode($respuesta);
    }

    /**
     * Elimina (soft-delete) un pago aprobado.
     * POST: id_pago
     */
    public function eliminar()
    {
        $respuesta = ["res" => false];
        $idPago = (int)($_POST['id_pago'] ?? 0);
        $idEmpresa = (int)($_SESSION['id_empresa'] ?? 0);

        if ($idPago <= 0) {
            $respuesta["error"] = "ID de pago invalido";
            return json_encode($respuesta);
        }

        $sql = "UPDATE planilla_pagos SET estado = '0' WHERE id_pago = $idPago AND id_empresa = $idEmpresa";

        if ($this->conexion->query($sql) && $this->conexion->affected_rows > 0) {
            $respuesta["res"] = true;
        } else {
            $respuesta["error"] = "No se encontro el pago o no tienes permisos";
        }
        return json_encode($respuesta);
    }

    /**
     * Lista pagos aprobados con filtros opcionales.
     */
    public function historial()
    {
        $idEmpresa = (int)($_SESSION['id_empresa'] ?? 0);
        $idUsuario = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : null;
        $desde = $_POST['desde'] ?? '';
        $hasta = $_POST['hasta'] ?? '';

        $where = "WHERE p.id_empresa = $idEmpresa AND p.estado = '1'";
        if ($idUsuario) $where .= " AND p.id_usuario = $idUsuario";
        if ($desde) $where .= " AND p.periodo_desde >= '$desde'";
        if ($hasta) $where .= " AND p.periodo_hasta <= '$hasta'";

        $sql = "SELECT p.*, u.nombres, u.apellidos
                FROM planilla_pagos p
                LEFT JOIN usuarios u ON p.id_usuario = u.usuario_id
                $where
                ORDER BY p.fecha_aprobacion DESC
                LIMIT 200";

        $lista = [];
        $res = $this->conexion->query($sql);
        if ($res) {
            while ($r = $res->fetch_assoc()) $lista[] = $r;
        }
        return json_encode(["res" => true, "data" => $lista]);
    }

    /**
     * Helper: calcula dias inclusivos entre dos fechas YYYY-MM-DD
     */
    private function diasEntre($desde, $hasta)
    {
        $d1 = strtotime($desde);
        $d2 = strtotime($hasta);
        if (!$d1 || !$d2 || $d2 < $d1) return 1;
        return (int)round(($d2 - $d1) / 86400) + 1;
    }
}
