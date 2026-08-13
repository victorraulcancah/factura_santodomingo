<?php

/**
 * Totales del dashboard para un rango de fechas.
 *
 * Lo usan la vista (carga inicial) y el endpoint AJAX que dispara el filtro
 * Desde/Hasta de la tabla, para que las tarjetas muestren siempre el mismo
 * periodo que la tabla y no se dupliquen las consultas.
 */
class DashboardTotales
{
    /**
     * @param string $desde Fecha YYYY-MM-DD, vacio = sin limite inferior
     * @param string $hasta Fecha YYYY-MM-DD, vacio = sin limite superior
     */
    public static function obtener($conexion, $empresa, $sucursal, $usuario_id, $esVendedor, $desde = '', $hasta = '')
    {
        $empresa    = (int) $empresa;
        $sucursal   = (int) $sucursal;
        $usuario_id = (int) $usuario_id;

        // Rango aplicado sobre la columna de fecha que corresponda a cada tabla
        $rango = function ($columna) use ($conexion, $desde, $hasta) {
            $sql = '';
            if ($desde !== '') {
                $sql .= " AND $columna >= '" . $conexion->real_escape_string($desde) . "'";
            }
            if ($hasta !== '') {
                $sql .= " AND $columna <= '" . $conexion->real_escape_string($hasta) . "'";
            }
            return $sql;
        };

        if ($esVendedor) {
            // Vendedor: sus cotizaciones (es la base de su comision)
            $rangoCoti = $rango('fecha');
            $rangoCotiC = $rango('c.fecha');
            $sql = "SELECT
                (SELECT COUNT(DISTINCT id_cliente) FROM cotizaciones
                    WHERE id_empresa='$empresa' AND estado <> '2' AND sucursal='$sucursal' AND id_usuario='$usuario_id'$rangoCoti) cnt_cli,
                (SELECT SUM(total) FROM cotizaciones
                    WHERE id_empresa='$empresa' AND estado <> '2' AND sucursal='$sucursal' AND id_usuario='$usuario_id'$rangoCoti) ventaTotal,
                (SELECT COALESCE(SUM(pc.cantidad), 0) FROM productos_cotis pc
                    INNER JOIN cotizaciones c ON pc.id_coti = c.cotizacion_id
                    WHERE c.id_empresa='$empresa' AND c.estado <> '2' AND c.sucursal='$sucursal' AND c.id_usuario='$usuario_id'$rangoCotiC) totalCajas,
                (SELECT COALESCE(SUM(pc.cantidad), 0) FROM productos_cotis pc
                    INNER JOIN cotizaciones c ON pc.id_coti = c.cotizacion_id
                    WHERE c.id_empresa='$empresa' AND c.estado <> '2' AND c.sucursal='$sucursal' AND c.id_usuario='$usuario_id' AND pc.es_regalo = 1$rangoCotiC) totalRegalos,
                (SELECT COALESCE(SUM(pc.cantidad * pc.costo), 0) FROM productos_cotis pc
                    INNER JOIN cotizaciones c ON pc.id_coti = c.cotizacion_id
                    WHERE c.id_empresa='$empresa' AND c.estado <> '2' AND c.sucursal='$sucursal' AND c.id_usuario='$usuario_id' AND pc.es_regalo = 1$rangoCotiC) costoRegalos";
        } else {
            // Admin: ventas confirmadas de la sucursal
            $rangoVen = $rango('fecha_emision');
            $rangoVenV = $rango('v.fecha_emision');
            $sql = "SELECT
                (SELECT SUM(total) FROM ventas
                    WHERE id_empresa='$empresa' AND estado = '1' AND sucursal='$sucursal'$rangoVen) totalv,
                (SELECT COUNT(*) FROM clientes WHERE id_empresa = '$empresa') cnt_cli,
                (SELECT SUM(total) FROM ventas
                    WHERE id_empresa='$empresa' AND sucursal='$sucursal' AND id_tido = 2 AND estado = '1'$rangoVen) totalvF,
                (SELECT SUM(total) FROM ventas
                    WHERE id_empresa='$empresa' AND sucursal='$sucursal' AND id_tido = 1 AND estado = '1'$rangoVen) totalvB,
                (SELECT COALESCE(SUM(pv.cantidad), 0) FROM productos_ventas pv
                    INNER JOIN ventas v ON pv.id_venta = v.id_venta
                    WHERE v.id_empresa='$empresa' AND v.estado = '1' AND v.sucursal='$sucursal'$rangoVenV) totalCajas,
                (SELECT COALESCE(SUM(pv.cantidad), 0) FROM productos_ventas pv
                    INNER JOIN ventas v ON pv.id_venta = v.id_venta
                    WHERE v.id_empresa='$empresa' AND v.estado = '1' AND v.sucursal='$sucursal' AND pv.es_regalo = 1$rangoVenV) totalRegalos,
                (SELECT COALESCE(SUM(pv.cantidad * pv.costo), 0) FROM productos_ventas pv
                    INNER JOIN ventas v ON pv.id_venta = v.id_venta
                    WHERE v.id_empresa='$empresa' AND v.estado = '1' AND v.sucursal='$sucursal' AND pv.es_regalo = 1$rangoVenV) costoRegalos";
        }

        $data = $conexion->query($sql)->fetch_assoc() ?: [];

        // Normalizar: las sumas devuelven NULL cuando no hay filas en el rango
        foreach (['totalv', 'totalvF', 'totalvB', 'ventaTotal', 'totalCajas', 'totalRegalos', 'costoRegalos'] as $campo) {
            $data[$campo] = floatval($data[$campo] ?? 0);
        }
        $data['cnt_cli'] = intval($data['cnt_cli'] ?? 0);

        return $data;
    }

    /**
     * Sueldo base, bono por meta y total a ganar del vendedor sobre un monto vendido.
     */
    public static function ganancias($conexion, $usuario_id, $ventaTotal)
    {
        $usuario_id = (int) $usuario_id;
        $cfg = $conexion->query("SELECT tipo_sueldo, monto_sueldo_fijo, porcentaje_sueldo_comision,
                                        meta_ventas, porcentaje_comision_meta
                                 FROM usuarios WHERE usuario_id = $usuario_id")->fetch_assoc() ?: [];

        $tipo_sueldo         = (int) ($cfg['tipo_sueldo'] ?? 1);
        $monto_sueldo_fijo   = (float) ($cfg['monto_sueldo_fijo'] ?? 0);
        $pct_sueldo_comision = ((float) ($cfg['porcentaje_sueldo_comision'] ?? 0)) / 100;
        $meta_ventas         = (float) ($cfg['meta_ventas'] ?? 0);
        $pct_comision_meta   = ((float) ($cfg['porcentaje_comision_meta'] ?? 0)) / 100;

        $ventaTotal  = floatval($ventaTotal);
        $sueldo_base = ($tipo_sueldo == 1) ? $monto_sueldo_fijo : ($ventaTotal * $pct_sueldo_comision);
        $bono_meta   = ($meta_ventas > 0 && $ventaTotal >= $meta_ventas) ? ($ventaTotal * $pct_comision_meta) : 0;

        return [
            'tipo_sueldo'      => $tipo_sueldo,
            'meta_ventas'      => $meta_ventas,
            'sueldo_base'      => $sueldo_base,
            'bono_meta'        => $bono_meta,
            'total_ganancias'  => $sueldo_base + $bono_meta,
            'falta_meta'       => max(0, $meta_ventas - $ventaTotal),
        ];
    }
}
