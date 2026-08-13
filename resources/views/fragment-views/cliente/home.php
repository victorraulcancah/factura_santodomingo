<?php
    $empresa = $_SESSION['id_empresa'];
    $rol = $_SESSION['rol'];
    $usuario_id = $_SESSION['usuario_fac'];
    $sucursal = $_SESSION['sucursal'];
    $esVendedor = ($rol == 2);

    $anio1 = date("Y");
    $mes1 = date("m");
    $anio2 = '';
    $mes2 = '';
    if ($mes1 == 1) {
        $mes2 = '12';
        $anio2 = $anio1 - 1;
    } else {
        $anio2 = $anio1;
        $mes2 = $mes1 - 1;
    }

    $conexion = (new Conexion())->getConexion();

    // Leer config FRESCA de la BD (no de la sesion cacheada al login)
    $_cfgRow = $conexion->query("SELECT tipo_sueldo, monto_sueldo_fijo, porcentaje_sueldo_comision,
                                        meta_ventas, porcentaje_comision_meta
                                 FROM usuarios WHERE usuario_id = $usuario_id")->fetch_assoc() ?: [];
    $tipo_sueldo = (int)($_cfgRow['tipo_sueldo'] ?? 1);
    $monto_sueldo_fijo = (float)($_cfgRow['monto_sueldo_fijo'] ?? 0);
    $pct_sueldo_comision = ((float)($_cfgRow['porcentaje_sueldo_comision'] ?? 0)) / 100;
    $meta_ventas = (float)($_cfgRow['meta_ventas'] ?? 0);
    $pct_comision_meta = ((float)($_cfgRow['porcentaje_comision_meta'] ?? 0)) / 100;
    if ($esVendedor) {
        // Vendedor: datos basados en cotizaciones (tiene id_usuario)
        $sql = "SELECT
            (SELECT COUNT(DISTINCT id_cliente) FROM cotizaciones WHERE id_empresa='$empresa' AND estado <> '2' AND sucursal='$sucursal' AND id_usuario='$usuario_id') cnt_cli,
            (SELECT SUM(total) FROM cotizaciones WHERE id_empresa='$empresa' AND estado <> '2' AND sucursal='$sucursal' AND id_usuario='$usuario_id' AND YEAR(fecha)='$anio1' AND MONTH(fecha)='$mes1') ventaTotal,
            (SELECT COALESCE(SUM(pc.cantidad), 0) FROM productos_cotis pc INNER JOIN cotizaciones c ON pc.id_coti = c.cotizacion_id WHERE c.id_empresa='$empresa' AND c.estado <> '2' AND c.sucursal='$sucursal' AND c.id_usuario='$usuario_id' AND YEAR(c.fecha)='$anio1' AND MONTH(c.fecha)='$mes1') totalCajas
        ";
    } else {
        // Admin: datos globales de ventas
        $sql = "SELECT (SELECT SUM(total) FROM ventas WHERE id_empresa='$empresa' AND estado = '1' and sucursal='$sucursal' AND YEAR(fecha_emision)='$anio1' AND MONTH(fecha_emision)='$mes1') totalv ,
(SELECT COUNT(*)  FROM  clientes WHERE id_empresa = '$empresa') cnt_cli,
(SELECT SUM(total) FROM ventas WHERE id_empresa='$empresa'  and sucursal='$sucursal' and id_tido =2 AND estado = '1' AND YEAR(fecha_emision)='$anio1' AND MONTH(fecha_emision)='$mes1') totalvF ,
(SELECT SUM(total) FROM ventas WHERE id_empresa='$empresa' and sucursal='$sucursal' and id_tido =1 AND estado = '1' AND YEAR(fecha_emision)='$anio1' AND MONTH(fecha_emision)='$mes1') totalvB,
 (SELECT SUM(total) FROM ventas WHERE id_empresa='$empresa' and sucursal='$sucursal' AND estado = '1' AND YEAR(fecha_emision)='$anio2' AND MONTH(fecha_emision)='$mes2') totalvMA,
 (SELECT COALESCE(SUM(pc.cantidad), 0) FROM productos_cotis pc INNER JOIN cotizaciones c ON pc.id_coti = c.cotizacion_id WHERE c.id_empresa='$empresa' AND c.estado <> '2' AND c.sucursal='$sucursal' AND YEAR(c.fecha)='$anio1' AND MONTH(c.fecha)='$mes1') totalCajas
       ";
    }

    $data = $conexion->query($sql)->fetch_assoc();

    $ventaTotal = floatval($data["ventaTotal"] ?? 0);
    $sueldo_base = 0;
    if ($tipo_sueldo == 1) {
        $sueldo_base = floatval($monto_sueldo_fijo);
    } else {
        $sueldo_base = $ventaTotal * $pct_sueldo_comision;
    }
    
    $bono_meta = 0;
    if ($meta_ventas > 0 && $ventaTotal >= $meta_ventas) {
        $bono_meta = $ventaTotal * $pct_comision_meta; 
    }
    
    $total_ganancias = $sueldo_base + $bono_meta;
    $dataListVen = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

    if ($esVendedor) {
        $sql = "SELECT MONTH(fecha) mes, SUM(total) as total
            FROM cotizaciones
            WHERE id_empresa = '$empresa' AND estado <> '2' AND sucursal='$sucursal' AND id_usuario='$usuario_id' AND YEAR(fecha) = '$anio1'
            GROUP BY mes";
    } else {
        $sql = "SELECT MONTH(fecha_emision) mes, SUM(total) total
            FROM ventas
            WHERE id_empresa = '$empresa' AND estado = '1' AND sucursal='$sucursal' AND YEAR(fecha_emision) = '$anio1'
            GROUP BY mes";
    }
    $resultList = $conexion->query($sql);

    foreach ($resultList as $dtTemp) {
        $tempValue = 0;
        if (doubleval($dtTemp['total']) > 0) {
            $mes_total = doubleval($dtTemp['total']);
            if ($esVendedor) {
                $mes_base = ($tipo_sueldo == 1) ? floatval($monto_sueldo_fijo) : ($mes_total * $pct_sueldo_comision);
                $mes_bono = ($meta_ventas > 0 && $mes_total >= $meta_ventas) ? ($mes_total * $pct_comision_meta) : 0;
                $tempValue = $mes_base + $mes_bono;
            } else {
                $tempValue = $mes_total;
            }
        }
        $dataListVen[intval($dtTemp['mes'])] = $tempValue;
    }

    // Obtener lista de cotizaciones recientes
    $clientesRecientes = [];
    if ($esVendedor) {
        $sqlCli = "SELECT cl.datos as nombre, cl.documento, c.fecha as ultima_fecha, c.total,
                (SELECT COALESCE(SUM(pc.cantidad), 0) FROM productos_cotis pc WHERE pc.id_coti = c.cotizacion_id) as cajas
            FROM cotizaciones c
            LEFT JOIN clientes cl ON c.id_cliente = cl.id_cliente
            WHERE c.id_empresa='$empresa' AND c.sucursal='$sucursal' AND c.id_usuario='$usuario_id' AND c.estado <> '2'
            ORDER BY c.fecha DESC LIMIT 10";
    } else {
        // Admin: cotizaciones de TODOS los vendedores
        $sqlCli = "SELECT cl.datos as nombre, cl.documento, c.fecha as ultima_fecha, c.total,
                u.nombres_apellidos as vendedor,
                (SELECT COALESCE(SUM(pc.cantidad), 0) FROM productos_cotis pc WHERE pc.id_coti = c.cotizacion_id) as cajas
            FROM cotizaciones c
            LEFT JOIN clientes cl ON c.id_cliente = cl.id_cliente
            LEFT JOIN usuarios u ON c.id_usuario = u.usuario_id
            WHERE c.id_empresa='$empresa' AND c.sucursal='$sucursal' AND c.estado <> '2'
            ORDER BY c.fecha DESC LIMIT 20";
    }
    $resCli = $conexion->query($sqlCli);
    while ($row = $resCli->fetch_assoc()) {
        $clientesRecientes[] = $row;
    }
?>
<!-- start page title -->
<div class="page-title-box">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h6 class="page-title">Dashboard</h6>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item active">Bienvenido <strong>VINA SANTO DOMINGO</strong> al Sistema de Facturacion Electronica <strong>HATUNA</strong></li>
            </ol>
        </div>
        <div class="col-md-4">

        </div>
    </div>
</div>
<!-- end page title -->

<?php if ($esVendedor): ?>
<!-- DASHBOARD VENDEDOR -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stat bg-primary text-white">
            <div class="card-body">
                <div class="mb-4">
                    <div class="float-start mini-stat-img me-4">
                        <img src="<?=URL::to('public/assets/images/services-icon/01.png')?>" alt="">
                    </div>
                    <h5 class="font-size-16 text-uppercase text-white-50">Sueldo Base</h5>
                    <h4 class="fw-medium font-size-24">S/ <?=number_format($sueldo_base, 2, ".", ",")?></h4>
                    <div class="mini-stat-label bg-success">
                        <p class="mb-0">Mes</p>
                    </div>
                </div>
                <div class="pt-2">
                    <p class="text-white-50 mb-0 mt-1"><?= ($tipo_sueldo == 1) ? 'Sueldo Fijo' : 'Sueldo por Comision ('.($pct_sueldo_comision*100).'%)' ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stat bg-primary text-white">
            <div class="card-body">
                <div class="mb-4">
                    <div class="float-start mini-stat-img me-4">
                        <img src="<?=URL::to('public/assets/images/services-icon/02.png')?>" alt="">
                    </div>
                    <h5 class="font-size-16 text-uppercase text-white-50">Bono por Meta</h5>
                    <h4 class="fw-medium font-size-24">S/ <?=number_format($bono_meta, 2, ".", ",")?></h4>
                    <div class="mini-stat-label bg-info">
                        <p class="mb-0"><?= ($meta_ventas>0 && $ventaTotal >= $meta_ventas)?'Logrado':'Pendiente' ?></p>
                    </div>
                </div>
                <div class="pt-2">
                    <p class="text-white-50 mb-0 mt-1"><?= ($meta_ventas>0) ? 'Meta: S/ '.$meta_ventas : 'Sin bono' ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stat bg-primary text-white">
            <div class="card-body">
                <div class="mb-4">
                    <div class="float-start mini-stat-img me-4">
                        <img src="<?=URL::to('public/assets/images/services-icon/03.png')?>" alt="">
                    </div>
                    <h5 class="font-size-16 text-uppercase text-white-50">Total a Ganar</h5>
                    <h4 class="fw-medium font-size-24">S/ <?=number_format($total_ganancias, 2, ".", ",")?></h4>
                    <div class="mini-stat-label bg-danger">
                        <p class="mb-0">Mes</p>
                    </div>
                </div>
                <div class="pt-2">
                    <p class="text-white-50 mb-0 mt-1">Sueldo Base + Bono</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stat bg-primary text-white">
            <div class="card-body">
                <div class="mb-4">
                    <div class="float-start mini-stat-img me-4">
                        <img src="<?=URL::to('public/assets/images/services-icon/04.png')?>" alt="">
                    </div>
                    <h5 class="font-size-16 text-uppercase text-white-50">Venta Total</h5>
                    <h4 class="fw-medium font-size-24">S/ <?=number_format($ventaTotal, 2, ".", ",")?></h4>
                    <div class="mini-stat-label bg-warning">
                        <p class="mb-0">Mes</p>
                    </div>
                </div>
                <div class="pt-2">
                    <p class="text-white-50 mb-0 mt-1">Total vendido este mes</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<!-- DASHBOARD ADMIN -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stat bg-primary text-white">
            <div class="card-body">
                <div class="mb-4">
                    <div class="float-start mini-stat-img me-4">
                        <img src="<?=URL::to('public/assets/images/services-icon/01.png')?>" alt="">
                    </div>
                    <h5 class="font-size-16 text-uppercase text-white-50">Balance</h5>
                    <h4 class="fw-medium font-size-24">S/ <?=number_format($data["totalv"], 2, ".", ",")?>  </h4>
                    <div class="mini-stat-label bg-success">
                        <p class="mb-0">Reporte</p>
                    </div>
                </div>
                <div class="pt-2">
                    <div class="float-end" data-bs-toggle="modal" data-bs-target="#exportarModal">
                        <a href="javascript:void(0)" class="text-white-50"><i class="mdi mdi-arrow-right h5"></i></a>
                    </div>

                    <p class="text-white-50 mb-0 mt-1">Ventas y Compras</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stat bg-primary text-white">
            <div class="card-body">
                <div class="mb-4">
                    <div class="float-start mini-stat-img me-4">
                        <img src="<?=URL::to('public/assets/images/services-icon/01.png')?>" alt="">
                    </div>
                    <h5 class="font-size-16 text-uppercase text-white-50">Monto Vendido</h5>
                    <h4 class="fw-medium font-size-24">S/ <?=number_format($data["totalv"], 2, ".", ",")?>  </h4>
                    <div class="mini-stat-label bg-success">
                        <p class="mb-0">Mes</p>
                    </div>
                </div>
                <div class="pt-2">
                    <div class="float-end">
                        <a href="javascript:void(0)" class="text-white-50"><i class="mdi mdi-arrow-right h5"></i></a>
                    </div>

                    <p class="text-white-50 mb-0 mt-1">Facturas y Boletas</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stat bg-primary text-white">
            <div class="card-body">
                <div class="mb-4">
                    <div class="float-start mini-stat-img me-4">
                        <img src="<?=URL::to('public/assets/images/services-icon/02.png')?>" alt="">
                    </div>
                    <h5 class="font-size-16 text-uppercase text-white-50">Cantidad de Clientes</h5>
                    <h4 class="fw-medium font-size-24"><?=$data["cnt_cli"]?>  </h4>
                    <div hidden class="mini-stat-label bg-danger">
                        <p class="mb-0">Total</p>
                    </div>
                </div>
                <div class="pt-2">
                    <div hidden class="float-end">
                        <a href="javascript:void(0)" class="text-white-50"><i class="mdi mdi-arrow-right h5"></i></a>
                    </div>

                    <p class="text-white-50 mb-0 mt-1">Total</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stat bg-primary text-white">
            <div class="card-body">
                <div class="mb-4">
                    <div class="float-start mini-stat-img me-4">
                        <img src="<?=URL::to('public/assets/images/services-icon/03.png')?>" alt="">
                    </div>
                    <h5 class="font-size-16 text-uppercase text-white-50">Total en Facturas</h5>
                    <h4 class="fw-medium font-size-24">S/ <?=number_format($data["totalvF"], 2, ".", ",")?>  </h4>
                    <div class="mini-stat-label bg-info">
                        <p class="mb-0"> Mes</p>
                    </div>
                </div>
                <div class="pt-2">
                    <div class="float-end">
                        <a href="javascript:void(0)" class="text-white-50"><i class="mdi mdi-arrow-right h5"></i></a>
                    </div>

                    <p class="text-white-50 mb-0 mt-1"></p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stat bg-primary text-white">
            <div class="card-body">
                <div class="mb-4">
                    <div class="float-start mini-stat-img me-4">
                        <img src="<?=URL::to('public/assets/images/services-icon/04.png')?>" alt="">
                    </div>
                    <h5 class="font-size-16 text-uppercase text-white-50">Total en Boletas</h5>
                    <h4 class="fw-medium font-size-24">S/ <?=number_format($data["totalvB"], 2, ".", ",")?>  </h4>
                    <div class="mini-stat-label bg-warning">
                        <p class="mb-0">Mes</p>
                    </div>
                </div>
                <div class="pt-2">
                    <div class="float-end">
                        <a href="javascript:void(0)" class="text-white-50"><i class="mdi mdi-arrow-right h5"></i></a>
                    </div>

                    <p class="text-white-50 mb-0 mt-1"></p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stat bg-primary text-white">
            <div class="card-body">
                <div class="mb-4">
                    <div class="float-start mini-stat-img me-4">
                        <img src="<?=URL::to('public/assets/images/services-icon/04.png')?>" alt="">
                    </div>
                    <h5 class="font-size-16 text-uppercase text-white-50">Cajas Vendidas</h5>
                    <h4 class="fw-medium font-size-24"><?=intval($data["totalCajas"])?></h4>
                    <div class="mini-stat-label bg-danger">
                        <p class="mb-0">Mes</p>
                    </div>
                </div>
                <div class="pt-2">
                    <p class="text-white-50 mb-0 mt-1">Total cajas todos los vendedores</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<!-- end row -->

<?php if (!empty($clientesRecientes)): ?>
<!-- TABLA COTIZACIONES RECIENTES -->
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4"><?= $esVendedor ? 'Mis Clientes Recientes' : 'Cotizaciones Recientes - Todos los Vendedores' ?></h4>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover text-center">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <?php if (!$esVendedor): ?><th>Vendedor</th><?php endif; ?>
                                <th>Cliente</th>
                                <th>Documento</th>
                                <th>Fecha</th>
                                <th>Cajas</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $sumTotal = 0;
                                $sumCajas = 0;
                                foreach ($clientesRecientes as $i => $cli):
                                    $sumTotal += floatval($cli['total']);
                                    $sumCajas += intval($cli['cajas']);
                            ?>
                            <tr>
                                <td><?=$i + 1?></td>
                                <?php if (!$esVendedor): ?><td><?=$cli['vendedor'] ?? '-'?></td><?php endif; ?>
                                <td><?=$cli['nombre']?></td>
                                <td><?=$cli['documento']?></td>
                                <td><?=$cli['ultima_fecha']?></td>
                                <td><?=intval($cli['cajas'])?></td>
                                <td>S/ <?=number_format($cli['total'], 2, '.', ',')?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-dark fw-bold">
                                <td colspan="<?= $esVendedor ? 4 : 5 ?>" class="text-end">TOTAL</td>
                                <td><?=$sumCajas?></td>
                                <td>S/ <?=number_format($sumTotal, 2, '.', ',')?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4"><?= $esVendedor ? 'Mi Comision Anual' : 'Venta Anual' ?></h4>
                <div class="row">
                    <div class="col-lg-7">
                        <div>
                            <canvas id="chart-with-area">
                            </canvas>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="text-center">
                                    <p class="text-muted mb-4">Este Mes</p>
                                    <h3>S/ <?= $esVendedor ? number_format($total_ganancias, 2, ".", ",") : number_format($data["totalv"], 2, ".", ",")?></h3>
                                    <p class="text-muted mb-5"><?= $esVendedor ? 'Mi Ingreso Total (Sueldo + Bono).' : 'Ganancias Totales.' ?></p>
                                    <span class="peity-donut"
                                          data-peity='{ "fill": ["#02a499", "#f2f2f2"], "innerRadius": 28, "radius": 32 }'
                                          data-width="72" data-height="72"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-center">
                                    <p class="text-muted mb-4"><?= $esVendedor ? 'Venta Total Mes' : 'Mes Anterior' ?></p>
                                    <h3>S/ <?= $esVendedor ? number_format($data["ventaTotal"], 2, ".", ",") : number_format($data["totalvMA"], 2, ".", ",") ?></h3>
                                    <p class="text-muted mb-5"><?= $esVendedor ? 'Total sin comision.' : 'Comparativa Ganancias Totales.' ?></p>
                                    <span class="peity-donut"
                                          data-peity='{ "fill": ["#02a499", "#f2f2f2"], "innerRadius": 28, "radius": 32 }'
                                          data-width="72" data-height="72"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end row -->
            </div>
        </div>
        <!-- end card -->
    </div>

</div>
<!-- end row -->

<textarea style="display: none" id="listatempdata"><?=json_encode($dataListVen)?></textarea>

<?php if (!$esVendedor): ?>
<div class="modal fade" id="exportarModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" target="_blank" action="<?=URL::to('reporte/balance')?>"
              method="get">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Exportar Balance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-md-6 mt-2">
                        <label>Desde</label>
                        <input required="required" name="desde" value="<?=date('Y-m-d')?>" type="date"
                               class="form-control">
                    </div>
                    <div class="form-group col-md-6 mt-2">
                        <label>Hasta</label>
                        <input required="required" name="hasta" value="<?=date('Y-m-d')?>" type="date"
                               class="form-control">
                    </div>
                    <div class="form-group col-md-12 mt-2">
                        <div class="form-group col-md-12 mt-2">
                            <label for="formGroupExampleInput">Tipo</label>
                            <div class="form-group col-md-12 mt-2">

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="inlineRadioOptions"
                                           id="inlineRadio1" ruta="<?=URL::to('reporte/balance')?>"
                                           value="option" checked>
                                    <label class="form-check-label" for="inlineRadio1">Compras y ventas</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="inlineRadioOptions"
                                           id="inlineRadio2" ruta="<?=URL::to('reporte/compras_balance')?>"
                                           value="option">
                                    <label class="form-check-label" for="inlineRadio2">Compras</label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="inlineRadioOptions"
                                           id="inlineRadio3" ruta="<?=URL::to('reporte/ventas_balance')?>"
                                           value="option">
                                    <label class="form-check-label" for="inlineRadio3">Ventas</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="inlineRadioOptions"
                                           id="inlineRadio3" ruta="<?=URL::to('reporte/kardex_balance')?>"
                                           value="option">
                                    <label class="form-check-label" for="inlineRadio3">Kardex</label>
                                </div>
                                <script>
									$("[id^='inlineRadio']").click(function () {
										$('#exportarModal form').attr('action', this.getAttribute('ruta'))
									});
                                </script>
                            </div>
                        </div>
                    </div>

                    <div class="form-group col-md-12 mt-2">
                        <div class="form-group col-md-12 mt-2">
                            <label for="formGroupExampleInput">Formato</label>
                            <div class="form-group col-md-12 mt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tipo" id="exampleRadios1"
                                           value="pdf"
                                           checked>
                                    <label class="form-check-label" for="inlineRadio1">PDF</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tipo" id="exampleRadios1"
                                           value="EXCEL">
                                    <label class="form-check-label" for="inlineRadio2">Excel</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="almacenId" value="<?php echo 1 ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Guardar</button>
                <button type="button" data-bs-dismiss="modal" class="btn btn-secondary">Cerrar</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
	$(document).ready(function () {
		new Chart("chart-with-area", {
			type: "line",
			data: {
				labels: getMesAbreLinst("es"),
				datasets: [
					{
						label: '<?= $esVendedor ? "Ingreso Mensual (Sueldo + Bono)" : "Ventas" ?>',
						data: JSON.parse($("#listatempdata").val()),
						borderColor: "#626ed4",
						backgroundColor: "rgba(98,110,212,0.36)",
						fill: true

					}
				]
			},

		});
	})
</script>
