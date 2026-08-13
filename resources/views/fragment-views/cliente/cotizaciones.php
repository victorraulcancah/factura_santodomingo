<?php
// Lee la config del vendedor SIEMPRE fresca desde la BD (no de la sesion, que esta cacheada al login)
$_esquema_tipo_sueldo = 1;
$_esquema_pct_comision = 0;
if (isset($_SESSION['usuario_fac']) && ($_SESSION['rol'] ?? 0) == 2) {
    $_conn = (new Conexion())->getConexion();
    $_uid = (int)$_SESSION['usuario_fac'];
    $_row = $_conn->query("SELECT tipo_sueldo, porcentaje_sueldo_comision FROM usuarios WHERE usuario_id = $_uid")->fetch_assoc();
    if ($_row) {
        $_esquema_tipo_sueldo = (int)$_row['tipo_sueldo'];
        $_esquema_pct_comision = (float)$_row['porcentaje_sueldo_comision'];
    }
}
?>
<div class="page-title-box">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h6 class="page-title">Cotizaciones</h6>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Facturacion</a></li>
                <li class="breadcrumb-item"><a href="/ventas" class="button-link">Cotizaciones</a></li>
                <li class="breadcrumb-item active" aria-current="page">Productos </li>
            </ol>
        </div>
        <div class="col-md-4">
            <div class="float-end d-none d-md-block">

            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <h4 class="card-title"></h4>

                <div class="card-title-desc text-end">
                    <a href="/cotizaciones/add" id="folder_btn_nuevo_folder" class="btn btn-primary button-link">
                        <i class="fa fa-plus "></i> Nueva Cotizaci&oacute;n
                    </a>
                </div>
                <div class="table-responsive">
                    <table id="datatable-c" class="table nowrap table-sm table-bordered text-center">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Sub. Total</th>
                                <th>IGV</th>
                                <th>Total</th>
                                <th>Vendedor</th>
                                <th>Estado</th>
                                <th><?php echo ($_SESSION['rol'] == 2) ? 'Comision' : 'Vender'; ?></th>
                                <th>Guia Remision</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function tes() {
        /*$("#loader-menor").show()
        _ajax("/ajs/cotizaciones", "POST", {}, function(resp) {
            //console.log(resp);
            tabla.rows().remove();
            resp.forEach(function(item) {
                let simbol='S/ '
                if (item.moneda.toString()==='2'){
                    item.total =item.total/item.cm_tc
                    simbol='$ '
                }
                tabla.row.add([
                    item.numero,
                    item.fecha,
                    item.documento + " | " + item.datos,
                    simbol+(parseFloat(item.total) / 1.18).toFixed(4),
                    simbol+(parseFloat(item.total) / 1.18 * 0.18).toFixed(4),
                    simbol+(parseFloat(item.total)).toFixed(4),
                    item.estado,
                    item.cotizacion_id,
                    item.cotizacion_id,

                    item.cotizacion_id
                ]).draw(false);
            })
        })*/
    }
    var tabla;
    $(document).ready(function() {
        let rol = <?php echo (int)$_SESSION["rol"]; ?>;
        // Esquema del vendedor leido FRESCO de la BD (no de la sesion cacheada)
        let tipoSueldo = <?php echo (int)$_esquema_tipo_sueldo; ?>;
        let pctComision = <?php echo (float)$_esquema_pct_comision; ?>;

        tabla = $("#datatable-c").DataTable({
            "processing": true,
            "serverSide": true,
            "sAjaxSource": _URL + "/data/cotizaciones/lista/ss",
            order: [
                [0, "desc"]
            ],
            columnDefs: [{
                    targets: 8,
                    render(data, type, row) {
                        if (rol !== 2) {
                            return `<a href="/ventas/productos?coti=${data}" class="btn btn-success btn-sm button-link"><i class="fa fa-align-justify"></i></a>`;
                        } else {
                            // Si el vendedor cobra sueldo fijo, no hay comision por cotizacion individual
                            if (tipoSueldo === 1) {
                                return `<span class="badge bg-secondary font-size-11" title="Sueldo fijo, sin comision por cotizacion">—</span>`;
                            }
                            // Si cobra por comision, mostrar total * pct%
                            let total = parseFloat(row[5]) || 0;
                            let comision = (total * (pctComision / 100)).toFixed(2);
                            return `<span class="badge bg-success font-size-12" title="${pctComision}% de S/ ${total.toFixed(2)}">S/ ${comision}</span>`;
                        }
                    }
                },
                {
                    targets: 7,
                    render: function(data, type, row, meta) {
                        /*  estado = '' */

                        if (data == '1') {
                            return '<span class="badge rounded-pill bg-success">Vendido</span>'
                        } else {
                            return '<span class="badge rounded-pill bg-danger">No Vendido</span>'
                        }

                    }
                },
                {
                    targets: 10,
                    render: function(data, type, row, meta) {
                        return `

                        <a href="${'/cotizaciones/edt/'+data}" class="button-link btn btn-sm btn-primary "><i class="fa fa-edit"></i></a>
                            <a href="${_URL+'/r/cotizaciones/reporte/'+data}" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-file"></i></a>
                            <button onclick="eliminarCotizacion(${data})" data-cod="" type="button" class="btn-del btn btn-danger btn-sm"><i class="fa fa-times"></i></button>
                            `;
                    }
                },
                {
                    targets: 9,
                    render(data) {
                        return `<a href="/guia/remision/registrar?coti=${data}" class="btn btn-success btn-sm button-link"><i class="fa fa-clipboard"></i></a>`;
                    }
                }
            ]
        })

        tes()

    })

    function eliminarCotizacion(cod) {
        console.log(cod)
        _ajax("/ajs/cotizaciones/del", "POST", {
            cod
        }, function(resp) {
            tes()
        })
    }
</script>