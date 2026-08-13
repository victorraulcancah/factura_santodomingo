<?php
    require_once PATH_APP . "clases/DashboardTotales.php";

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

    // Rango inicial de la tabla y de las tarjetas: el mes en curso
    $rangoDesde = date('Y-m-01');
    $rangoHasta = date('Y-m-d');

    // Leer config FRESCA de la BD (no de la sesion cacheada al login)
    $_cfgRow = $conexion->query("SELECT tipo_sueldo, monto_sueldo_fijo, porcentaje_sueldo_comision,
                                        meta_ventas, porcentaje_comision_meta
                                 FROM usuarios WHERE usuario_id = $usuario_id")->fetch_assoc() ?: [];
    $tipo_sueldo = (int)($_cfgRow['tipo_sueldo'] ?? 1);
    $monto_sueldo_fijo = (float)($_cfgRow['monto_sueldo_fijo'] ?? 0);
    $pct_sueldo_comision = ((float)($_cfgRow['porcentaje_sueldo_comision'] ?? 0)) / 100;
    $meta_ventas = (float)($_cfgRow['meta_ventas'] ?? 0);
    $pct_comision_meta = ((float)($_cfgRow['porcentaje_comision_meta'] ?? 0)) / 100;
    // Totales de las tarjetas. Se calculan para el mismo rango que la tabla,
    // y el filtro Desde/Hasta los recalcula via /ajs/dashboard/totales.
    $data = DashboardTotales::obtener($conexion, $empresa, $sucursal, $usuario_id, $esVendedor, $rangoDesde, $rangoHasta);

    // El comparativo con el mes anterior no depende del filtro
    $data["totalvMA"] = 0;
    if (!$esVendedor) {
        $_rowMA = $conexion->query("SELECT SUM(total) t FROM ventas
            WHERE id_empresa='$empresa' AND sucursal='$sucursal' AND estado = '1'
              AND YEAR(fecha_emision)='$anio2' AND MONTH(fecha_emision)='$mes2'")->fetch_assoc();
        $data["totalvMA"] = floatval($_rowMA["t"] ?? 0);
    }


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

    // Formatea cantidades que pueden tener decimales (media caja = 0.5)
    function formatCajas($valor) {
        $valor = floatval($valor);
        return rtrim(rtrim(number_format($valor, 2, '.', ','), '0'), '.');
    }

    // Obtener lista de cotizaciones. Se traen todas y el filtro Desde/Hasta de la
    // tabla actua en el navegador, sin recargar.
    $clientesRecientes = [];
    if ($esVendedor) {
        $sqlCli = "SELECT cl.datos as nombre, cl.documento, c.fecha as ultima_fecha, c.total,
                (SELECT COALESCE(SUM(pc.cantidad), 0) FROM productos_cotis pc WHERE pc.id_coti = c.cotizacion_id) as cajas
            FROM cotizaciones c
            LEFT JOIN clientes cl ON c.id_cliente = cl.id_cliente
            WHERE c.id_empresa='$empresa' AND c.sucursal='$sucursal' AND c.id_usuario='$usuario_id' AND c.estado <> '2'
            ORDER BY c.fecha DESC";
    } else {
        // Admin: cotizaciones de TODOS los vendedores
        $sqlCli = "SELECT cl.datos as nombre, cl.documento, c.fecha as ultima_fecha, c.total,
                COALESCE(
                    NULLIF(TRIM(CONCAT(COALESCE(u.nombres,''),' ',COALESCE(u.apellidos,''))),''),
                    NULLIF(TRIM(u.nombres_apellidos),''),
                    u.usuario
                ) as vendedor,
                (SELECT COALESCE(SUM(pc.cantidad), 0) FROM productos_cotis pc WHERE pc.id_coti = c.cotizacion_id) as cajas
            FROM cotizaciones c
            LEFT JOIN clientes cl ON c.id_cliente = cl.id_cliente
            LEFT JOIN usuarios u ON c.id_usuario = u.usuario_id
            WHERE c.id_empresa='$empresa' AND c.sucursal='$sucursal' AND c.estado <> '2'
            ORDER BY c.fecha DESC";
    }
    $resCli = $conexion->query($sqlCli);
    while ($row = $resCli->fetch_assoc()) {
        $clientesRecientes[] = $row;
    }

    // Obtener lista de ventas recientes (misma tabla, se alterna con el select)
    $ventasRecientes = [];
    $_expVendedor = "COALESCE(
                    NULLIF(TRIM(CONCAT(COALESCE(u.nombres,''),' ',COALESCE(u.apellidos,''))),''),
                    NULLIF(TRIM(u.nombres_apellidos),''),
                    u.usuario
                )";
    $_filtroVen = $esVendedor ? " AND v.id_usuario='$usuario_id'" : "";
    $sqlVen = "SELECT cl.datos as nombre, cl.documento, v.fecha_emision as ultima_fecha, v.total,
                CONCAT(COALESCE(v.serie,''),'-',COALESCE(v.numero,'')) as comprobante,
                $_expVendedor as vendedor,
                (SELECT COALESCE(SUM(pv.cantidad), 0) FROM productos_ventas pv WHERE pv.id_venta = v.id_venta) as cajas
            FROM ventas v
            LEFT JOIN clientes cl ON v.id_cliente = cl.id_cliente
            LEFT JOIN usuarios u ON v.id_usuario = u.usuario_id
            WHERE v.id_empresa='$empresa' AND v.sucursal='$sucursal' AND v.estado = '1'$_filtroVen
            ORDER BY v.fecha_emision DESC, v.id_venta DESC";
    if ($resVen = $conexion->query($sqlVen)) {
        while ($row = $resVen->fetch_assoc()) {
            $ventasRecientes[] = $row;
        }
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
                    <h4 class="fw-medium font-size-24">S/ <span id="card-sueldo-base"><?=number_format($sueldo_base, 2, ".", ",")?></span></h4>
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
                    <h4 class="fw-medium font-size-24">S/ <span id="card-bono-meta"><?=number_format($bono_meta, 2, ".", ",")?></span></h4>
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
                    <h4 class="fw-medium font-size-24">S/ <span id="card-total-ganancias"><?=number_format($total_ganancias, 2, ".", ",")?></span></h4>
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
                    <h4 class="fw-medium font-size-24">S/ <span id="card-venta-total"><?=number_format($ventaTotal, 2, ".", ",")?></span></h4>
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
                    <h4 class="fw-medium font-size-24">S/ <span class="card-total-vendido"><?=number_format(floatval($data["totalv"]), 2, ".", ",")?></span>  </h4>
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
                    <h4 class="fw-medium font-size-24">S/ <span class="card-total-vendido"><?=number_format(floatval($data["totalv"]), 2, ".", ",")?></span>  </h4>
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
                    <h4 class="fw-medium font-size-24">S/ <span id="card-total-facturas"><?=number_format(floatval($data["totalvF"]), 2, ".", ",")?></span>  </h4>
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
                    <h4 class="fw-medium font-size-24">S/ <span id="card-total-boletas"><?=number_format(floatval($data["totalvB"]), 2, ".", ",")?></span>  </h4>
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
                    <h4 class="fw-medium font-size-24"><span id="card-total-cajas"><?=formatCajas($data["totalCajas"])?></span></h4>
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

<?php if (!empty($clientesRecientes) || !empty($ventasRecientes)): ?>
<!-- TABLA COTIZACIONES / VENTAS RECIENTES -->
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-end mb-4">
                    <div class="col-md-4">
                        <h4 class="card-title mb-0" id="titulo-tabla-recientes"></h4>
                    </div>
                    <div class="col-md-8">
                        <div class="row justify-content-end g-2">
                            <div class="col-auto">
                                <label class="form-label mb-1">Mostrar</label>
                                <select id="select-tabla-recientes" class="form-control form-select">
                                    <option value="cotizaciones">Cotizaciones</option>
                                    <option value="ventas">Ventas</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <label class="form-label mb-1">Desde</label>
                                <input type="date" id="filtro-desde" class="form-control" value="<?=date('Y-m-01')?>">
                            </div>
                            <div class="col-auto">
                                <label class="form-label mb-1">Hasta</label>
                                <input type="date" id="filtro-hasta" class="form-control" value="<?=date('Y-m-d')?>">
                            </div>
                            <div class="col-auto d-flex align-items-end">
                                <button type="button" id="filtro-limpiar" class="btn btn-secondary">Ver todo</button>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                    // Las dos tablas se renderizan y el select alterna cual se muestra
                    $_tablasRecientes = [
                        'cotizaciones' => [
                            'titulo' => $esVendedor ? 'Mis Cotizaciones' : 'Cotizaciones - Todos los Vendedores',
                            'filas'  => $clientesRecientes,
                            'vacio'  => 'No hay cotizaciones registradas.',
                        ],
                        'ventas' => [
                            'titulo' => $esVendedor ? 'Mis Ventas' : 'Ventas - Todos los Vendedores',
                            'filas'  => $ventasRecientes,
                            'vacio'  => 'No hay ventas registradas.',
                        ],
                    ];
                ?>

                <?php foreach ($_tablasRecientes as $_clave => $_tabla): ?>
                <div class="tabla-recientes" data-tabla="<?=$_clave?>" data-titulo="<?=htmlspecialchars($_tabla['titulo'], ENT_QUOTES)?>" style="display: none;">
                    <?php if (empty($_tabla['filas'])): ?>
                        <p class="text-muted text-center mb-0"><?=$_tabla['vacio']?></p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <?php if (!$esVendedor): ?><th>Vendedor</th><?php endif; ?>
                                    <th>Cliente</th>
                                    <th>Documento</th>
                                    <?php if ($_clave == 'ventas'): ?><th>Comprobante</th><?php endif; ?>
                                    <th>Fecha</th>
                                    <th>Cajas</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($_tabla['filas'] as $cli): ?>
                                <tr class="fila-reciente" data-fecha="<?=$cli['ultima_fecha']?>" data-cajas="<?=floatval($cli['cajas'])?>" data-total="<?=floatval($cli['total'])?>">
                                    <td class="col-item"></td>
                                    <?php if (!$esVendedor): ?><td><?=$cli['vendedor'] ?: '-'?></td><?php endif; ?>
                                    <td><?=$cli['nombre']?></td>
                                    <td><?=$cli['documento']?></td>
                                    <?php if ($_clave == 'ventas'): ?><td><?=$cli['comprobante']?></td><?php endif; ?>
                                    <td><?=$cli['ultima_fecha']?></td>
                                    <td><?=formatCajas($cli['cajas'])?></td>
                                    <td>S/ <?=number_format($cli['total'], 2, '.', ',')?></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="sin-resultados" style="display: none;">
                                    <td colspan="<?= 6 + (!$esVendedor ? 1 : 0) + ($_clave == 'ventas' ? 1 : 0) ?>" class="text-muted">
                                        No hay registros en el rango de fechas seleccionado.
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="table-dark fw-bold">
                                    <td colspan="<?= 4 + (!$esVendedor ? 1 : 0) + ($_clave == 'ventas' ? 1 : 0) ?>" class="text-end">TOTAL</td>
                                    <td class="total-cajas">0</td>
                                    <td class="total-monto">S/ 0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

                <div class="row align-items-center mt-3" id="paginador-recientes">
                    <div class="col-md-6 d-flex align-items-center gap-2">
                        <label class="form-label mb-0">Ver</label>
                        <select id="paginas-tamano" class="form-control form-select" style="max-width: 90px;">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="0">Todos</option>
                        </select>
                        <span class="text-muted" id="paginas-info"></span>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="btn-group">
                            <button type="button" class="btn btn-secondary" id="pagina-primera">&laquo;</button>
                            <button type="button" class="btn btn-secondary" id="pagina-anterior">Anterior</button>
                            <button type="button" class="btn btn-secondary" id="pagina-siguiente">Siguiente</button>
                            <button type="button" class="btn btn-secondary" id="pagina-ultima">&raquo;</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        var selector = document.getElementById("select-tabla-recientes");
        var titulo = document.getElementById("titulo-tabla-recientes");
        var desde = document.getElementById("filtro-desde");
        var hasta = document.getElementById("filtro-hasta");
        var limpiar = document.getElementById("filtro-limpiar");
        var tablas = document.querySelectorAll(".tabla-recientes");

        var tamano = document.getElementById("paginas-tamano");
        var info = document.getElementById("paginas-info");
        var btnPrimera = document.getElementById("pagina-primera");
        var btnAnterior = document.getElementById("pagina-anterior");
        var btnSiguiente = document.getElementById("pagina-siguiente");
        var btnUltima = document.getElementById("pagina-ultima");
        var pagina = 1;

        // Filtra por rango de fechas, pagina el resultado y recalcula el pie.
        // Los totales son de TODO lo filtrado, no solo de la pagina visible.
        function aplicarFiltro(tabla) {
            var min = desde.value;
            var max = hasta.value;
            var sumCajas = 0;
            var sumTotal = 0;
            var filtradas = [];

            tabla.querySelectorAll(".fila-reciente").forEach(function(fila) {
                var fecha = fila.getAttribute("data-fecha") || "";
                var dentro = (!min || fecha >= min) && (!max || fecha <= max);
                if (dentro) {
                    filtradas.push(fila);
                    sumCajas += parseFloat(fila.getAttribute("data-cajas")) || 0;
                    sumTotal += parseFloat(fila.getAttribute("data-total")) || 0;
                } else {
                    fila.style.display = "none";
                }
            });

            var porPagina = parseInt(tamano.value, 10) || 0;
            var paginas = porPagina > 0 ? Math.max(1, Math.ceil(filtradas.length / porPagina)) : 1;
            if (pagina > paginas) {
                pagina = paginas;
            }
            var inicio = porPagina > 0 ? (pagina - 1) * porPagina : 0;
            var fin = porPagina > 0 ? inicio + porPagina : filtradas.length;

            filtradas.forEach(function(fila, i) {
                var enPagina = i >= inicio && i < fin;
                fila.style.display = enPagina ? "" : "none";
                if (enPagina) {
                    // Numeracion corrida entre paginas
                    fila.querySelector(".col-item").textContent = i + 1;
                }
            });

            var vacio = tabla.querySelector(".sin-resultados");
            if (vacio) {
                vacio.style.display = filtradas.length === 0 ? "" : "none";
            }

            var celdaCajas = tabla.querySelector(".total-cajas");
            var celdaMonto = tabla.querySelector(".total-monto");
            if (celdaCajas) {
                celdaCajas.textContent = parseFloat(sumCajas.toFixed(2)).toString();
            }
            if (celdaMonto) {
                celdaMonto.textContent = "S/ " + sumTotal.toLocaleString("es-PE", {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            pintarPaginador(filtradas.length, inicio, Math.min(fin, filtradas.length), paginas);
        }

        function pintarPaginador(total, inicio, fin, paginas) {
            if (total === 0) {
                info.textContent = "Sin registros";
            } else {
                info.textContent = (inicio + 1) + "-" + fin + " de " + total +
                    (paginas > 1 ? "  (pagina " + pagina + " de " + paginas + ")" : "");
            }
            btnPrimera.disabled = btnAnterior.disabled = (pagina <= 1);
            btnSiguiente.disabled = btnUltima.disabled = (pagina >= paginas);
            btnUltima.setAttribute("data-ultima", paginas);
        }

        function irA(destino) {
            pagina = destino;
            tablas.forEach(function(tabla) {
                if (tabla.getAttribute("data-tabla") === selector.value) {
                    aplicarFiltro(tabla);
                }
            });
        }

        function money(valor) {
            return (parseFloat(valor) || 0).toLocaleString("es-PE", {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function pintar(sel, texto) {
            document.querySelectorAll(sel).forEach(function(el) {
                el.textContent = texto;
            });
        }

        // Recalcula las tarjetas para el mismo rango que la tabla
        var pedido = 0;
        function actualizarTarjetas() {
            var propio = ++pedido;
            _ajax("/ajs/dashboard/totales", "POST", {
                desde: desde.value,
                hasta: hasta.value
            }, function(resp) {
                // Ignorar respuestas de filtros ya superados
                if (propio !== pedido || !resp || !resp.res) {
                    return;
                }
                pintar("#card-total-cajas", parseFloat((parseFloat(resp.totalCajas) || 0).toFixed(2)).toString());
                if (resp.esVendedor) {
                    pintar("#card-sueldo-base", money(resp.sueldo_base));
                    pintar("#card-bono-meta", money(resp.bono_meta));
                    pintar("#card-total-ganancias", money(resp.total_ganancias));
                    pintar("#card-venta-total", money(resp.ventaTotal));
                } else {
                    pintar(".card-total-vendido", money(resp.totalv));
                    pintar("#card-total-facturas", money(resp.totalvF));
                    pintar("#card-total-boletas", money(resp.totalvB));
                }
            });
        }

        function refrescar() {
            pagina = 1;
            var clave = selector.value;
            tablas.forEach(function(tabla) {
                var activa = tabla.getAttribute("data-tabla") === clave;
                tabla.style.display = activa ? "" : "none";
                if (activa) {
                    titulo.textContent = tabla.getAttribute("data-titulo");
                    aplicarFiltro(tabla);
                }
            });
            try {
                localStorage.setItem("tablaRecientesDashboard", clave);
            } catch (e) {}
        }

        try {
            selector.value = localStorage.getItem("tablaRecientesDashboard") || "cotizaciones";
        } catch (e) {}

        function cambioFecha() {
            refrescar();
            actualizarTarjetas();
        }

        tamano.addEventListener("change", refrescar);
        btnPrimera.addEventListener("click", function() { irA(1); });
        btnAnterior.addEventListener("click", function() { irA(Math.max(1, pagina - 1)); });
        btnSiguiente.addEventListener("click", function() { irA(pagina + 1); });
        btnUltima.addEventListener("click", function() {
            irA(parseInt(btnUltima.getAttribute("data-ultima"), 10) || 1);
        });

        selector.addEventListener("change", refrescar);
        desde.addEventListener("change", cambioFecha);
        hasta.addEventListener("change", cambioFecha);
        limpiar.addEventListener("click", function() {
            desde.value = "";
            hasta.value = "";
            cambioFecha();
        });

        // Al cargar, las tarjetas ya vienen calculadas con el mismo rango desde PHP
        refrescar();
    })();
</script>
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
                                    <h3>S/ <?= $esVendedor ? number_format($total_ganancias, 2, ".", ",") : number_format(floatval($data["totalv"]), 2, ".", ",")?></h3>
                                    <p class="text-muted mb-5"><?= $esVendedor ? 'Mi Ingreso Total (Sueldo + Bono).' : 'Ganancias Totales.' ?></p>
                                    <span class="peity-donut"
                                          data-peity='{ "fill": ["#02a499", "#f2f2f2"], "innerRadius": 28, "radius": 32 }'
                                          data-width="72" data-height="72"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-center">
                                    <p class="text-muted mb-4"><?= $esVendedor ? 'Venta Total Mes' : 'Mes Anterior' ?></p>
                                    <h3>S/ <?= $esVendedor ? number_format(floatval($data["ventaTotal"]), 2, ".", ",") : number_format(floatval($data["totalvMA"]), 2, ".", ",") ?></h3>
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
